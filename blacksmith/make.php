<?php

require_once __DIR__ . "/../vendor/Autoloader.php";
Autoloader::register();

use Core\Blacksmith;

$blacksmith = new Blacksmith();

if ($argc < 3) {
  $blacksmith->show_usage('make');
  die;
}
$file_type = $argv[1];
$file_name = $argv[2];
$actions = array_slice($argv, 3);

$blacksmith->make($file_type, $file_name, $actions);
