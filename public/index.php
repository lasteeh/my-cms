<?php

require_once "../core/Autoloader.php";
Autoloader::register();


use Core\App;

$app = new App();

$app->run();
