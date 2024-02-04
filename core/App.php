<?php

namespace Core;

use Core\Request;
use Core\ErrorHandler;

class App
{
  use ErrorHandler;

  /** DO NOT EDIT *************************************************************************************/
  // directory name of where the App Class is in relative to the root directory of the project
  protected const APP_DIR = 'core';

  // directory name of the application controllers
  protected const APP_CONTROLLER_DIR = 'app/controllers';

  // file path of the Routes configuration relative to the root directory of the project
  protected const ROUTES_FILE_PATH = 'config/routes.php';

  // file path of the index.php file that serves as the funnel
  protected const INDEX_FILE_PATH = 'public/index.php';

  // NOTE: directory/path names must NOT have slashed in front
  /****************************************************************************************************/

  public static $ROOT_DIR;
  public static $ROOT_URL;
  public static $ROUTES = [];
  public array $ERRORS = [];

  public function __construct()
  {
    self::$ROOT_DIR = str_replace(self::APP_DIR, '', __DIR__);
    self::$ROOT_URL = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . str_replace('/' . self::INDEX_FILE_PATH, '', $_SERVER['SCRIPT_NAME']);


    $routes_config_file = self::$ROOT_DIR . self::ROUTES_FILE_PATH;
    if (file_exists($routes_config_file)) {
      self::$ROUTES = require_once $routes_config_file;
    } else {
      $this->$ERRORS[] = "File not found: {$routes_config_file}";

      // set fallback routes
      self::$ROUTES = [];
    }

    set_exception_handler([$this, 'exception_handler']);
  }

  public function run()
  {
    session_start();
  }

  public function execute(Request $request)
  {
    $controller = $this->fetch_controller($request);
    $action = $request->CONTROLLER['action'];

    $this->run_controller_action($controller, $action);
    $this->run_error_check();
  }


  protected function fetch_controller(Request $request)
  {
    // set the namespace for the controller
    $controller_namespace = implode('\\', array_map('ucfirst', explode('/', self::APP_CONTROLLER_DIR)));

    $controller_name = $controller_namespace . '\\' . ucfirst($request->CONTROLLER['name']) . 'Controller';

    $controller_name = '123123';

    if (class_exists($controller_name)) {
      return new $controller_name;
    } else {
      $this->ERRORS[] = "Controller not found: \"{$controller_name}\"";
      throw new \Exception(__FILE__ . ':' . __LINE__);
    }
  }

  protected function run_controller_action(?object $controller, string $action)
  {
    if (!is_object($controller) || $controller === null || !$controller) {
      $this->ERRORS[] = "Controller not found: \"{$controller}\"";
      throw new \Exception(__FILE__ . ':' . __LINE__);
      return;
    }

    if (method_exists($controller, $action)) {
      $controller->$action();
    } else {
      $this->ERRORS[] = "Action not found: \"{$action}\"";
      throw new \Exception(__FILE__ . ':' . __LINE__);
    }
  }
}
