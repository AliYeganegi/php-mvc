<?php

namespace App\Core;

use App\Core\Database\Connection;
use App\Core\View;
use \App\Core\Autoloader;
use \App\Core\ErrorHandler;

// error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('APP_START', microtime(true));
define('CONFIG_PATH', BASE_PATH . '/config');
define('VIEW_PATH', BASE_PATH . '/views');

require_once BASE_PATH . '/src/Core/Autoloader.php';

Autoloader::register();
ErrorHandler::register();

class Bootstrap
{
    public static function init(): void
    {
        self::loadConfig();
        self::initDatabase();
        
        View::setViewsPath(VIEW_PATH);    
    }
    
    protected static function loadConfig(): void
    {
        $dbConfigFile = CONFIG_PATH . '/database.php';
        if (file_exists($dbConfigFile)) {
            $dbConfig = require $dbConfigFile;
            Connection::setConfig($dbConfig);
        }
    }

    protected static function initDatabase(): void
    {
        try {
            Connection::getPDO();
        } catch (\Exception $e) {
            error_log('Database connection failed: ' . $e->getMessage());
        }
    }
}

Bootstrap::init();