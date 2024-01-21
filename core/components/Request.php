<?php

namespace Core\Components;

class Request
{
  public $URI = '/';
  public $PARAMETER = null;
  public $METHOD = 'GET';
  public $CONTROLLER = [
    'name' => 'application',
    'action' => 'index',
  ];
  public $ERRORS = [];

  public function __construct($root_url = null, $configured_routes = [])
  {
    // validate root_url as a valid url
    if ($root_url === null || !is_string($root_url) || !filter_var($root_url, FILTER_VALIDATE_URL)) {
      $root_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
    }

    // create custom server request uri
    $url_components = parse_url($root_url);
    $request_path = $url_components['path'] ?? '';

    if (strpos($_SERVER['REQUEST_URI'], $request_path) === 0) {
      $this->URI = substr($_SERVER['REQUEST_URI'], strlen($request_path));
    }

    // process Request
    $this->initialize($configured_routes);
  }

  protected function initialize($configured_routes = [])
  {
    foreach ($configured_routes as $route => $methods) {
      foreach ($methods as $method => $controller_name_action_pair) {
        // create a regular expression pattern to match the url.
        $route_pattern = str_replace('/', '\/', $route);
        $route_pattern = '#^' . preg_replace('/:([^\s\/]+)/', '([^\/]+)', $route_pattern) . '$#';

        // check if the current request URI matches the route pattern
        // and if the HTTP method matches the specified method.
        if ($_SERVER['REQUEST_METHOD'] === $method && preg_match($route_pattern, $this->URI, $matches)) {
          // set Request parameter
          $this->PARAMETER = $matches[1] ?? null;
          // set Request method
          $this->METHOD = $method;
          // split the controller and action from the route information.
          list($controller_name, $controller_action) = explode('@', $controller_name_action_pair);
          // set Request controller
          $this->CONTROLLER['name'] = $controller_name;
          $this->CONTROLLER['action'] = $controller_action;
        }
      }
    }
  }
}
