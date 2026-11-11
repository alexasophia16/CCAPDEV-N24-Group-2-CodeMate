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

    public function googleDemo(): void {
        $mode = ($_GET['mode'] ?? 'signup') === 'login' ? 'login' : 'signup';
        $this->render('auth/google-demo', [
            'pageTitle' => $mode === 'signup' ? 'Sign up with Google' : 'Sign in with Google',
            'mode' => $mode,
            'error' => get_flash('error'),
            'old' => $_SESSION['old_google'] ?? ['full_name'=>'', 'email'=>'', 'role'=>'tutee'],
        ], 'auth');
        unset($_SESSION['old_google']);
    }

    public function googleDemoSubmit(): void {
        verify_csrf();
        $mode = input('mode') === 'login' ? 'login' : 'signup';
        $fullName = input('full_name');
        $email = mb_strtolower(input('email'));
        $role = input('role');
        $_SESSION['old_google'] = ['full_name'=>$fullName, 'email'=>$email, 'role'=>$role];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            flash('error', 'Please enter a valid email address.');
            redirect('google-demo', ['mode'=>$mode]);
        }

        $users = new User();
        $existing = $users->findByEmail($email);
        if ($mode === 'login') {
            if (!$existing || $existing['account_status'] !== 'active') {
                flash('error', 'No active CodeMate account was found for that email. Please sign up first.');
                redirect('google-demo', ['mode'=>'login']);
            }
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$existing['user_id'];
            unset($_SESSION['old_google']);
            redirect('dashboard');
        }

        if ($fullName === '' || mb_strlen($fullName) > 100) {
            flash('error', 'Please enter your full name (up to 100 characters).');
            redirect('google-demo', ['mode'=>'signup']);
        }
        if (!in_array($role, ['tutee', 'tutor'], true)) {
            flash('error', 'Please choose whether you want to register as a Tutor or Tutee.');
            redirect('google-demo', ['mode'=>'signup']);
        }
        if ($existing) {
            flash('error', 'An account with this email already exists. Please sign in instead.');
            redirect('google-demo', ['mode'=>'login']);
        }

        try {
            $id = $users->create($fullName, $email, bin2hex(random_bytes(32)), $role);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                flash('error', 'An account with this email already exists. Please sign in instead.');
                redirect('google-demo', ['mode'=>'login']);
            }
            throw $exception;
        }

        unset($_SESSION['old_google']);
        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
        redirect('dashboard');
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
