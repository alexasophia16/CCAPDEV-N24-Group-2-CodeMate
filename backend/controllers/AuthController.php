<?php
class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (current_user()) redirect('dashboard');
        $this->render('auth/login', [
            'pageTitle' => 'Log In',
            'error' => get_flash('error'),
            'success' => get_flash('success'),
            'email' => $_SESSION['old_email'] ?? '',
        ], 'auth');
        unset($_SESSION['old_email']);
    }

    public function login(): void
    {
        $email = strtolower(input('email'));
        $password = (string) ($_POST['password'] ?? '');
        $_SESSION['old_email'] = $email;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            flash('error', 'Please enter a valid email address and password.');
            redirect('login');
        }

        $users = new User();
        $user = $users->findByEmail($email);
        $valid = false;

        if ($user !== null) {
            $stored = (string) $user['password'];
            $valid = password_verify($password, $stored);

            // Seeded demo accounts may still have legacy plaintext passwords.
            if (!$valid && !str_starts_with($stored, '$2y$') && !str_starts_with($stored, '$argon2')
                && hash_equals($stored, $password)) {
                $valid = true;
                $users->updatePassword((int) $user['user_id'], $password);
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
        $_SESSION['user_id'] = (int) $user['user_id'];
        redirect('dashboard');
    }

    public function showSignup(): void
    {
        if (current_user()) redirect('dashboard');
        $this->render('auth/signup', [
            'pageTitle' => 'Sign Up',
            'error' => get_flash('error'),
            'old' => $_SESSION['old_signup'] ?? ['full_name' => '', 'email' => '', 'role' => 'tutee'],
        ], 'auth');
        unset($_SESSION['old_signup']);
    }

    public function signup(): void
    {
        $fullName = input('full_name');
        $email = strtolower(input('email'));
        $password = (string) ($_POST['password'] ?? '');
        $repeatPassword = (string) ($_POST['repeat_password'] ?? '');
        $role = input('role');

        $_SESSION['old_signup'] = ['full_name' => $fullName, 'email' => $email, 'role' => $role];
        $users = new User();
        $error = null;

        if ($fullName === '' || mb_strlen($fullName) > 100) {
            $error = 'Please enter your name (up to 100 characters).';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            $error = 'Please enter a valid email address.';
        } elseif (!in_array($role, ['tutee', 'tutor'], true)) {
            $error = 'Please choose whether you want to register as a tutor or tutee.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $repeatPassword) {
            $error = 'Passwords do not match.';
        } elseif ($users->emailExists($email)) {
            $error = 'An account with this email already exists. Try logging in instead.';
        }

        if ($error !== null) {
            flash('error', $error);
            redirect('signup');
        }

        try {
            $userId = $users->create($fullName, $email, $password, $role);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                flash('error', 'An account with this email already exists. Try logging in instead.');
                redirect('signup');
            }
            throw $exception;
        }

        unset($_SESSION['old_signup']);
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        redirect('dashboard');
    }

    public function googleLogin(): void
    {
        $this->startGoogleAuth('login');
    }

    public function googleSignup(): void
    {
        $this->startGoogleAuth('signup');
    }

    private function startGoogleAuth(string $mode): void
    {
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

    public function googleCallback(): void
    {
        $mode = $_SESSION['google_oauth_mode'] ?? 'login';
        $returnPage = $mode === 'signup' ? 'signup' : 'login';
        $expectedState = (string) ($_SESSION['google_oauth_state'] ?? '');
        $receivedState = (string) ($_GET['state'] ?? '');
        unset($_SESSION['google_oauth_state'], $_SESSION['google_oauth_mode']);

        if ($expectedState === '' || $receivedState === '' || !hash_equals($expectedState, $receivedState)) {
            flash('error', 'Google sign-in could not be verified. Please try again.');
            redirect($returnPage);
        }
        if (isset($_GET['error'])) {
            flash('error', 'Google sign-in was cancelled or denied. Please try again.');
            redirect($returnPage);
        }

        $code = (string) ($_GET['code'] ?? '');
        $config = $this->googleConfig();
        if ($code === '' || !$config) {
            flash('error', 'Google sign-in could not be completed. Check the Google OAuth configuration.');
            redirect($returnPage);
        }

        $tokenResponse = $this->googleHttpRequest('https://oauth2.googleapis.com/token', 'POST', [
            'code' => $code,
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'redirect_uri' => $config['redirect_uri'],
            'grant_type' => 'authorization_code',
        ]);
        $token = is_array($tokenResponse) ? (string) ($tokenResponse['access_token'] ?? '') : '';
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
        $email = is_array($googleUser) ? strtolower(trim((string) ($googleUser['email'] ?? ''))) : '';
        $name = trim((string) ($googleUser['name'] ?? ''));
        $verified = is_array($googleUser) && ($googleUser['email_verified'] ?? false) === true;

        if (!$verified || !filter_var($email, FILTER_VALIDATE_EMAIL) || $name === '') {
            flash('error', 'Google did not return a verified email address. Please try again.');
            redirect($returnPage);
        }

        $users = new User();
        $user = $users->findByEmail($email);

        if ($user !== null) {
            if ($mode === 'signup') {
                flash('error', 'An account with this Google email already exists. Please log in instead.');
                redirect('login');
            }
            if ($user['account_status'] !== 'active') {
                flash('error', 'This CodeMate account is unavailable. Please contact an administrator.');
                redirect('login');
            }
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['user_id'];
            redirect('dashboard');
        }

        if ($mode !== 'signup') {
            flash('error', 'No CodeMate account is linked to this Google email. Please sign up first.');
            redirect('login');
        }

        $_SESSION['pending_google_user'] = ['full_name' => $name, 'email' => $email];
        redirect('google-role');
    }

    public function showGoogleRole(): void
    {
        if (current_user()) redirect('dashboard');
        if (empty($_SESSION['pending_google_user']['email'])) {
            flash('error', 'Please continue with Google sign-up first.');
            redirect('signup');
        }

        $this->render('auth/google-role', [
            'pageTitle' => 'Choose Account Type',
            'error' => get_flash('error'),
            'googleUser' => $_SESSION['pending_google_user'],
            'oldRole' => $_SESSION['old_google_role'] ?? 'tutee',
        ], 'auth');
        unset($_SESSION['old_google_role']);
    }

    public function saveGoogleRole(): void
    {
        if (current_user()) redirect('dashboard');

        $pending = $_SESSION['pending_google_user'] ?? null;
        $role = input('role');
        if (!is_array($pending) || empty($pending['email'])) {
            flash('error', 'Your Google sign-up session expired. Please try again.');
            redirect('signup');
        }
        if (!in_array($role, ['tutee', 'tutor'], true)) {
            $_SESSION['old_google_role'] = $role;
            flash('error', 'Please choose either Tutor or Tutee.');
            redirect('google-role');
        }

        $users = new User();
        if ($users->emailExists($pending['email'])) {
            unset($_SESSION['pending_google_user']);
            flash('error', 'An account with this email already exists. Please log in instead.');
            redirect('login');
        }

        try {
            $userId = $users->create(
                $pending['full_name'],
                $pending['email'],
                bin2hex(random_bytes(32)),
                $role
            );
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                unset($_SESSION['pending_google_user']);
                flash('error', 'An account with this email already exists. Please log in instead.');
                redirect('login');
            }
            throw $exception;
        }

        unset($_SESSION['pending_google_user'], $_SESSION['old_google_role']);
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        redirect('dashboard');
    }

    private function googleDemoEnabled(): bool
    {
        $path = APP_ROOT . '/backend/config/google.php';
        if (!is_file($path)) return false;
        $config = require $path;
        return is_array($config) && ($config['demo_mode'] ?? false) === true;
    }

    public function showGoogleDemo(): void
    {
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

    public function googleDemoSubmit(): void
    {
        if (current_user()) redirect('dashboard');
        if (!$this->googleDemoEnabled()) redirect('login');

        $mode = input('mode') === 'signup' ? 'signup' : 'login';
        $name = input('full_name');
        $email = strtolower(input('email'));
        $_SESSION['old_google_demo'] = ['full_name' => $name, 'email' => $email];
        $users = new User();
        $user = $users->findByEmail($email);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            flash('error', 'Please enter a valid email address.');
            redirect('google-demo', ['mode' => $mode]);
        }

        if ($mode === 'signup') {
            if ($name === '' || mb_strlen($name) > 100) {
                flash('error', 'Please enter your name (up to 100 characters).');
                redirect('google-demo', ['mode' => $mode]);
            }
            if ($user !== null) {
                flash('error', 'An account with this email already exists. Please log in instead.');
                redirect('login');
            }
            $_SESSION['pending_google_user'] = ['full_name' => $name, 'email' => $email];
            unset($_SESSION['old_google_demo']);
            redirect('google-role');
        }

        if ($user === null) {
            flash('error', 'No CodeMate account is linked to this email. Please sign up first.');
            redirect('login');
        }
        if ($user['role'] === 'admin' || $user['account_status'] !== 'active') {
            flash('error', 'This account cannot use Google demo sign-in.');
            redirect('login');
        }

        unset($_SESSION['old_google_demo']);
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['user_id'];
        redirect('dashboard');
    }

    private function googleConfig(): ?array
    {
        $path = APP_ROOT . '/backend/config/google.php';
        if (!is_file($path)) return null;
        $config = require $path;
        if (!is_array($config)) return null;
        foreach (['client_id', 'client_secret', 'redirect_uri'] as $key) {
            if (empty($config[$key]) || str_contains((string) $config[$key], 'YOUR_')) return null;
        }
        return $config;
    }

    private function googleHttpRequest(string $url, string $method, array $data = [], string $accessToken = ''): ?array
    {
        $headers = "Accept: application/json\\r\\n";
        if ($accessToken !== '') $headers .= "Authorization: Bearer {$accessToken}\\r\\n";
        $content = '';
        if ($method === 'POST') {
            $headers .= "Content-Type: application/x-www-form-urlencoded\\r\\n";
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

    public function logout(): void
    {
        unset($_SESSION['user_id']);
        flash('success', 'You have been logged out.');
        redirect('login');
    }
}
