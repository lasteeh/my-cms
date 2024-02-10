<?php

namespace Core\Components;

use Core\Base;
use Core\App;
use Core\Components\ActionView;
use App\Controllers\ApplicationController;

class ActionController extends Base
{
  protected static $skip_before_action = [];
  protected static $before_action = [];
  protected static $skip_after_action = [];
  protected static $after_action = [];

  public function __construct()
  {
    $this->get_before_action_filters();
    $this->get_skip_before_action_filters();
    $this->get_after_action_filters();
    $this->get_skip_after_action_filters();
  }

  public function get_before_action_filters()
  {
    $all_before_action = array_merge(
      self::$before_action,
      ApplicationController::$before_action,
      static::$before_action
    );

    static::$before_action = $this->normalize_filter_array($all_before_action);
  }

  public function get_skip_before_action_filters()
  {
    $all_skip_before_action = array_merge(
      self::$skip_before_action,
      ApplicationController::$skip_before_action,
      static::$skip_before_action
    );

    static::$skip_before_action = $this->normalize_filter_array($all_skip_before_action);
  }
  public function get_after_action_filters()
  {
    $all_after_action = array_merge(
      self::$after_action,
      ApplicationController::$after_action,
      static::$after_action
    );

    static::$after_action = $this->normalize_filter_array($all_after_action);
  }

  public function get_skip_after_action_filters()
  {
    $all_skip_after_action = array_merge(
      self::$skip_after_action,
      ApplicationController::$skip_after_action,
      static::$skip_after_action
    );

    static::$skip_after_action = $this->normalize_filter_array($all_skip_after_action);
  }

  public function filter_should_apply(string $action, array $options = [])
  {
    return empty($options) ||
      (isset($options['only']) && in_array($action, $options['only'])) ||
      (isset($options['except']) && !in_array($action, $options['except']));
  }

  public function filter_should_skip($skip_filters, $filter, string $action)
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

  public function not_found()
  {
    $page_info = [
      'page_title' => 'Page Not Found',
    ];
    $this->render($page_info);
  }

  public function render(array $page_info = [])
  {
    // Use debug_backtrace() to get the calling function's name
    $backtrace = debug_backtrace();
    $calling_function = $backtrace[1]['function'];
    $action = $calling_function;

    $controller = get_class($this);

    $page = new ActionView($controller, $action);
    $page->prepare($page_info);
    $page->view();
  }

  public function redirect(string $url = '/')
  {
    header("Location:" . self::$ROOT_URL . $url);
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
}
