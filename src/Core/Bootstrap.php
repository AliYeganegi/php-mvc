<?php
// src/Core/Bootstrap.php
namespace App\Core;

// Set up error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define application constants
define('APP_START', microtime(true));
define('CONFIG_PATH', BASE_PATH . '/config');
define('VIEW_PATH', BASE_PATH . '/views');

// Require the autoloader
require_once BASE_PATH . '/src/Core/Autoloader.php';

// Register the autoloader
\App\Core\Autoloader::register();

// Set up error handling
\App\Core\ErrorHandler::register();

// Load configuration
// Will be implemented in a future step