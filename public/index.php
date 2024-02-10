<?php

require_once "../vendor/Autoloader.php";
Autoloader::register();

use Core\App;
use Core\Request;

$app = new App();
$app->run();

$request = new Request($_SERVER['REQUEST_URI'], App::$ROUTES);
$app->execute($request);
