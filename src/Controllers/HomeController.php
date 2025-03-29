<?php
// src/Controllers/HomeController.php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

class HomeController
{
    public function index(Request $request)
    {
        return '<h1>Welcome to Your MVC Framework</h1><p>This is the homepage.</p>';
    }
}