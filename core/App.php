<?php

namespace Core;

use Core\Traits\ErrorHandler;
use Core\Components\CMSException;

class App
{
  use ErrorHandler;

  /** DO NOT EDIT *************************************************************************************/
  // directory name of where the App Class is in relative to the root directory of the project
  private const APP_DIR = 'core';

  // directory name of the application controllers
  private const APP_CONTROLLER_DIR = 'app/controllers';

  // file path of the Routes configuration relative to the root directory of the project
  private const ROUTES_FILE_PATH = 'config/routes.php';

  // file path of the index.php file that serves as the funnel
  private const INDEX_FILE_PATH = 'public/index.php';

  // NOTE: directory/path names must NOT have slashes in front
  /****************************************************************************************************/

  public static $ROOT_DIR;
  public static $ROOT_URL;
  public static $ROUTES = [];
  public array $ERRORS = [];

  public function __construct()
  {
    set_exception_handler([$this, 'exception_handler']);

    self::$ROOT_DIR = str_replace(self::APP_DIR, '', __DIR__);
    self::$ROOT_URL = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . str_replace('/' . self::INDEX_FILE_PATH, '', $_SERVER['SCRIPT_NAME']);

    $this->load_routes();
  }

  public function run()
  {
    session_start();
    $this->connect_to_database();
  }

  public function execute(Request $request)
  {
    $controller = $this->fetch_controller($request);
    $action = $this->fetch_action($request, $controller);

    $this->run_controller_action($controller, $action);
  }

  protected function load_routes()
  {
    $routes_config_file = self::$ROOT_DIR . self::ROUTES_FILE_PATH;
    if (file_exists($routes_config_file)) {
      self::$ROUTES = require_once $routes_config_file;
    } else {
      $this->ERRORS[] = "File not found: {$routes_config_file}";
      throw new CMSException();
    }
  }

  protected function connect_to_database()
  {
    $database = new Database;
    $connection = $database->test_connection();

    if (!$connection) {
      $this->ERRORS[] = $database->ERRORS;
      throw new CMSException();
    }
  }


  protected function fetch_controller(Request $request)
  {
    // set the namespace for the controller and get controller name
    $controller_namespace = implode('\\', array_map('ucfirst', explode('/', self::APP_CONTROLLER_DIR)));
    $controller_name = $controller_namespace . '\\' . ucfirst($request->CONTROLLER['name']) . 'Controller';

    if (class_exists($controller_name)) {
      return new $controller_name;
    } else {
      $this->ERRORS[] = "Controller not found: \"{$controller_name}\"";
      throw new CMSException();
    }
  }

  protected function fetch_action(Request $request, ?object $controller = null)
  {
    if (!$controller || !is_object($controller) || $controller === null) {
      $controller = $this->fetch_controller($request);
    }
    $action = $request->CONTROLLER['action'];

    if ($action && is_string($action) && $action !== '' && method_exists($controller, $action)) {
      return $action;
    } else {
      $this->ERRORS[] = "Action not found: \"{$action}\"";
      throw new CMSException();
    }
  }

  protected function run_controller_action(?object $controller, string $action)
  {
    if (!is_object($controller) || $controller === null || !$controller) {
      $this->ERRORS[] = "Controller not found: \"{$controller}\"";
      throw new CMSException();
    }

    if (method_exists($controller, $action)) {
      $controller->$action();
    } else {
      $this->ERRORS[] = "Action not found: \"{$action}\"";
      throw new CMSException();
    }
  }
}
