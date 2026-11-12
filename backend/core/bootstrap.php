<?php
//this sets the timezone. without this, PHP may use server's timezone. then checks like "is this slot in the future?"
//so the times shown on bookings and notifications would be off by 8hrs. the db connection also sets +08:00, so PHP
//and MariaDB agree.
date_default_timezone_set('Asia/Manila');

//this is how the app remembers who's logged in between pages. this reads the browser's session cookie and loads
//$_SESSION, which holds user_id and flash messages. 
session_start(); 

require APP_ROOT . '/backend/core/helpers.php'; //defines the small functions every file uses, like e(), url(), redirect(), etc.

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
