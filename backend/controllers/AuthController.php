<?php
class AuthController extends Controller
{
    public function showLogin(): void {
        if (current_user()) redirect('dashboard');
        $this->render('auth/login', [
            'pageTitle'=>'Log In', 'error'=>get_flash('error'), 'success'=>get_flash('success'),
            'email'=>$_SESSION['old_email'] ?? ''
        ], 'auth');
        unset($_SESSION['old_email']);
    }
    public function login(): void {
        verify_csrf();
        $email = mb_strtolower(input('email'));
        $password = (string)($_POST['password'] ?? '');
        $_SESSION['old_email'] = $email;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            flash('error', 'Please enter a valid email address and password.');
            redirect('login');
        }
        $user = (new User())->findByEmail($email);
        $valid = false;
        if ($user) {
            // Seeded demonstration accounts use legacy plaintext passwords.
            // New accounts use password_hash(); both are accepted during migration.
            $stored = (string)$user['password'];
            $valid = password_verify($password, $stored) ||
                (!str_starts_with($stored, '$2y$') && !str_starts_with($stored, '$argon2') && hash_equals($stored, $password));
            if ($valid && !password_get_info($stored)['algo']) {
                (new ModelPasswordUpgrade())->upgrade((int)$user['user_id'], $password);
            }
        }
        if (!$valid) {
            flash('error', 'Incorrect email or password.');
            redirect('login');
        }
        if ($user['account_status'] !== 'active') {
            flash('error', 'This account has been deactivated. Please contact an admin.');
            redirect('login');
        }
        unset($_SESSION['old_email']);
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['user_id'];
        redirect('dashboard');
    }
    public function showSignup(): void {
        if (current_user()) redirect('dashboard');
        $this->render('auth/signup', [
            'pageTitle'=>'Sign Up', 'error'=>get_flash('error'),
            'old'=>$_SESSION['old_signup'] ?? ['full_name'=>'','email'=>'']
        ], 'auth');
        unset($_SESSION['old_signup']);
    }
    public function signup(): void {
        verify_csrf();
        $fullName = input('full_name');
        $email = mb_strtolower(input('email'));
        $password = (string)($_POST['password'] ?? '');
        $repeat = (string)($_POST['repeat_password'] ?? '');
        $_SESSION['old_signup'] = ['full_name'=>$fullName, 'email'=>$email];
        $users = new User();
        $error = null;
        if ($fullName === '' || mb_strlen($fullName) > 100) $error = 'Please enter a name (up to 100 characters).';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) $error = 'Please enter a valid email address.';
        elseif (strlen($password) < 8) $error = 'Password must be at least 8 characters.';
        elseif ($password !== $repeat) $error = 'Passwords do not match.';
        elseif ($users->emailExists($email)) $error = 'An account with this email already exists. Try logging in instead.';
        if ($error) {
            flash('error', $error);
            redirect('signup');
        }
        try {
            $id = $users->create($fullName, $email, $password);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                flash('error', 'An account with this email already exists.');
                redirect('signup');
            }
            throw $exception;
        }
        unset($_SESSION['old_signup']);
        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
        redirect('dashboard');
    }
    public function googleLogin(): void {
        $this->startGoogleAuth('login');
    }

    public function googleSignup(): void {
        $this->startGoogleAuth('signup');
    }

    private function startGoogleAuth(string $mode): void {
        if (current_user()) redirect('dashboard');

        $config = $this->googleConfig();
        if (!$config) {
            if ($this->googleDemoEnabled()) {
                redirect('google-demo', ['mode' => $mode]);
            }
            flash('error', 'Google sign-in is not configured yet. Please use email and password for now.');
            redirect($mode === 'signup' ? 'signup' : 'login');
        }

        $state = bin2hex(random_bytes(32));
        $_SESSION['google_oauth_state'] = $state;
        $_SESSION['google_oauth_mode'] = $mode;

        $query = http_build_query([
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
        ]);

        header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $query);
        exit;
    }

    public function googleCallback(): void {
        $mode = $_SESSION['google_oauth_mode'] ?? 'login';
        $returnPage = $mode === 'signup' ? 'signup' : 'login';
        $expectedState = (string)($_SESSION['google_oauth_state'] ?? '');
        $receivedState = (string)($_GET['state'] ?? '');
        unset($_SESSION['google_oauth_state'], $_SESSION['google_oauth_mode']);

        if ($expectedState === '' || $receivedState === '' || !hash_equals($expectedState, $receivedState)) {
            flash('error', 'Google sign-in could not be verified. Please try again.');
            redirect($returnPage);
        }
        if (isset($_GET['error'])) {
            flash('error', 'Google sign-in was cancelled or denied. Please try again.');
            redirect($returnPage);
        }

        $code = (string)($_GET['code'] ?? '');
        $config = $this->googleConfig();
        if ($code === '' || !$config) {
            flash('error', 'Google sign-in could not be completed. Check the Google OAuth configuration.');
            redirect($returnPage);
        }

        $tokenResponse = $this->googleHttpRequest(
            'https://oauth2.googleapis.com/token',
            'POST',
            [
                'code' => $code,
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'redirect_uri' => $config['redirect_uri'],
                'grant_type' => 'authorization_code',
            ]
        );
        $token = is_array($tokenResponse) ? (string)($tokenResponse['access_token'] ?? '') : '';
        if ($token === '') {
            flash('error', 'Google could not verify your sign-in. Please try again.');
            redirect($returnPage);
        }

        $googleUser = $this->googleHttpRequest(
            'https://openidconnect.googleapis.com/v1/userinfo',
            'GET',
            [],
            $token
        );
        $email = is_array($googleUser) ? mb_strtolower(trim((string)($googleUser['email'] ?? ''))) : '';
        $name = trim((string)($googleUser['name'] ?? ''));
        $emailVerified = is_array($googleUser) && ($googleUser['email_verified'] ?? false) === true;

        if (!$emailVerified || !filter_var($email, FILTER_VALIDATE_EMAIL) || $name === '') {
            flash('error', 'Google did not return a verified email address. Please use email and password instead.');
            redirect($returnPage);
        }

        $users = new User();
        $user = $users->findByEmail($email);

        if ($user === null) {
            if ($mode !== 'signup') {
                flash('error', 'No CodeMate account is linked to this Google email. Please sign up first.');
                redirect('login');
            }

            try {
                $randomPassword = bin2hex(random_bytes(32));
                $userId = $users->create($name, $email, $randomPassword);
                $user = $users->findById($userId);
            } catch (PDOException $exception) {
                // Handle a duplicate account created between the lookup and insert.
                if ($exception->getCode() !== '23000') throw $exception;
                $user = $users->findByEmail($email);
            }
        } elseif ($mode === 'signup') {
            flash('error', 'An account with this Google email already exists. Please log in instead.');
            redirect('login');
        }

        if (!$user || $user['account_status'] !== 'active') {
            flash('error', 'This CodeMate account is unavailable. Please contact an administrator.');
            redirect($returnPage);
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['user_id'];
        redirect('dashboard');
    }

    // Demo mode: a built-in "Choose an account" page that behaves like Google sign-in
    // but never contacts Google. Turn it on with a google.php that contains
    // only: return ['demo_mode' => true];
    private function googleDemoEnabled(): bool {
        $path = APP_ROOT . '/backend/config/google.php';
        if (!is_file($path)) return false;

        $config = require $path;
        return is_array($config) && ($config['demo_mode'] ?? false) === true;
    }

    public function showGoogleDemo(): void {
        if (current_user()) redirect('dashboard');
        if (!$this->googleDemoEnabled()) {
            flash('error', 'Google sign-in is not configured yet. Please use email and password for now.');
            redirect('login');
        }

        $mode = ($_GET['mode'] ?? '') === 'signup' ? 'signup' : 'login';
        $this->render('auth/google-demo', [
            'pageTitle' => $mode === 'signup' ? 'Sign up with Google' : 'Log in with Google',
            'mode' => $mode,
            'error' => get_flash('error'),
            'old' => $_SESSION['old_google_demo'] ?? ['full_name' => '', 'email' => ''],
        ], 'auth');
        unset($_SESSION['old_google_demo']);
    }

    public function googleDemoSubmit(): void {
        verify_csrf();
        if (current_user()) redirect('dashboard');
        if (!$this->googleDemoEnabled()) redirect('login');

        $mode = input('mode') === 'signup' ? 'signup' : 'login';
        $name = input('full_name');
        $email = mb_strtolower(input('email'));
        $_SESSION['old_google_demo'] = ['full_name' => $name, 'email' => $email];

        $users = new User();
        $user = $users->findByEmail($email);
        $error = null;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            $error = 'Please enter a valid email address.';
        } elseif ($mode === 'signup' && ($name === '' || mb_strlen($name) > 100)) {
            $error = 'Please enter your name (up to 100 characters).';
        } elseif ($mode === 'signup' && $user !== null) {
            flash('error', 'An account with this Google email already exists. Please log in instead.');
            redirect('login');
        } elseif ($mode === 'login' && $user === null) {
            flash('error', 'No CodeMate account is linked to this Google email. Please sign up first.');
            redirect('login');
        } elseif ($mode === 'login' && $user['role'] === 'admin') {
            $error = 'Admin accounts cannot use the Google demo. Please log in with email and password.';
        } elseif ($mode === 'login' && $user['account_status'] !== 'active') {
            $error = 'This CodeMate account is unavailable. Please contact an administrator.';
        }

        if ($error !== null) {
            flash('error', $error);
            redirect('google-demo', ['mode' => $mode]);
        }

        if ($mode === 'signup') {
            try {
                $userId = $users->create($name, $email, bin2hex(random_bytes(32)));
            } catch (PDOException $exception) {
                if ($exception->getCode() === '23000') {
                    flash('error', 'An account with this Google email already exists. Please log in instead.');
                    redirect('login');
                }
                throw $exception;
            }
        } else {
            $userId = (int)$user['user_id'];
        }

        unset($_SESSION['old_google_demo']);
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        redirect('dashboard');
    }

    private function googleConfig(): ?array {
        $path = APP_ROOT . '/backend/config/google.php';
        if (!is_file($path)) return null;

        $config = require $path;
        if (!is_array($config)) return null;

        foreach (['client_id', 'client_secret', 'redirect_uri'] as $key) {
            if (empty($config[$key]) || str_contains((string)$config[$key], 'YOUR_')) return null;
        }
        return $config;
    }

    private function googleHttpRequest(string $url, string $method, array $data = [], string $accessToken = ''): ?array {
        $headers = "Accept: application/json\r\n";
        if ($accessToken !== '') $headers .= "Authorization: Bearer {$accessToken}\r\n";
        $content = '';
        if ($method === 'POST') {
            $headers .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $content = http_build_query($data);
        }

        $context = stream_context_create(['http' => [
            'method' => $method,
            'header' => $headers,
            'content' => $content,
            'timeout' => 15,
            'ignore_errors' => true,
        ]]);
        $response = @file_get_contents($url, false, $context);
        if ($response === false) return null;

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : null;
    }

    public function logout(): void {
        verify_csrf();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time()-42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        session_start();
        flash('success', 'You have been logged out.');
        redirect('login');
    }
}
