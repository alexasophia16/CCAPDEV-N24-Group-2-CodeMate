<?php

// Creates the one database connection the whole request shares.
// The login details come from backend/config/database.php (copied from database.example.php),
// so each computer or server can use its own without changing the code.
class Database
{
    // static: saved on the class, so every model reuses the same connection
    private static ?PDO $connection = null;

    // Connects the first time it's called and returns the same PDO object after that.
    public static function connection(): PDO
    {
        if (self::$connection === null) {
            $config = require APP_ROOT . '/backend/config/database.php';

            // utf8mb4 so names with ñ, accents or emoji are stored correctly
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $config['host'],
                $config['port'],
                $config['database']
            );

            self::$connection = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // an SQL error throws an exception instead of failing silently
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // rows come back as arrays keyed by column name, like $row['full_name']
                PDO::ATTR_EMULATE_PREPARES   => false,                  // MariaDB itself prepares the statements, so values never mix into the SQL text
            ]);

            // Philippine time, matching date_default_timezone_set() in bootstrap.php,
            // so NOW() in SQL and time() in PHP agree
            self::$connection->exec("SET time_zone = '+08:00'");
        }

        return self::$connection;
    }
}
