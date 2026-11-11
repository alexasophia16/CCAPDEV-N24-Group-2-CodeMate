<?php
// Entry point: index.php?page=<name>, see backend/config/routes.php

define('APP_ROOT', __DIR__);

require APP_ROOT . '/backend/core/bootstrap.php';

$router = new Router(require APP_ROOT . '/backend/config/routes.php');
$router->dispatch();
