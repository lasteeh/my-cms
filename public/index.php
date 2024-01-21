<?php

require_once "../core/Autoloader.php";
Autoloader::register();

use Core\App;
use Core\Components\Request;

$app = new App();
$request = new Request($_SERVER['REQUEST_URI'], App::$ROOT_URL, App::$ROUTES);

$app->run($request);
