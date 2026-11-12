<?php
// Entry point: index.php?page=<name>, see backend/config/routes.php


define('APP_ROOT', __DIR__); //__DIR__ is the folder this file is in, the project root

require APP_ROOT . '/backend/core/bootstrap.php'; //runs the setup in bootstrap.php

$router = new Router(require APP_ROOT . '/backend/config/routes.php'); //returns an array mapping each "METHOD page" to a controller and method
//the router reads $_GET['page'] and the request method (GET or POST), looks up "GET find-tutor" in the list, creates TutorController,
//and calls find().
$router->dispatch(); 
