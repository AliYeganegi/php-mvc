<?php

namespace App\Core;

class ErrorHandler
{
    public static function register()
    {
        set_error_handler([self::class, 'handleError']);
        
        set_exception_handler([self::class, 'handleException']);
        
        register_shutdown_function([self::class, 'handleShutdown']);
    }
    
    public static function handleError($level, $message, $file, $line)
    {
        if (!(error_reporting() & $level)) {
            return;
        }
        
        throw new \ErrorException($message, 0, $level, $file, $line);
    }
    
    public static function handleException($exception)
    {
        $response = new Response();
        
        // In development mode, show detailed error
        if (getenv('APP_ENV') !== 'production') {
            $response->setStatusCode(500);
            $message = sprintf(
                "<h1>Error: %s</h1><p>%s</p><p>In %s on line %d</p>",
                get_class($exception),
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            );
            
            // Add stack trace in development
            $message .= "<h2>Stack Trace:</h2><pre>" . $exception->getTraceAsString() . "</pre>";
            
            $response->setContent($message);
        } else {
            // In production, show generic error
            $response->setStatusCode(500);
            $response->setContent("<h1>Server Error</h1><p>An error occurred. Please try again later.</p>");
        }
        
        $response->send();
    }
    
    public static function handleShutdown()
    {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::handleError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }
}