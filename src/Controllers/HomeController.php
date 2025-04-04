<?php
// src/Controllers/HomeController.php
namespace App\Controllers;


use App\Core\View;
use App\Core\Response;
use App\Models\UserModel;

class HomeController
{
    public function index(): Response
    {
        $view = new View('home.index', [
            'title' => 'Welcome to my MVC framework',
            'message' => 'Hello world!'
        ]);
        
        // Return the rendered view with a 200 status code
        return new Response($view->render(), 200);
    }
}