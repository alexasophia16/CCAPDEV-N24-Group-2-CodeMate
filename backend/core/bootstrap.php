<?php
date_default_timezone_set('Asia/Manila');

session_start();

require APP_ROOT . '/backend/core/helpers.php';

spl_autoload_register(function ($class) {
    foreach (['core', 'controllers', 'models'] as $folder) {
        $file = APP_ROOT . "/backend/$folder/$class.php";
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

if (!is_file(APP_ROOT . '/backend/config/database.php')) {
    http_response_code(500);
    exit('Missing backend/config/database.php. Copy backend/config/database.example.php to backend/config/database.php and enter your database login.');
}
