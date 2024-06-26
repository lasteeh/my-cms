<?php

namespace Core;

use Core\Base;

class App extends Base
{
  public static $ROUTES = [];

  public function __construct()
  {
    parent::__construct();
  }

  public function run()
  {
    session_start();

    $this->load_routes();
    $this->connect_to_database();
  }

  public function execute(Request $request)
  {
    $controller = $this->fetch_controller($request);
    $action = $this->fetch_action($request, $controller);
    $route_parameters = $request->ROUTE_PARAMETERS;
    $request_uri = $request->URI;

    $this->run_controller_action($controller, $action, $route_parameters, $request_uri);
  }

  protected function load_routes()
  {
    $routes_config_file = self::$ROOT_DIR . self::CONFIG_DIR . '/routes.php';

    if (file_exists($routes_config_file)) {
      self::$ROUTES = require_once $routes_config_file;
    } else {
      $this->ERRORS[] = "File not found: {$routes_config_file}";
      $this->handle_errors();
    }
  }

  protected function connect_to_database()
  {
    $database = new Database;
    $connection = $database->test_connection();

    if (!$connection) {
      $this->ERRORS[] = $database->ERRORS;
      $this->handle_errors();
    }
  }


  protected function fetch_controller(Request $request)
  {
    // set the namespace for the controller and get controller name
    $controller_namespace = implode('\\', array_map('ucfirst', explode('/', self::APP_DIR . '/' . self::CONTROLLERS_DIR)));
    $controller_name = $controller_namespace . '\\' . ucfirst($request->CONTROLLER['name']) . 'Controller';

    if (class_exists($controller_name)) {
      return new $controller_name;
    } else {
      $this->ERRORS[] = "Controller not found: \"{$controller_name}\"";
      $this->handle_errors();
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
      $this->handle_errors();
    }
  }

  protected function run_controller_action(?object $controller, ?string $action, ?array $route_parameters, ?string $request_uri)
  {
    if (!is_object($controller) || $controller === null || !$controller) {
      $this->ERRORS[] = "Controller not found: \"{$controller}\"";
      $this->handle_errors();
    }

    if (method_exists($controller, $action)) {
      $controller->set_request_uri($request_uri);
      $controller->set_route_params($route_parameters);
      $controller->execute($action);
    } else {
      $this->ERRORS[] = "Action not found: \"{$action}\"";
      $this->handle_errors();
    }
  }
}
