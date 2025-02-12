<?php

namespace Core\Components;

use Core\Base;
use Core\Traits\FlashHandling;

class ActionView extends Base
{
  use FlashHandling;

  private const VIEW_FILE_EXT = '.view.php';
  private const LAYOUTS_DIR = 'layouts\\';

  private array $OBJECTS = [];

  private string $controller_views_directory;
  private string $controller_action;
  private array $page_info = [];
  private string $page_layout = '';
  private string $inline_style = '';
  private string $inline_script = '';


  public function __construct(string $controller_name, string $action)
  {
    $this->controller_views_directory = $this->construct_controller_views_directory($controller_name);
    $this->controller_action = $action;
  }


  private function VIEWS_DIR()
  {
    return self::$ROOT_DIR . self::APP_DIR . '\\' . self::VIEWS_DIR . '\\';
  }

  private function construct_controller_views_directory(string $controller_name)
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

    $view_file_directory = $this->VIEWS_DIR() . $controller_name;

    if (!is_dir($view_file_directory)) {
      $this->ERRORS[] = "Views directory not found: {$view_file_directory}";
      $this->handle_errors();
    }

    return $controller_name;
  }

  private function get_view_file(string $action)
  {
    return $this->VIEWS_DIR() . $this->controller_views_directory . $action . self::VIEW_FILE_EXT;
  }

  private function get_layout_file(string $layout, string $directory = self::LAYOUTS_DIR)
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


  public function prepare(array $config)
  {
    $page_info = $config['page_info'];
    $page_layout = $config['page_layout'];
    $inline_css = $config['inline_css'];
    $inline_js = $config['inline_js'];
    $objects = $config['objects'];
    $page_errors = $config['errors'];

    $this->page_info = $page_info;
    $this->page_layout = $page_layout;
    $this->inline_style = $inline_css;
    $this->inline_script = $inline_js;
    $this->OBJECTS = $objects;
    $this->ERRORS = $page_errors;
  }

  public function get_object(string $name)
  {
    if (isset($this->OBJECTS[$name])) {
      return $this->OBJECTS[$name];
    } else {
      $this->add_error("Object not found: {$name}");
      $this->handle_errors();
    }
  }


  public function view()
  {
    $view_file = $this->get_view_file($this->controller_action);
    $layout_file = $this->get_layout_file($this->page_layout);

    if (file_exists($view_file)) {
      include $layout_file;
      $this->clear_flash();
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
  public function get_url(string $path)
  {
    return static::$ROOT_URL . $path;
  }

  public function stylesheet(string $file_name)
  {
    echo static::$ROOT_URL . "/public/assets/css/" . $file_name . ".css";
  }

  public function script(string $file_name)
  {
    echo static::$ROOT_URL . "/public/assets/js/" . $file_name . ".js";
  }

  public function inline_style()
  {
    $style = $this->inline_style;

    if (!empty($this->inline_style)) {
      echo "<style>$style</style>";
    }
  }
  public function inline_script()
  {
    $script = $this->inline_script;

    if (!empty($this->inline_script)) {
      echo "<script>$script</script>";
    }
  }
  public function page_info(string $key)
  {
    $info = $this->page_info[$key] ?? '';

    if (!empty($info) && is_string($info)) {
      echo $info;
    }
  }
  public function get_page_info(string $key)
  {
    $info = $this->page_info[$key] ?? '';

    if (!empty($info) && is_string($info)) {
      return $info;
    }
  }
}
