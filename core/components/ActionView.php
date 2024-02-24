<?php

namespace Core\Components;

use Core\Base;
use Core\App;

class ActionView extends Base
{
  private const VIEW_FILE_EXT = '.view.php';
  private const LAYOUTS_DIR = 'layouts\\';

  private array $OBJECTS = [];

  protected string $controller_views_directory;
  protected string $controller_action;

  public string $page_title = '';


  public function __construct(string $controller_name, string $action)
  {
    $this->controller_views_directory = $this->construct_controller_views_directory($controller_name);
    $this->controller_action = $action;
  }


  protected function VIEWS_DIR()
  {
    return self::$ROOT_DIR . self::APP_DIR . '\\' . self::VIEWS_DIR . '\\';
  }

  public function construct_controller_views_directory(string $controller_name)
  {
    // old name creation
    // $controller_name = str_replace(ucfirst(self::APP_DIR) . '\\', '', $controller_name);
    // $controller_name = str_replace(ucfirst(self::CONTROLLERS_DIR) . '\\', '', $controller_name);
    // $controller_name = str_replace('Controller', '', $controller_name);
    // $controller_name = strtolower($controller_name) . '\\';

    // new name creation
    $controller_name = preg_replace('/^.*\\\\/', '', $controller_name); // remove namespace
    $controller_name = str_replace('Controller', '', $controller_name); // remove 'Controller'
    $controller_name = preg_replace('/(?<!^)([A-Z])/', '_$1', $controller_name); // camelcase to underscore
    $controller_name = strtolower($controller_name) . "\\"; // convert to lowercase

    return $controller_name;
  }

  protected function get_view_file(string $action)
  {
    return $this->VIEWS_DIR() . $this->controller_views_directory . $action . self::VIEW_FILE_EXT;
  }

  protected function get_layout_file(string $layout = 'application', string $directory = self::LAYOUTS_DIR)
  {
    $layout_directory = $directory ?? self::LAYOUTS_DIR;

    $layout_file = $this->VIEWS_DIR() . $layout_directory . $layout . self::VIEW_FILE_EXT;

    if (file_exists($layout_file)) {
      return $layout_file;
    } else {
      // handle errors: layout file 404
      $this->ERRORS[] = "Layout file not found: {$layout_file}";
      $this->handle_errors();
    }
  }


  public function prepare(array $page_info = [], array $page_errors = [], array $objects = [])
  {
    $this->page_title = isset($page_info['page_title']) && is_string($page_info['page_title']) ? $page_info['page_title'] : '';

    $this->ERRORS = $page_errors;

    $this->OBJECTS = $objects;
  }

  public function get_object(string $name)
  {
    return $this->OBJECTS[$name];
  }


  public function view()
  {
    $view_file = $this->get_view_file($this->controller_action);
    $layout_file = $this->get_layout_file();

    if (file_exists($view_file)) {
      include $layout_file;
      exit;
    } else {
      // handle errors: view file 404
      $this->ERRORS[] = "View file not found: {$view_file}";
      $this->handle_errors();
      return;
    }
  }

  public function url(string $path)
  {
    echo static::$ROOT_URL . $path;
  }
}
