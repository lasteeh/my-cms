<?php

namespace Core;

use Core\Components\Request;

class App
{
  // names the directory the files are in
  protected const APP_DIR = 'core';
  protected const ROUTES_FILE_PATH = 'config/routes.php';
  // requires the forward slash infront for URL normalization that goes with the routes!
  protected const INDEX_FILE_PATH = '/public/index.php';

  public static $ROOT_DIR;
  public static $ROOT_URL;
  public static $ROUTES = [];

  public function __construct()
  {
    self::$ROOT_DIR = str_replace(self::APP_DIR, '', __DIR__);
    self::$ROOT_URL = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . str_replace(self::INDEX_FILE_PATH, '', $_SERVER['SCRIPT_NAME']);
    self::$ROUTES = file_exists(self::$ROOT_DIR . self::ROUTES_FILE_PATH) ? require_once self::$ROOT_DIR . self::ROUTES_FILE_PATH : [];
  }

  public function run(Request $request)
  {

    // fetch controller
    list($controller, $errors) = $this->fetch_controller($request);

    if ($errors) {
      // handle errors: controller 404 
      return;
    }

    // handle controller action
    $action = $request->CONTROLLER['action'];

    if (method_exists($controller, $action)) {
      $controller->execute($action);
      return;
    } else {
      // handle errors: controller action 404 
      $controller->execute('not_found');
      return;
    }
  }

  public function fetch_controller(Request $request)
  {
    $controller_name = 'App\\Controllers\\' . ucfirst($request->CONTROLLER['name']) . 'Controller';

    if (class_exists($controller_name)) {
      return [new $controller_name, null];
    }
    return [null, ['Controller not found.']];
  }
}
