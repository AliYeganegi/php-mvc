<?php

use App\Core\Application;

// root path
define('BASE_PATH', dirname(__DIR__));

// load autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// load bootstrap
require_once BASE_PATH . '/src/Core/Bootstrap.php';

// start app
$app = new  Application();
$app->run();