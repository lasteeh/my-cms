<?php

namespace Core;

use Core\Base;

class Request extends Base
{
  public ?string $URI = null;
  public string $METHOD = 'GET'; // set 'GET' as fallback request method

  public $PARAMETERS = [];
  public $CONTROLLER = [
    'name' => 'application', // set 'application' fallback controller name
    'action' => 'not_found', // set 'not_found' fallback controller action
  ];

  private ?string $SERVER_REQUEST_URI = null;
  private array $CONFIGURED_ROUTES = [];

  public function __construct(string $server_request_uri = null, array $configured_routes = [])
  {
    parent::__construct();

    $this->SERVER_REQUEST_URI = $server_request_uri;
    $this->CONFIGURED_ROUTES = $configured_routes;

    // create custom request uri
    $this->create_URI(self::$ROOT_URL);
    // configure the Request with request method, parameter, and controller info based on configured routes
    $this->configure_request($this->CONFIGURED_ROUTES);
  }

  protected function create_URI(string $root_url)
  {
    // validate root_url as a valid url
    if ($root_url === null || !is_string($root_url) || !filter_var($root_url, FILTER_VALIDATE_URL)) {
      $this->ERRORS[] = "Invalid 'root_url' provided: $root_url";

      // set fallback url
      $root_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
    }

    // create custom server request uri
    $url_components = parse_url($root_url);
    $request_path = $url_components['path'] ?? '';

    if (strpos($_SERVER['REQUEST_URI'], $request_path) === 0) {
      $this->URI = substr($_SERVER['REQUEST_URI'], strlen($request_path));
    } else {
      $this->URI = $request_path;
    }
  }

  protected function configure_request(array $configured_routes = [])
  {
    $match_found = false;

    foreach ($configured_routes as $route => $methods) {
      foreach ($methods as $method => $controller_name_action_pair) {
        // create a regular expression pattern to match the url.
        $route_pattern = str_replace('/', '\/', $route);
        $route_pattern = '#^' . preg_replace('/:([^\s\/]+)/', '([^\/]+)', $route_pattern) . '$#';

        // check if the current request URI matches the route pattern
        // and if the HTTP method matches the specified method.
        if (preg_match($route_pattern, $this->URI, $matches) && $_SERVER['REQUEST_METHOD'] === $method) {
          // Extract parameter values and set Request parameters
          preg_match_all('/:([^\s\/]+)/', $route, $parameter_keys);
          foreach ($parameter_keys[1] as $index => $key) {
            $this->PARAMETERS[$key] = $matches[$index + 1];
          }
          // set Request method
          $this->METHOD = $method;
          // split the controller and action from the route information.
          list($controller_name, $controller_action) = explode('@', $controller_name_action_pair);
          // set Request controller
          $this->CONTROLLER['name'] = $controller_name;
          $this->CONTROLLER['action'] = $controller_action;

          $match_found = true;
          break;
        }
      }
    }

    // if no match found, add error to Request
    if (!$match_found) {
      $this->ERRORS[] = "No matching route for {$_SERVER['REQUEST_METHOD']} {$this->URI}";
    }
  }
}
