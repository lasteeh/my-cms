<?php

namespace Core;

use Core\Components\Request;

class App
{
  protected static $ROOT_DIR;
  protected static $ROOT_URL;
  protected static $ROUTES = [];

  public function __construct()
  {
    self::$ROOT_DIR = str_replace('core', '', __DIR__);
    self::$ROOT_URL = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . str_replace('/public/index.php', '', $_SERVER['SCRIPT_NAME']);
    self::$ROUTES = file_exists(self::$ROOT_DIR . "config/routes.php") ? require_once self::$ROOT_DIR . "config/routes.php" : [];
  }

  public function run()
  {
    $request = new Request(self::$ROOT_URL, self::$ROUTES);
  }
}
