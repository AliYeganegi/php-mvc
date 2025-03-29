<?php
// src/Core/Autoloader.php
namespace App\Core;

class Autoloader
{
    /**
     * Register the autoloader
     */
    public static function register()
    {
        spl_autoload_register([self::class, 'loadClass']);
    }

    /**
     * Load a class based on namespace and class name
     */
    public static function loadClass($class)
    {
        // Convert namespace separator to directory separator
        $prefix = 'App\\';
        
        // Only handle our own namespace
        if (strpos($class, $prefix) !== 0) {
            return;
        }
        
        // Get the relative class name
        $relativeClass = substr($class, strlen($prefix));
        
        // Convert namespace to path
        $file = BASE_PATH . '/src/' . str_replace('\\', '/', $relativeClass) . '.php';
        
        // If file exists, require it
        if (file_exists($file)) {
            require_once $file;
        }
    }
}