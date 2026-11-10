<?php
// Entry point: index.php?page=<name>, see backend/config/routes.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('APP_ROOT', __DIR__);

require APP_ROOT . '/backend/core/bootstrap.php';

$router = new Router(require APP_ROOT . '/backend/config/routes.php');
$router->dispatch();
