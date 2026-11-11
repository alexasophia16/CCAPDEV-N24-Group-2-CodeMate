<?php
// Entry point: index.php?page=<name>, see backend/config/routes.php

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('APP_ROOT', __DIR__);

require APP_ROOT . '/backend/core/bootstrap.php';

$router = new Router(require APP_ROOT . '/backend/config/routes.php');
$router->dispatch();
