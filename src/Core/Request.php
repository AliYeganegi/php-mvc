<?php

namespace App\Core;

class Request
{
    private $get = [];

    private $post = [];

    private $files = [];
    
    private $cookies = [];
    
    private $server = [];
    
    private $headers = [];
    
    private $content;
    
    private $uri;
    
    private $method;
    
    private $pathParts = [];
    
    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->files = $_FILES;
        $this->cookies = $_COOKIE;
        $this->server = $_SERVER;
        
        // Extract headers
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $this->headers[$header] = $value;
            }
        }
        
        $this->uri = $this->getUri();
        
        $this->method = $this->getMethod();
        
        $this->content = file_get_contents('php://input');
        
        $path = parse_url($this->uri, PHP_URL_PATH);
        $path = trim($path, '/');
        $this->pathParts = $path ? explode('/', $path) : [];
    }
    
    public function getUri()
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }
    
    public function getPath()
    {
        return parse_url($this->getUri(), PHP_URL_PATH);
    }
    
    public function getMethod()
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }
    
    public function isAjax()
    {
        return isset($this->headers['X-Requested-With']) && 
               strtolower($this->headers['X-Requested-With']) === 'xmlhttprequest';
    }
    
    public function get($key = null, $default = null)
    {
        if ($key === null) {
            return $this->get;
        }
        
        return $this->get[$key] ?? $default;
    }
    
    public function post($key = null, $default = null)
    {
        if ($key === null) {
            return $this->post;
        }
        
        return $this->post[$key] ?? $default;
    }
    
    public function input($key, $default = null)
    {
        return $this->post($key) ?? $this->get($key) ?? $default;
    }
    
    public function all()
    {
        return array_merge($this->get, $this->post);
    }
    
    public function header($key, $default = null)
    {
        $key = str_replace(' ', '-', ucwords(str_replace('-', ' ', strtolower($key))));
        return $this->headers[$key] ?? $default;
    }
    
    public function headers()
    {
        return $this->headers;
    }

    public function getContent()
    {
        return $this->content;
    }
    
    public function json($assoc = true)
    {
        if (strpos($this->header('Content-Type', ''), 'application/json') !== false) {
            return json_decode($this->getContent(), $assoc);
        }
        
        return null;
    }
}