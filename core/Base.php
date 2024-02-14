<?php

namespace Core;

use Core\Traits\ErrorHandler;

class Base
{
  use ErrorHandler;

  /** DO NOT EDIT *************************************************************************************/
  // directory names
  protected const CORE_DIR = 'core';
  protected const APP_DIR = 'app';
  protected const DB_DIR = 'db';
  protected const CONTROLLERS_DIR = 'controllers';
  protected const MODELS_DIR = 'models';
  protected const VIEWS_DIR = 'views';
  protected const CONFIG_DIR = 'config';
  protected const MIGRATIONS_DIR = 'migrations';
  protected const INDEX_FILE_PATH = 'public/index.php';

  private static $ENV_LOADED = false;
  private static $ERROR_HANDLER_SET = false;
  /****************************************************************************************************/

  public static $ROOT_DIR;
  public static $ROOT_URL;
  public array $ERRORS = [];

  public function __construct()
  {
    // initiate exception handler setup
    if (!self::$ERROR_HANDLER_SET) {
      set_exception_handler([$this, 'handle_errors']);
      self::$ERROR_HANDLER_SET = true;
    }

    // initiate global variables
    self::$ROOT_DIR = str_replace(self::CORE_DIR, '', __DIR__);
    if (isset($_SERVER['REQUEST_SCHEME']) && isset($_SERVER['HTTP_HOST'])) {
      self::$ROOT_URL = ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost/') . str_replace('/' . self::INDEX_FILE_PATH, '', $_SERVER['SCRIPT_NAME']);
    } else {
      self::$ROOT_URL = 'CLI';
    }

    // set env variables
    if (!self::$ENV_LOADED) {
      self::load_env_file();
      self::$ENV_LOADED = true;
    }
  }

  protected static function load_env_file()
  {
    $env_file = self::$ROOT_DIR .  ".env"; // file path to the .env file

    if (file_exists($env_file)) {
      $env_file_contents = file_get_contents($env_file);

      $lines = explode("\n", $env_file_contents);

      foreach ($lines as $line) {
        if (empty($line) || strpos($line, '#') === 0) {
          continue;
        }

        $line_entry = explode('=', $line, 2);

        if (count($line_entry) === 2) {
          list($key, $value) = $line_entry;

          $_ENV[trim($key)] = trim($value);
        }
      }
    }
  }
}
