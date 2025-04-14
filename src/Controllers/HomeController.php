<?php
// src/Controllers/HomeController.php
namespace App\Controllers;

use App\Core\Database\QueryBuilder;
use App\Core\View;
use App\Core\Response;
use App\Models\UserModel;

class HomeController
{
    public function index(): Response
    {
        $users = QueryBuilder::table('users')
            ->select(['name'])
            ->get();

            var_dump($users);

        $view = new View('home.index', [
            'title' => 'salam',
            'message' => 'Hello world!'
        ]);

        return new Response($view->render(), 200);
    }
}
