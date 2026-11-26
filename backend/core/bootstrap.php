<?php
//this sets the timezone. without this, PHP may use server's timezone. then checks like "is this slot in the future?"
//so the times shown on bookings and notifications would be off by 8hrs. the db connection also sets +08:00, so PHP
//and MariaDB agree.
date_default_timezone_set('Asia/Manila');

//errors go to the server's error log, never onto the page, so users can't see file paths or SQL
ini_set('display_errors', '0');
ini_set('log_errors', '1');

//security headers sent with every page
header('X-Frame-Options: DENY');                 //other sites can't show our pages in a frame (clickjacking)
header('X-Content-Type-Options: nosniff');       //browsers use the file type we send instead of guessing
header('Referrer-Policy: same-origin');          //our URLs aren't passed along to other sites

// Configure session cookie settings before starting the session
// AI was used to help in this part
session_set_cookie_params([
    'lifetime' => 0,          // Session-only lifetime (expires when the browser is closed)
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', // HTTPS-only once the site runs on https
    'httponly' => true,       // Prevents JS access to the cookie 
    'samesite' => 'Lax'       // Prevents CSRF
]);

//this is how the app remembers who's logged in between pages. this reads the browser's session cookie and loads
//$_SESSION, which holds user_id and flash messages. 
session_start(); 

require APP_ROOT . '/backend/core/helpers.php'; //defines the small functions every file uses, like e(), url(), redirect(), etc.

//logs the user out after 30 minutes with no activity, e.g. someone who left a lab PC logged in
if (isset($_SESSION['user_id']) && time() - ($_SESSION['last_activity'] ?? time()) > 30 * 60) {
    $_SESSION = [];
    session_regenerate_id(true);
    flash('error', 'You were logged out after 30 minutes of inactivity. Please log in again.');
    redirect('login');
}
$_SESSION['last_activity'] = time();

//loads class files automatically. when code says new Booking(), PHP calls this function with "Booking", which looks in core,
//controllers, and models for Booking.php and requires it.
spl_autoload_register(function ($class) {
    foreach (['core', 'controllers', 'models'] as $folder) {
        $file = APP_ROOT . "/backend/$folder/$class.php";
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

//checks the database config exists
if (!is_file(APP_ROOT . '/backend/config/database.php')) {
    http_response_code(500);
    exit('Missing backend/config/database.php. Copy backend/config/database.example.php to backend/config/database.php and enter your database login.');
}
