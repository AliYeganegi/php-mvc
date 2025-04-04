<?php

//application's root path
define('BASE_PATH', dirname(__DIR__));

// load 
require_once BASE_PATH . '/src/Core/Bootstrap.php';

// starting point
$app = new \App\Core\Application();
$app->run();