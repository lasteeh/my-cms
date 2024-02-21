<?php
require_once __DIR__ . "/../vendor/Autoloader.php";
Autoloader::register();

use Core\Blacksmith;

$blacksmith = new Blacksmith;

if ($argc < 2) {
  $blacksmith->show_usage('register');
  die;
}

$model_type = $argv[1];
$parameters = array_slice($argv, 2);

$blacksmith->register($model_type, $parameters);
