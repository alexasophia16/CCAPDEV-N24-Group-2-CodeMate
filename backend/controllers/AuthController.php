<?php

// Log in, sign up, log out, and signing in with Google.
class AuthController extends Controller
{
    // Log in 

    // GET login
    public function showLogin(): void
    {
        if (current_user()) {
            redirect('dashboard');
        }

        $this->render('auth/login', [
            'pageTitle' => 'Log In',
            'error'     => get_flash('error'),
            'success'   => get_flash('success'),
            'email'     => $_SESSION['old_email'] ?? '',
        ], 'auth');
        unset($_SESSION['old_email']);
    }

    // POST login
    public function login(): void
    {
        $email = mb_strtolower(input('email'));
        $password = (string) ($_POST['password'] ?? '');
        $_SESSION['old_email'] = $email;   // so the email stays filled in after an error

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            flash('error', 'Please enter a valid email address and password.');
            redirect('login');
        }

        $user = (new User())->findByEmail($email);
        if (!$user || !$this->passwordMatches($password, (string) $user['password'])) {
            flash('error', 'Incorrect email or password.');
            redirect('login');
        }
        if ($user['account_status'] !== 'active') {
            flash('error', 'This account has been deactivated. Please contact an admin.');
            redirect('login');
        }

        unset($_SESSION['old_email']);
        $_SESSION['user_id'] = (int) $user['user_id'];
        redirect('dashboard');
    }

    // Passwords are plain text 
    private function passwordMatches(string $password, string $stored): bool
    {
        if (str_starts_with($stored, '$2y$')) {
            return password_verify($password, $stored);
        }
        return $stored === $password;
    }

    // Sign up 

    // GET signup
    public function showSignup(): void
    {
        if (current_user()) {
            redirect('dashboard');
        }

        $this->render('auth/signup', [
            'pageTitle' => 'Sign Up',
            'error'     => get_flash('error'),
            'old'       => $_SESSION['old_signup'] ?? ['full_name' => '', 'email' => '', 'role' => 'tutee'],
        ], 'auth');
        unset($_SESSION['old_signup']);
    }

    // POST signup
    public function signup(): void
    {
        $fullName = input('full_name');
        $email = mb_strtolower(input('email'));
        $password = (string) ($_POST['password'] ?? '');
        $repeat = (string) ($_POST['repeat_password'] ?? '');
        $role = input('role');
        $_SESSION['old_signup'] = ['full_name' => $fullName, 'email' => $email, 'role' => $role];
        $users = new User();

        $error = null;
        if ($fullName === '' || mb_strlen($fullName) > 100) {
            $error = 'Please enter a name (up to 100 characters).';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            $error = 'Please enter a valid email address.';
        } elseif (!in_array($role, ['tutee', 'tutor'], true)) {
            $error = 'Please choose Tutor or Tutee.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $repeat) {
            $error = 'Passwords do not match.';
        } elseif ($users->emailExists($email)) {
            $error = 'An account with this email already exists. Try logging in instead.';
        }

        if ($error !== null) {
            flash('error', $error);
            redirect('signup');
        }

        $userId = $this->createAccount($users, $fullName, $email, $password, $role, 'signup');
        unset($_SESSION['old_signup']);
        $_SESSION['user_id'] = $userId;
        redirect('dashboard');
    }

    // Creates the account. If someone else registered the same email a moment earlier,
    // the UNIQUE index on users.email throws error 23000; show a message instead of crashing.
    private function createAccount(User $users, string $name, string $email, string $password, string $role, string $backTo): int
    {
        try {
            return $users->create($name, $email, $password, $role);
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }
            flash('error', 'An account with this email already exists. Please log in instead.');
            redirect($backTo);
        }
    }

    // Log out 

    // POST logout: clear the session and its cookie
    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $cookie = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
        }
        session_destroy();

        // start a new, empty session to carry the message below. It needs a new id: with the
        // old one PHP doesn't send a cookie again, so the browser (whose cookie was just
        // deleted) would never see the message.
        session_id(session_create_id());
        session_start();
        flash('success', 'You have been logged out.');
        redirect('login');
    }

    // Google sign-in

    // reason the last Google request failed, shown in the error message
    private string $googleError = '';

    // GET google-start&mode=login|signup
    // sends the user to Google with our client id and a random "state"
    public function googleStart(): void
    {
        if (current_user()) {
            redirect('dashboard');
        }
        $mode = ($_GET['mode'] ?? '') === 'signup' ? 'signup' : 'login';
        $config = $this->googleConfig();

        if ($config === null) {
            flash('error', 'Google sign-in is not set up yet. Please use email and password.');
            redirect($mode);
        }

        // state is checked when Google sends the user back, so a forged callback is rejected
        $state = bin2hex(random_bytes(16));
        $_SESSION['google_oauth'] = ['state' => $state, 'mode' => $mode, 'time' => time()];

        header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id'     => $config['client_id'],
            'redirect_uri'  => $config['redirect_uri'],
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'state'         => $state,
            'prompt'        => 'select_account',
        ]));
        exit;
    }

    // GET google-callback&code=...&state=...
    // Google sends them back with a one-time code; we trade it for a token and ask Google for their verified email
    public function googleCallback(): void
    {
        $config = $this->googleConfig();
        $oauth = $_SESSION['google_oauth'] ?? null;
        unset($_SESSION['google_oauth']);
        $mode = ($oauth['mode'] ?? '') === 'signup' ? 'signup' : 'login';

        $validState = is_array($oauth)
            && hash_equals((string) $oauth['state'], (string) ($_GET['state'] ?? ''))
            && time() - (int) $oauth['time'] <= 600;   // 10 minutes
        if ($config === null || !$validState) {
            flash('error', 'Google sign-in expired or was invalid. Please try again.');
            redirect($mode);
        }
        if (isset($_GET['error']) || empty($_GET['code'])) {
            flash('error', 'Google sign-in was cancelled.');
            redirect($mode);
        }

        // trade the code for an access token (server to server, so the secret stays here)
        $token = $this->googleRequest('https://oauth2.googleapis.com/token', [
            'code'          => (string) $_GET['code'],
            'client_id'     => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'redirect_uri'  => $config['redirect_uri'],
            'grant_type'    => 'authorization_code',
        ]);
        if (!$token || empty($token['access_token'])) {
            flash('error', 'Could not complete Google sign-in (' . ($this->googleError ?: 'no access token') . '). Please try again.');
            redirect($mode);
        }

        // ask Google who the user is; only a verified email is accepted
        $profile = $this->googleRequest('https://openidconnect.googleapis.com/v1/userinfo', null, $token['access_token']);
        $email = mb_strtolower(trim((string) ($profile['email'] ?? '')));
        $verified = filter_var($profile['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if (!$verified || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            flash('error', 'Your Google account did not return a verified email address.');
            redirect($mode);
        }
        $name = trim((string) ($profile['name'] ?? '')) ?: (strstr($email, '@', true) ?: 'Google User');

        $user = (new User())->findByEmail($email);
        if ($user) {
            if ($user['account_status'] !== 'active') {
                flash('error', 'This account has been deactivated. Please contact an admin.');
                redirect('login');
            }
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['user_id'];
            redirect('dashboard');
        }

        if ($mode === 'login') {
            flash('error', 'No CodeMate account uses that Google email yet. Please sign up first.');
            redirect('signup');
        }
        $_SESSION['google_pending'] = ['email' => $email, 'full_name' => mb_substr($name, 0, 100), 'time' => time()];
        redirect('google-role');
    }

    // GET google-role: a new Google user picks Tutee or Tutor
    // logged in; new email -> googleRole() asks Tutee or Tutor
    public function googleRole(): void
    {
        $this->render('auth/google-role', [
            'pageTitle'  => 'Choose Your Account Type',
            'googleUser' => $this->googlePending(),
            'error'      => get_flash('error'),
        ], 'auth');
    }

    // POST google-role: create the account
    public function googleRoleSubmit(): void
    {
        $pending = $this->googlePending();
        $role = input('role');
        if (!in_array($role, ['tutee', 'tutor'], true)) {
            flash('error', 'Please choose whether you want to register as a Tutor or Tutee.');
            redirect('google-role');
        }

        unset($_SESSION['google_pending']);
        $users = new User();
        if ($users->emailExists($pending['email'])) {
            flash('error', 'An account with this email already exists. Please log in instead.');
            redirect('login');
        }

        // Google users always sign in through Google, so they get a random password
        $userId = $this->createAccount($users, $pending['full_name'], $pending['email'], bin2hex(random_bytes(32)), $role, 'login');
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        redirect('dashboard');
    }

    // the Google profile saved by googleCallback(); sends the user back if it's missing or older than 15 minutes
    private function googlePending(): array
    {
        $pending = $_SESSION['google_pending'] ?? null;
        if (!is_array($pending) || time() - (int) $pending['time'] > 900) {
            unset($_SESSION['google_pending']);
            flash('error', 'Your Google sign-up expired. Please try again.');
            redirect('signup');
        }
        return $pending;
    }

    // backend/config/google.php, or null if it's missing or still has the example values
    private function googleConfig(): ?array
    {
        $file = APP_ROOT . '/backend/config/google.php';
        $config = is_file($file) ? require $file : null;
        if (!is_array($config)) {
            return null;
        }
        foreach (['client_id', 'client_secret', 'redirect_uri'] as $key) {
            $value = trim((string) ($config[$key] ?? ''));
            if ($value === '' || str_starts_with($value, 'YOUR_')) {
                return null;
            }
        }
        return $config;
    }

    // Calls a Google API and returns the JSON as an array, or null (reason in $googleError).
    // Uses cURL when it's installed, otherwise PHP's built-in HTTPS support.
    private function googleRequest(string $url, ?array $post = null, ?string $accessToken = null): ?array
    {
        $headers = ['Accept: application/json'];
        if ($accessToken !== null) {
            $headers[] = 'Authorization: Bearer ' . $accessToken;
        }
        if ($post !== null) {
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        }
        $payload = $post !== null ? http_build_query($post) : null;

        if (function_exists('curl_init')) {
            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_HTTPHEADER     => $headers,
            ]);
            if ($payload !== null) {
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
            }
            $body = curl_exec($curl);
            $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $networkError = curl_error($curl);
            curl_close($curl);
        } elseif (in_array('https', stream_get_wrappers(), true)) {
            $context = stream_context_create(['http' => [
                'method'        => $payload !== null ? 'POST' : 'GET',
                'header'        => implode("\r\n", $headers),
                'content'       => $payload ?? '',
                'timeout'       => 10,
                'ignore_errors' => true,   // still read Google's error message on a 400/401
            ]]);
            $body = @file_get_contents($url, false, $context);
            // file_get_contents() fills $http_response_header; the first line is like "HTTP/1.1 200 OK"
            $status = preg_match('{^HTTP/\S+ (\d{3})}', $http_response_header[0] ?? '', $m) ? (int) $m[1] : 0;
            $networkError = $body === false ? (error_get_last()['message'] ?? 'request failed') : '';
        } else {
            $this->googleError = 'PHP needs the curl or openssl extension';
            error_log('Google sign-in: install php-curl or enable openssl');
            return null;
        }

        if ($body === false || $status === 0) {
            $this->googleError = 'the server could not reach Google';
            error_log("Google sign-in: could not reach $url ($networkError)");
            return null;
        }
        $data = json_decode($body, true);
        if ($status < 200 || $status >= 300 || !is_array($data)) {
            // log Google's error code only, never the tokens
            $this->googleError = (string) ($data['error'] ?? "HTTP $status");
            error_log("Google sign-in: $url returned $status " . ($data['error'] ?? '') . ' ' . ($data['error_description'] ?? ''));
            return null;
        }
        $this->googleError = '';
        return $data;
    }
}
