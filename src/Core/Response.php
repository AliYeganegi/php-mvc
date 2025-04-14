<?php
// src/Core/Response.php
namespace App\Core;

class Response
{
    private $statusCode = 200;
    
    private $headers = [];
    
    private $content = '';

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

    public function __construct($content = '', $statusCode = 200, $headers = [])
    {
        $this->setContent($content);
        $this->setStatusCode($statusCode);
        
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
    }
    
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }
    
    public function getContent()
    {
        return $this->content;
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }
    
    public static function json($data, $statusCode = 200, $headers = [])
    {
        $content = json_encode($data);
        
        $response = new self($content, $statusCode, $headers);
        $response->setHeader('Content-Type', 'application/json');
        
        return $response;
    }

    public static function redirect($url, $statusCode = 302)
    {
        $response = new self('', $statusCode);
        $response->setHeader('Location', $url);
        
        return $response;
    }
    
    public function send()
    {
        http_response_code($this->statusCode);
        
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        
        echo $this->content;
        
        return $this;
    }
}