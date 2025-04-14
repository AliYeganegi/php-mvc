<?php

namespace App\Core;

class Autoloader
{
    public static function register()
    {
        spl_autoload_register([self::class, 'loadClass']);
    }

    public static function loadClass($class)
    {
        $prefix = 'App\\';
        
        if (strpos($class, $prefix) !== 0) {
            return;
        }
        
        // Get the relative class name
        $relativeClass = substr($class, strlen($prefix));
        
        // namespace to path
        $file = BASE_PATH . '/src/' . str_replace('\\', '/', $relativeClass) . '.php';
        
        if (file_exists($file)) {
            require_once $file;
        }
    }
}