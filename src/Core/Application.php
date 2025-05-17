<?php

namespace App\Core;

class Application
{
    private $request;
    
    private $response;
    
    private $routes = [];
    
    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
        
        if (file_exists(BASE_PATH . '/config/routes.php')) {
            $this->routes = require_once BASE_PATH . '/config/routes.php';
        }

        View::setViewsPath(dirname(__DIR__, 2) . '/views');
        View::setLayout('default');
    }
    
    public function run()
    {
        try {
            $path = $this->request->getPath();
            $method = $this->request->getMethod();
            
            $handler = $this->findRoute($path, $method);
            
            if ($handler) {                
                $response = $this->executeHandler($handler);
                
                if ($response instanceof Response) {
                    $response->send();
                } else {
                    $this->response->setContent($response)->send();
                }
            } else {
                // 404 error
                $this->response->setStatusCode(404)
                    ->setContent('<h1>404 Not Found</h1><p>Page was not found</p>')
                    ->send();
            }
        } catch (\Exception $e) {
            ErrorHandler::handleException($e);
        }
    }
    
    private function findRoute($path, $method)
    {
        $path = trim($path, '/');
        $path = $path ?: 'index';
        
        if (isset($this->routes[$method][$path])) {
            return $this->routes[$method][$path];
        }
        
        return null;
    }
    
    private function executeHandler($handler)
    {
        if (is_callable($handler)) {
            return call_user_func($handler, $this->request);
        }
        
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