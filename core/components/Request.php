<?php

namespace Core\Components;

class Request
{
  protected $REQUEST_URI = '/';
  protected $ROUTES = [];

  public function __construct($root_url = null, $routes = [])
  {
    // validate root_url as a valid url
    if ($root_url === null || !is_string($root_url) || !filter_var($root_url, FILTER_VALIDATE_URL)) {
      $root_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
    }

    // create custom server request uri
    $url_components = parse_url($root_url);
    $request_path = $url_components['path'] ?? '';

    if (strpos($_SERVER['REQUEST_URI'], $request_path) === 0) {
      $this->REQUEST_URI = substr($_SERVER['REQUEST_URI'], strlen($request_path));
    }

    // configure routes for matching
    $this->ROUTES = $routes;

    // 
  }
}
