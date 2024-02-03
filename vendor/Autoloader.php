<?php

class Autoloader
{

  public static function register()
  {
    spl_autoload_register([__CLASS__, 'autoload']);
  }

  public static function autoload($class_name)
  {
    $class_file = str_replace('vendor', '', __DIR__) . str_replace('\\', '/', $class_name) . '.php';

    if (file_exists($class_file)) {
      require_once $class_file;
    }
  }
}
