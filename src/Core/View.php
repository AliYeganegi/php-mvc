<?php

namespace App\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class View
{
    protected static $viewsPath = '';
    protected static ?Environment $twig = null;
    protected static $layout = null;

    protected $vars = [];

    public function __construct(protected string $view, array $data = [])
    {
        $this->vars = $data;
    }

    public static function setViewsPath(string $path): void
    {
        self::$viewsPath = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;

        // Set up Twig
        $loader = new FilesystemLoader(self::$viewsPath);
        self::$twig = new Environment($loader, [
            'cache' => false, // Set to true and give a cache directory for production
            'debug' => true,
        ]);
    }

    public static function setLayout(?string $layout): void
    {
        self::$layout = $layout;
    }

    public function render(): string
    {
        // Determine if it's a Twig view or PHP view
        $twigViewFile = str_replace('.', DIRECTORY_SEPARATOR, $this->view) . '.twig';
        $phpViewFile = self::$viewsPath . str_replace('.', DIRECTORY_SEPARATOR, $this->view) . '.php';

        if (file_exists(self::$viewsPath . $twigViewFile)) {
            return self::$twig->render($twigViewFile, $this->vars);
        }

        if (!file_exists($phpViewFile)) {
            throw new \Exception("View file '{$phpViewFile}' not found");
        }

        extract($this->vars);
        ob_start();
        include $phpViewFile;
        $content = ob_get_clean();

        if (self::$layout !== null) {
            $layoutFile = self::$viewsPath . 'layouts' . DIRECTORY_SEPARATOR . self::$layout . '.php';
            if (!file_exists($layoutFile)) {
                throw new \Exception("Layout file '{$layoutFile}' not found");
            }

            ob_start();
            include $layoutFile;
            return ob_get_clean();
        }

        return $content;
    }

    public static function partial(string $partial, array $data = []): string
    {
        $partialFile = self::$viewsPath . 'partials' . DIRECTORY_SEPARATOR . $partial . '.php';

        if (!file_exists($partialFile)) {
            throw new \Exception("Partial file '{$partialFile}' not found");
        }

        extract($data);
        ob_start();
        include $partialFile;
        return ob_get_clean();
    }

    public function __toString(): string
    {
        try {
            return $this->render();
        } catch (\Exception $e) {
            return "Error rendering view: " . $e->getMessage();
        }
    }
}
