<?php
// src/Core/View.php
namespace App\Core;

class View
{
    /**
     * Path to the views directory
     * @var string
     */
    protected static $viewsPath = '';
    
    /**
     * Layout file to use
     * @var string|null
     */
    protected static $layout = null;
    
    /**
     * Variables to be passed to the view
     * @var array
     */
    protected $vars = [];
    
    /**
     * Constructor
     * @param string $view The view name
     * @param array $data Variables to pass to the view
     */
    public function __construct(protected string $view, array $data = [])
    {
        $this->vars = $data;
    }
    
    /**
     * Set the views directory path
     * @param string $path
     */
    public static function setViewsPath(string $path): void
    {
        self::$viewsPath = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
    }
    
    /**
     * Set the layout to use
     * @param string|null $layout
     */
    public static function setLayout(?string $layout): void
    {
        self::$layout = $layout;
    }
    
    /**
     * Render the view
     * @return string
     * @throws \Exception If view file doesn't exist
     */
    public function render(): string
    {
        $viewFile = self::$viewsPath . str_replace('.', DIRECTORY_SEPARATOR, $this->view) . '.php';
        
        if (!file_exists($viewFile)) {
            throw new \Exception("View file '{$viewFile}' not found");
        }
        
        // Extract variables to make them accessible in the view
        extract($this->vars);
        
        // Start output buffering
        ob_start();
        include $viewFile;
        $content = ob_get_clean();
        
        // If layout is set, render the layout with the content
        if (self::$layout !== null) {
            $layoutFile = self::$viewsPath . 'layouts' . DIRECTORY_SEPARATOR . self::$layout . '.php';
            
            if (!file_exists($layoutFile)) {
                throw new \Exception("Layout file '{$layoutFile}' not found");
            }
            
            // Start output buffering for layout
            ob_start();
            include $layoutFile;
            return ob_get_clean();
        }
        
        return $content;
    }
    
    /**
     * Include a partial view
     * @param string $partial
     * @param array $data
     * @return string
     */
    public static function partial(string $partial, array $data = []): string
    {
        $partialFile = self::$viewsPath . 'partials' . DIRECTORY_SEPARATOR . $partial . '.php';
        
        if (!file_exists($partialFile)) {
            throw new \Exception("Partial file '{$partialFile}' not found");
        }
        
        // Extract variables for the partial
        extract($data);
        
        // Start output buffering
        ob_start();
        include $partialFile;
        return ob_get_clean();
    }
    
    /**
     * Magic toString method to render the view when the object is used as a string
     * @return string
     */
    public function __toString(): string
    {
        try {
            return $this->render();
        } catch (\Exception $e) {
            // In case of an error, return the error message
            return "Error rendering view: " . $e->getMessage();
        }
    }
}