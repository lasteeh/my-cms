<?php

namespace Core;

use Core\Components\CMSException;
use Core\Traits\ErrorHandler;

class Database
{
  use ErrorHandler;

  /** DO NOT EDIT *************************************************************************************/
  // file path of the Database configuration relative to the root directory of the project
  private const DB_CONFIG_FILE_PATH = "config/database.php";

  // NOTE: directory/path names must NOT have slashes in front
  /****************************************************************************************************/

  private static string $NAME;
  private static string $HOST;
  private static string $USERNAME;
  private static string $PASSWORD;

  public static $PDO;
  public array $ERRORS = [];

  public function __construct()
  {
    set_exception_handler([$this, 'exception_handler']);

    $this->load_db_config();

    self::$NAME = "your_database_name";
    self::$HOST = "your_database_host";
    self::$USERNAME = "your_database_username";
    self::$PASSWORD = "your_database_password";
  }

  public function PDO()
  {
    if (self::$PDO === null) {
      self::$PDO = $this->create_pdo_instance();
    }
    return self::$PDO;
  }

  public function test_connection()
  {
    if (self::$PDO === null) {
      self::$PDO = $this->create_pdo_instance();
    }

    return self::$PDO !== null;
  }

  private function load_db_config()
  {
    $db_config_file = App::$ROOT_DIR . self::DB_CONFIG_FILE_PATH;
    if (file_exists($db_config_file)) {
      $db_configurations = require_once $db_config_file;

      // map the config to the private variable creds
    } else {
      $this->ERRORS[] = "File not found: {$db_config_file}";
      throw new CMSException();
    }
  }

  private function create_pdo_instance()
  {
    try {
      $pdo = new \PDO("mysql:host=" . self::$HOST . ";dbname=" . self::$NAME, self::$USERNAME, self::$PASSWORD);
      $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

      return $pdo;
    } catch (\PDOException $e) {
      $this->ERRORS[] = "PDO instance creation failed";
      throw new CMSException($e->getMessage());
    }
  }
}
