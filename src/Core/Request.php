<?php
// src/Core/Request.php
namespace App\Core;

class Request
{
    /**
     * @var array GET parameters
     */
    private $get = [];
    
    /**
     * @var array POST parameters
     */
    private $post = [];
    
    /**
     * @var array Uploaded files
     */
    private $files = [];
    
    /**
     * @var array Cookies
     */
    private $cookies = [];
    
    /**
     * @var array Server info
     */
    private $server = [];
    
    /**
     * @var array Request headers
     */
    private $headers = [];
    
    /**
     * @var string Raw request body
     */
    private $content;
    
    /**
     * @var string Request URI
     */
    private $uri;
    
    /**
     * @var string Request method
     */
    private $method;
    
    /**
     * @var array URI path parts
     */
    private $pathParts = [];
    
    /**
     * Create a new request instance
     */
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
        
        // Get request URI
        $this->uri = $this->getUri();
        
        // Get request method
        $this->method = $this->getMethod();
        
        // Get request content
        $this->content = file_get_contents('php://input');
        
        // Parse URI path parts
        $path = parse_url($this->uri, PHP_URL_PATH);
        $path = trim($path, '/');
        $this->pathParts = $path ? explode('/', $path) : [];
    }
    
    /**
     * Get the request URI
     */
    public function getUri()
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }
    
    /**
     * Get the path part of the URI
     */
    public function getPath()
    {
        return parse_url($this->getUri(), PHP_URL_PATH);
    }
    
    /**
     * Get request method
     */
    public function getMethod()
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }
    
    /**
     * Check if request is an AJAX request
     */
    public function isAjax()
    {
        return isset($this->headers['X-Requested-With']) && 
               strtolower($this->headers['X-Requested-With']) === 'xmlhttprequest';
    }
    
    /**
     * Get a GET parameter
     */
    public function get($key = null, $default = null)
    {
        if ($key === null) {
            return $this->get;
        }
        
        return $this->get[$key] ?? $default;
    }
    
    /**
     * Get a POST parameter
     */
    public function post($key = null, $default = null)
    {
        if ($key === null) {
            return $this->post;
        }
        
        return $this->post[$key] ?? $default;
    }
    
    /**
     * Get a parameter (GET or POST)
     */
    public function input($key, $default = null)
    {
        return $this->post($key) ?? $this->get($key) ?? $default;
    }
    
    /**
     * Get all input parameters (GET and POST combined)
     */
    public function all()
    {
        return array_merge($this->get, $this->post);
    }
    
    /**
     * Get a header
     */
    public function header($key, $default = null)
    {
        $key = str_replace(' ', '-', ucwords(str_replace('-', ' ', strtolower($key))));
        return $this->headers[$key] ?? $default;
    }
    
    /**
     * Get all headers
     */
    public function headers()
    {
        return $this->headers;
    }
    
    /**
     * Get request body content
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * Get JSON content as array
     */
    public function json($assoc = true)
    {
        if (strpos($this->header('Content-Type', ''), 'application/json') !== false) {
            return json_decode($this->getContent(), $assoc);
        }
        
        return null;
    }
}