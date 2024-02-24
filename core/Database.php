<?php

namespace Core;

use Core\Base;

class Database extends Base
{
  private static string $NAME;
  private static string $HOST;
  private static string $USERNAME;
  private static string $PASSWORD;

  public static $PDO;

  public function __construct()
  {
    parent::__construct();

    $config = $this->load_db_config();
    self::$NAME = $config['db_name'];
    self::$HOST = $config['db_host'];
    self::$USERNAME = $config['db_username'];
    self::$PASSWORD = $config['db_password'];
  }

  public function PDO(): \PDO
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
    $db_config_file = self::$ROOT_DIR . self::CONFIG_DIR . '/database.php';

    if (file_exists($db_config_file)) {
      $db_configurations = require_once $db_config_file;

      return $db_configurations;
    } else {
      $this->ERRORS[] = "File not found: {$db_config_file}";
      $this->handle_errors();
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
      $this->handle_errors($e->getMessage());
    }
  }
}
