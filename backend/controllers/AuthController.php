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
            'old'=>$_SESSION['old_signup'] ?? ['full_name'=>'','email'=>'','role'=>'tutee']
        ], 'auth');
        unset($_SESSION['old_signup']);
    }
    public function signup(): void {
        verify_csrf();
        $fullName = input('full_name');
        $email = mb_strtolower(input('email'));
        $password = (string)($_POST['password'] ?? '');
        $repeat = (string)($_POST['repeat_password'] ?? '');
        $role = input('role');
        $_SESSION['old_signup'] = ['full_name'=>$fullName, 'email'=>$email, 'role'=>$role];
        $users = new User();
        $error = null;
        if ($fullName === '' || mb_strlen($fullName) > 100) $error = 'Please enter a name (up to 100 characters).';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) $error = 'Please enter a valid email address.';
        elseif (!in_array($role, ['tutee', 'tutor'], true)) $error = 'Please choose Tutor or Tutee.';
        elseif (strlen($password) < 8) $error = 'Password must be at least 8 characters.';
        elseif ($password !== $repeat) $error = 'Passwords do not match.';
        elseif ($users->emailExists($email)) $error = 'An account with this email already exists. Try logging in instead.';
        if ($error) {
            flash('error', $error);
            redirect('signup');
        }
        try {
            $id = $users->create($fullName, $email, $password, $role);
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

    // ---------------------------------------------------------------------
    // Real Google sign-in (OAuth 2.0 authorization-code flow)
    // ---------------------------------------------------------------------
    private function googleConfig(): array {
        $file = APP_ROOT . '/backend/config/google.php';
        $cfg = is_file($file) ? require $file : [];
        return is_array($cfg) ? $cfg : [];
    }

    private function googleConfigured(array $cfg): bool {
        foreach (['client_id', 'client_secret', 'redirect_uri'] as $key) {
            $value = trim((string)($cfg[$key] ?? ''));
            if ($value === '' || str_starts_with($value, 'YOUR_')) return false;
        }
        return true;
    }

    // Small cURL helper. Returns the decoded JSON body, or null on any failure.
    private function googleRequest(string $url, ?array $post = null, ?string $bearer = null): ?array {
        if (!function_exists('curl_init')) return null;
        $headers = ['Accept: application/json'];
        if ($bearer !== null) $headers[] = 'Authorization: Bearer ' . $bearer;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => $headers,
        ]);
        if ($post !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body === false || $status < 200 || $status >= 300) return null;
        $data = json_decode((string) $body, true);
        return is_array($data) ? $data : null;
    }

    // GET google-start&mode=login|signup : sends the browser to Google's account chooser
    public function googleStart(): void {
        if (current_user()) redirect('dashboard');
        $mode = ($_GET['mode'] ?? 'login') === 'signup' ? 'signup' : 'login';
        $cfg = $this->googleConfig();

        if (!$this->googleConfigured($cfg)) {
            flash('error', 'Google sign-in is not set up yet. Please use email and password.');
            redirect($mode === 'signup' ? 'signup' : 'login');
        }

        $state = bin2hex(random_bytes(16));
        $_SESSION['google_oauth'] = ['state' => $state, 'mode' => $mode, 'time' => time()];

        $query = http_build_query([
            'client_id'     => $cfg['client_id'],
            'redirect_uri'  => $cfg['redirect_uri'],
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'state'         => $state,
            'prompt'        => 'select_account',
        ]);
        header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $query);
        exit;
    }

    // GET google-callback : Google sends the person back here with ?code=...&state=...
    public function googleCallback(): void {
        $cfg = $this->googleConfig();
        $oauth = $_SESSION['google_oauth'] ?? null;
        unset($_SESSION['google_oauth']);
        $mode = ($oauth['mode'] ?? 'login') === 'signup' ? 'signup' : 'login';
        $back = $mode === 'signup' ? 'signup' : 'login';

        if (!$this->googleConfigured($cfg) || !is_array($oauth)
            || !hash_equals((string)$oauth['state'], (string)($_GET['state'] ?? ''))
            || time() - (int)$oauth['time'] > 600) {
            flash('error', 'Google sign-in expired or was invalid. Please try again.');
            redirect($back);
        }
        if (isset($_GET['error']) || empty($_GET['code'])) {
            flash('error', 'Google sign-in was cancelled.');
            redirect($back);
        }

        // 1. trade the one-time code for an access token (server-to-server, over HTTPS)
        $token = $this->googleRequest('https://oauth2.googleapis.com/token', [
            'code'          => (string) $_GET['code'],
            'client_id'     => $cfg['client_id'],
            'client_secret' => $cfg['client_secret'],
            'redirect_uri'  => $cfg['redirect_uri'],
            'grant_type'    => 'authorization_code',
        ]);
        if (!$token || empty($token['access_token'])) {
            flash('error', 'Could not complete Google sign-in. Please try again.');
            redirect($back);
        }

        // 2. ask Google who the person is
        $profile = $this->googleRequest('https://openidconnect.googleapis.com/v1/userinfo', null, $token['access_token']);
        $email = mb_strtolower(trim((string)($profile['email'] ?? '')));
        $verified = filter_var($profile['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if (!$profile || $email === '' || !$verified || mb_strlen($email) > 150
            || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Your Google account did not return a verified email address.');
            redirect($back);
        }
        $name = trim((string)($profile['name'] ?? ''));
        if ($name === '') $name = strstr($email, '@', true) ?: 'Google User';
        $name = mb_substr($name, 0, 100);

        // 3. existing account -> sign in
        $existing = (new User())->findByEmail($email);
        if ($existing) {
            if ($existing['account_status'] !== 'active') {
                flash('error', 'This account has been deactivated. Please contact an admin.');
                redirect('login');
            }
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $existing['user_id'];
            redirect('dashboard');
        }

        // 4. no account yet
        if ($mode === 'login') {
            flash('error', 'No CodeMate account uses that Google email yet. Please sign up first.');
            redirect('signup');
        }
        $_SESSION['google_pending'] = ['email' => $email, 'full_name' => $name, 'time' => time()];
        redirect('google-role');
    }

    // GET google-role : new Google users choose Tutor or Tutee
    public function googleRole(): void {
        $pending = $this->googlePending();
        $this->render('auth/google-role', [
            'pageTitle'  => 'Choose Your Account Type',
            'googleUser' => $pending,
            'oldRole'    => 'tutee',
            'error'      => get_flash('error'),
        ], 'auth');
    }

    // POST google-role : create the account
    public function googleRoleSubmit(): void {
        verify_csrf();
        $pending = $this->googlePending();
        $role = input('role');
        if (!in_array($role, ['tutee', 'tutor'], true)) {
            flash('error', 'Please choose whether you want to register as a Tutor or Tutee.');
            redirect('google-role');
        }
        $users = new User();
        if ($users->emailExists($pending['email'])) {
            unset($_SESSION['google_pending']);
            flash('error', 'An account with this email already exists. Please sign in instead.');
            redirect('login');
        }
        try {
            // Google accounts get a random password; they always sign in through Google.
            $id = $users->create($pending['full_name'], $pending['email'], bin2hex(random_bytes(32)), $role);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                unset($_SESSION['google_pending']);
                flash('error', 'An account with this email already exists. Please sign in instead.');
                redirect('login');
            }
            throw $exception;
        }
        unset($_SESSION['google_pending']);
        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
        redirect('dashboard');
    }

    // The Google profile saved by googleCallback(), or back to Sign Up if missing/expired (15 min)
    private function googlePending(): array {
        $pending = $_SESSION['google_pending'] ?? null;
        if (!is_array($pending) || time() - (int)$pending['time'] > 900) {
            unset($_SESSION['google_pending']);
            flash('error', 'Your Google sign-up expired. Please try again.');
            redirect('signup');
        }
        return $pending;
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
