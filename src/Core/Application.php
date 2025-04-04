<?php
// src/Core/Application.php
namespace App\Core;

class Application
{
    /**
     * @var Request
     */
    private $request;
    
    /**
     * @var Response
     */
    private $response;
    
    /**
     * @var array
     */
    private $routes = [];
    
    /**
     * Create a new application instance
     */
    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
        
        // Load routes
        if (file_exists(BASE_PATH . '/config/routes.php')) {
            $this->routes = require_once BASE_PATH . '/config/routes.php';
        }

        View::setViewsPath(dirname(__DIR__, 2) . '/views');
        View::setLayout('default');
    }
    
    /**
     * Run the application
     */
    public function run()
    {
        try {
            // Get request path and method
            $path = $this->request->getPath();
            $method = $this->request->getMethod();
            
            // Find matching route
            $handler = $this->findRoute($path, $method);
            
            if ($handler) {
                // Execute middleware (will be implemented in future steps)
                
                // Execute route handler
                $response = $this->executeHandler($handler);
                
                // If response is a Response object, send it
                if ($response instanceof Response) {
                    $response->send();
                } else {
                    // Convert to Response and send
                    $this->response->setContent($response)->send();
                }
            } else {
                // No route found, return 404
                $this->response->setStatusCode(404)
                    ->setContent('<h1>404 Not Found</h1><p>The requested page was not found.</p>')
                    ->send();
            }
        } catch (\Exception $e) {
            // Pass to exception handler
            ErrorHandler::handleException($e);
        }
    }
    
    /**
     * Find a matching route for the given path and method
     */
    private function findRoute($path, $method)
    {
        // Simple routing for now, will be enhanced in future steps
        $path = trim($path, '/');
        $path = $path ?: 'index';
        
        if (isset($this->routes[$method][$path])) {
            return $this->routes[$method][$path];
        }
        
        return null;
    }
    
    /**
     * Execute a route handler
     */
    private function executeHandler($handler)
    {
        // If handler is a closure/function
        if (is_callable($handler)) {
            return call_user_func($handler, $this->request);
        }
        
        // If handler is a string in format "ControllerName@method"
        if (is_string($handler) && strpos($handler, '@') !== false) {
            list($controller, $method) = explode('@', $handler);
            $controllerClass = "\\App\\Controllers\\$controller";
            
            if (class_exists($controllerClass)) {
                $controllerInstance = new $controllerClass();
                
                if (method_exists($controllerInstance, $method)) {
                    return call_user_func([$controllerInstance, $method], $this->request);
                }
            }
        }
        
        throw new \RuntimeException("Handler not found or not callable");
    }
}