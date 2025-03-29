<?php
// public/index.php

// Define the application root path
define('BASE_PATH', dirname(__DIR__));

// Load the bootstrap file
require_once BASE_PATH . '/src/Core/Bootstrap.php';

// Create and run the application
$app = new \App\Core\Application();
$app->run();