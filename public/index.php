<?php

require_once "../vendor/Autoloader.php";
Autoloader::register();

use Core\App;
use Core\Request;

$app = new App();
$request = new Request($_SERVER['REQUEST_URI'], App::$ROOT_URL, App::$ROUTES);

$app->run();
$app->execute($request);
