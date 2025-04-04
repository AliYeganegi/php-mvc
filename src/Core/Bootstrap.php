<?php
// src/Core/Bootstrap.php
namespace App\Core;

use App\Core\Database\Connection;

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

/**
 * Bootstrap class for application initialization
 */
class Bootstrap
{
    /**
     * Initialize the application
     */
    public static function init(): void
    {
        // Load configuration files
        self::loadConfig();
        
        // Initialize database connection
        self::initDatabase();
        
        // Set up view paths
        \App\Core\View::setViewsPath(VIEW_PATH);
        
        // Additional initialization can be added here
    }
    
    /**
     * Load configuration files
     */
    protected static function loadConfig(): void
    {
        // Load database configuration if file exists
        $dbConfigFile = CONFIG_PATH . '/database.php';
        if (file_exists($dbConfigFile)) {
            $dbConfig = require $dbConfigFile;
            Connection::setConfig($dbConfig);
        }
        
        // Load other configurations as needed
        // Example: $routesConfig = require CONFIG_PATH . '/routes.php';
    }
    
    /**
     * Initialize database connection
     */
    protected static function initDatabase(): void
    {
        try {
            // This will establish the connection if not already connected
            Connection::getPDO();
        } catch (\Exception $e) {
            // Log the error but don't halt execution
            error_log('Database connection failed: ' . $e->getMessage());
        }
    }
}

// Run the bootstrap initialization
Bootstrap::init();