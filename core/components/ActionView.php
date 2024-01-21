<?php

namespace Core\Components;

use Core\App;

class ActionView
{
  private const VIEWS_DIR = 'app\\views\\';
  private const VIEW_FILE_EXT = '.view.php';
  private const LAYOUTS_DIR = 'layouts\\';

  private const DEFAULT_ACTION = 'not_found';
  private const DEFAULT_CONTROLLER_NAME = 'App\\Controllers\\ApplicationController';

  protected string $controller_views_directory = 'application\\';
  protected string $controller_action = self::DEFAULT_ACTION;

  public string $page_title = '';


  public function __construct(string $controller_name = self::DEFAULT_CONTROLLER_NAME, $action = self::DEFAULT_ACTION)
  {
    $this->controller_views_directory = $this->construct_controller_views_directory($controller_name);
    $this->controller_action = $action;
  }


  public function VIEWS_DIR()
  {
    return App::$ROOT_DIR . self::VIEWS_DIR;
  }

  public function construct_controller_views_directory(string $controller_name = self::DEFAULT_CONTROLLER_NAME)
  {
    $controller_name = str_replace('App\\', '', $controller_name);
    $controller_name = str_replace('Controllers\\', '', $controller_name);
    $controller_name = str_replace('Controller', '', $controller_name);
    $controller_name = strtolower($controller_name) . '\\';

    return $controller_name;
  }

  protected function get_view_file(string $action = self::DEFAULT_ACTION)
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
      echo 'layout does not exist';
      return;
    }
  }


  public function prepare(array $page_info = [])
  {
    $this->page_title = isset($page_info['page_title']) && is_string($page_info['page_title']) ? $page_info['page_title'] : '';
  }


  public function view()
  {
    $view_file = $this->get_view_file($this->controller_action);
    $layout_file = $this->get_layout_file();

    if (file_exists($view_file)) {
      include $layout_file;
    } else {
      // handle errors: view file 404
      echo 'view file does not exist';
      return;
    }
  }
}
