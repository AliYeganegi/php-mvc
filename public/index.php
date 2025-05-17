<?php

use App\Core\Application;

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/src/Core/Bootstrap.php';


$app = new  Application();
$app->run();