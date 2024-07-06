<?php

namespace Core\Components;

use Core\Base;
use Core\Components\ActionView;
use App\Controllers\ApplicationController;
use Core\Traits\FlashHandling;

class ActionController extends Base
{
  use FlashHandling;

  protected string $REQUEST_URI = '';
  protected array $ROUTE_PARAMS = [];
  protected array $PAGE_INFO = [];
  protected string $PAGE_LAYOUT = 'application';

  protected static $skip_before_action = [];
  protected static $before_action = [];
  protected static $skip_after_action = [];
  protected static $after_action = [];

  private array $OBJECTS = [];
  private string $INLINE_CSS = '';
  private string $INLINE_JS = '';

  public function __construct()
  {

    // setup filters
    $this->setup_filter('before_action');
    $this->setup_filter('skip_before_action');
    $this->setup_filter('after_action');
    $this->setup_filter('skip_after_action');
  }

  private function setup_filter(string $filter_name)
  {
    $all_filters = array_merge(
      self::$$filter_name,
      ApplicationController::$$filter_name,
      static::$$filter_name
    );

    static::$$filter_name = $this->normalize_filter_array($all_filters);
  }

  protected function filter_should_apply(string $action, array $options = [])
  {
    return empty($options) ||
      (isset($options['only']) && in_array($action, $options['only'])) ||
      (isset($options['except']) && !in_array($action, $options['except']));
  }

  protected function filter_should_skip($skip_filters, $filter, string $action)
  {
    if (isset($skip_filters[$filter])) {
      $skip_before_filter = $skip_filters[$filter];

      if (is_array($skip_before_filter)) {
        return empty($skip_before_filter) ||
          (isset($skip_before_filter['only']) && in_array($action, $skip_before_filter['only'])) ||
          (isset($skip_before_filter['except']) && !in_array($action, $skip_before_filter['except']));
      }

      return true;
    }

    return false;
  }

  public function set_route_params(array $parameters)
  {
    $this->ROUTE_PARAMS = $parameters;
  }
  public function get_route_param(string $parameter)
  {
    return $this->ROUTE_PARAMS[$parameter] ?? null;
  }
  public function get_route_params(): array
  {
    return $this->ROUTE_PARAMS;
  }

  public function set_request_uri(string $request_uri)
  {
    $this->REQUEST_URI = $request_uri;
  }
  public function get_request_uri()
  {
    return $this->REQUEST_URI;
  }
  public function get_request_uri_params()
  {
    $uri = ltrim($this->REQUEST_URI, "/");
    $uri_params = explode('/', $uri);

    return $uri_params;
  }

  public function render(string $action = '', ?string $controller_name = '', array $messages = [])
  {
    // set session messages if provided
    foreach ($messages as $type => $message) {
      $this->set_flash($type, $message);
    }

    // Use debug_backtrace() to get the calling function's name
    if ($action === '' || $action === null) {
      $backtrace = debug_backtrace();
      $calling_function = $backtrace[1]['function'];
      $action = $calling_function;
    }

    if ($controller_name === '' || $controller_name === null) {
      $controller_name = get_class($this);
    }

    $config = [
      'page_info' => $this->PAGE_INFO,
      'page_layout' => $this->PAGE_LAYOUT,
      'inline_css' => $this->INLINE_CSS,
      'inline_js' => $this->INLINE_JS,
      'objects' => $this->OBJECTS,
      'errors' => $this->ERRORS,
    ];

    $page = new ActionView($controller_name, $action);
    $page->prepare($config);
    $page->view();
  }

  public function redirect(string $url = '/', array $messages = [])
  {
    $this->clear_flash();

    // set session messages if provided
    foreach ($messages as $type => $message) {
      $this->set_flash($type, $message);
    }


    // $params = [];

    // if (!empty($messages['errors'])) {
    //   $params['errors'] = count($messages['errors']);
    // }

    $redirect_url = self::$ROOT_URL . $url;
    // if (!empty($params)) {
    //   $redirect_url .= '?' . http_build_query($params);
    // }

    header("Location:" . $redirect_url);
    exit();
  }

  public function execute(string $action)
  {
    foreach (static::$before_action as $filter => $options) {
      if ($this->filter_should_apply($action, $options) && !$this->filter_should_skip(static::$skip_before_action, $filter, $action)) {
        $this->$filter();
      }
    }

    $this->$action();

    foreach (static::$after_action as $filter => $options) {
      if ($this->filter_should_apply($action, $options) && !$this->filter_should_skip(static::$skip_after_action, $filter, $action)) {
        $this->$filter();
      }
    }
  }

  protected function normalize_filter_array(array $filters)
  {
    $normalized_filters = [];

    foreach ($filters as $key => $value) {
      $normalized_filters[is_array($value) ? $key : $value] = is_array($value) ? $value : [];
    }

    return $normalized_filters;
  }

  protected function set_object(string $name, mixed $object)
  {
    $this->OBJECTS[$name] = $object;
  }


  protected function params_permit(array $permitted_fields, mixed $user_input): array
  {
    $params = [];

    foreach ($permitted_fields as $field) {
      if (isset($user_input[$field])) {
        $params[$field] = $user_input[$field];
      }
    }

    return $params;
  }

  protected function set_layout(string $layout)
  {
    $this->PAGE_LAYOUT = $layout;
  }

  protected function set_inline_style(?string $style)
  {
    $this->INLINE_CSS = $style ?? '';
  }
  protected function set_inline_script(?string $script)
  {
    $this->INLINE_JS = $script ?? '';
  }

  protected function set_page_info(array $page_info = [])
  {
    $this->PAGE_INFO = $page_info;
  }
}
