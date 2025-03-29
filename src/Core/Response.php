<?php
// src/Core/Response.php
namespace App\Core;

class Response
{
    /**
     * @var int HTTP status code
     */
    private $statusCode = 200;
    
    /**
     * @var array HTTP headers
     */
    private $headers = [];
    
    /**
     * @var string Response content
     */
    private $content = '';
    
    /**
     * HTTP status codes
     */
    private static $statusTexts = [
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        204 => 'No Content',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        409 => 'Conflict',
        422 => 'Unprocessable Entity',
        500 => 'Internal Server Error',
    ];
    
    /**
     * Create a new response instance
     */
    public function __construct($content = '', $statusCode = 200, $headers = [])
    {
        $this->setContent($content);
        $this->setStatusCode($statusCode);
        
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
    }
    
    /**
     * Set response content
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }
    
    /**
     * Get response content
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * Set HTTP status code
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }
    
    /**
     * Get HTTP status code
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
    
    /**
     * Set a response header
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }
    
    /**
     * Get all response headers
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    
    /**
     * Create a JSON response
     */
    public static function json($data, $statusCode = 200, $headers = [])
    {
        $content = json_encode($data);
        
        $response = new self($content, $statusCode, $headers);
        $response->setHeader('Content-Type', 'application/json');
        
        return $response;
    }
    
    /**
     * Create a redirect response
     */
    public static function redirect($url, $statusCode = 302)
    {
        $response = new self('', $statusCode);
        $response->setHeader('Location', $url);
        
        return $response;
    }
    
    /**
     * Send the response
     */
    public function send()
    {
        // Send status code
        http_response_code($this->statusCode);
        
        // Send headers
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        
        // Send content
        echo $this->content;
        
        return $this;
    }
}