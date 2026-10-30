<?php

class AuthController extends Controller
{
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

    public function login(): void
    {
        $email = input('email');
        $password = $_POST['password'] ?? '';
        $user = (new User())->findByEmail($email);

        $_SESSION['old_email'] = $email;

        if ($email === '' || $password === '') {
            flash('error', 'Please enter your email and password.');
            redirect('login');
        }
        if ($user === null || $user['password'] !== $password) {
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

    public function showSignup(): void
    {
        if (current_user()) {
            redirect('dashboard');
        }

        $this->render('auth/signup', [
            'pageTitle' => 'Sign Up',
            'error'     => get_flash('error'),
            'old'       => $_SESSION['old_signup'] ?? ['full_name' => '', 'email' => ''],
        ], 'auth');
        unset($_SESSION['old_signup']);
    }

    // new accounts start as tutees
    public function signup(): void
    {
        $fullName = input('full_name');
        $email = input('email');
        $password = $_POST['password'] ?? '';
        $repeatPassword = $_POST['repeat_password'] ?? '';
        $users = new User();

        $_SESSION['old_signup'] = ['full_name' => $fullName, 'email' => $email];

        $error = null;
        if ($fullName === '') {
            $error = 'Please enter your name.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($password !== $repeatPassword) {
            $error = 'Passwords do not match.';
        } elseif ($users->emailExists($email)) {
            $error = 'An account with this email already exists. Try logging in instead.';
        }

        if ($error !== null) {
            flash('error', $error);
            redirect('signup');
        }

        unset($_SESSION['old_signup']);
        $_SESSION['user_id'] = $users->create($fullName, $email, $password);
        redirect('dashboard');
    }

    public function logout(): void
    {
        unset($_SESSION['user_id']);
        flash('success', 'You have been logged out.');
        redirect('login');
    }
}
