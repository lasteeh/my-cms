<?php

require_once __DIR__ . "/../vendor/Autoloader.php";
Autoloader::register();

use Core\Blacksmith;

$blacksmith = new Blacksmith();
$blacksmith->migrate();
