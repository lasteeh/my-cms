<?php

namespace Core;

use DateTime;

class Blacksmith extends Base
{
  /* ALLOWED FILE TYPES FOR CREATION ********************************************************/
  private static $FILE_TYPE = ['migration', 'model', 'controller'];
  /******************************************************************************************/


  protected static string $MIGRATIONS_DIRECTORY;
  protected static string $MODELS_DIRECTORY;
  protected static string $CONTROLLERS_DIRECTORY;
  protected static string $VIEWS_DIRECTORY;

  public function __construct()
  {
    parent::__construct();

    // reset exception handler to default for CLI
    restore_exception_handler();

    self::$MIGRATIONS_DIRECTORY = self::$ROOT_DIR . self::DB_DIR . "\\" . self::MIGRATIONS_DIR;
    self::$MODELS_DIRECTORY = self::$ROOT_DIR . self::APP_DIR . '\\' . self::MODELS_DIR;
    self::$CONTROLLERS_DIRECTORY = self::$ROOT_DIR . self::APP_DIR . '\\' . self::CONTROLLERS_DIR;
    self::$VIEWS_DIRECTORY = self::$ROOT_DIR . self::APP_DIR . '\\' . self::VIEWS_DIR;
  }

  public function make(string $file_type, string $temp_file_name, array $actions = [])
  {
    $this->validate_filename($temp_file_name);

    switch ($file_type) {
      case self::$FILE_TYPE[0]:
        $this->create_migration_file($temp_file_name);
        break;
      case self::$FILE_TYPE[1]:
        $this->create_model_file($temp_file_name);
        break;
      case self::$FILE_TYPE[2]:

        if (count($actions) > 0) {
          foreach ($actions as $action) {
            $this->validate_filename($action);
          }
        }

        $this->create_controller_file($temp_file_name, $actions);
        break;
      default:
        $this->show_usage('make');
        exit(1);
    }
  }

  public function migrate()
  {
    // instantiate PDO
    $db = new Database();
    $db->PDO();

    $migrations_directory = self::$MIGRATIONS_DIRECTORY;
    // check if directory for migrations is valid
    if (!is_dir($migrations_directory)) {
      die("Directory does not exist: {$migrations_directory}\n");
    }

    // fetch files in migrations directory
    $migration_files = glob(($migrations_directory . '\*.sql'));

    // check if fetch is successful
    if ($migration_files === false) {
      die("Error fetching SQL files from $migrations_directory.\n");
    }

    // sort by date through name
    sort($migration_files);

    // ask user confirmation
    $confirmation = readline("Confirm migration? Y/n: ");
    if (strtolower($confirmation) !== 'y') {
      echo "Migration aborted.\n";
      return;
    }

    // execute each sql files
    foreach ($migration_files as $file) {
      $file_path = $file;

      if (file_exists($file_path)) {
        $sql = file_get_contents($file_path);

        try {
          $db::$PDO->exec($sql);
          echo "File executed successfully: {$file}\n";
        } catch (\PDOException $e) {
          echo "Error executing SQL file: {$file}\n" . $e->getMessage() . "\n";
        }
      } else {
        echo "File not found: {$file}";
      }
    }
  }

  public function show_usage(string $function)
  {
    switch ($function) {
      case 'make':
        echo "\n";
        echo "Usage: php make.php <file_type> <file_name> [action1 action2 ...]\n";
        echo "\n";
        echo "Valid file types:\n";
        echo " - migration\n";
        echo " - model\n";
        echo " - controller\n";
        echo "\n";
        echo "Example for controller with actions:\n";
        echo " php make.php controller Users index new create update\n";
        echo "\n";
        echo "NOTE:\n";
        echo "File name should only have letters and underscores.\n";
        echo "Action names are optional for the 'controller' file type.\n";
        echo "\n";
        break;
      default:
        echo "Invalid usage type\n";
    }
  }

  private function create_migration_file(string $temp_file_name)
  {
    $current_date_time = new DateTime();
    $formatted_date_time = $current_date_time->format('YmdHisv');
    $file_name = self::$MIGRATIONS_DIRECTORY . "\\" . $formatted_date_time . "_" . $temp_file_name . ".sql";

    $this->create_file($file_name);
  }

  private function create_model_file(string $temp_file_name)
  {
    $name_parts = explode('_', $temp_file_name);
    $capitalized_name_parts = array_map('ucfirst', $name_parts);
    $formatted_name = implode('_', $capitalized_name_parts);

    $file_name = self::$MODELS_DIRECTORY . "\\" . $formatted_name . ".php";

    $file_content = "<?php namespace App\Models;

use App\Models\Application_Record;

class {$formatted_name} extends Application_Record
{
}";

    $this->create_file($file_name, $file_content);
  }


  private function create_controller_file(string $temp_file_name, array $actions = [])
  {
    $name_parts = explode('_', $temp_file_name);
    $capitalized_name_parts = array_map('ucfirst', $name_parts);
    $formatted_name = implode('', $capitalized_name_parts);
    $controller_name = strtolower($temp_file_name);


    if (!$this->is_plural($formatted_name)) {
      $formatted_name .= 's';
      $controller_name .= 's';
    }

    $formatted_name .= "Controller";

    $file_name = self::$CONTROLLERS_DIRECTORY . "\\" . $formatted_name . ".php";

    $action_definitions = "";

    foreach ($actions as $action) {
      $action_definitions .= "public function {$action}()
  {
    /* insert controller action logic here */
  }

  ";
    }

    $file_content = "<?php namespace App\Controllers;

use App\Controllers\ApplicationController;

class {$formatted_name} extends ApplicationController
{

  {$action_definitions}

}";

    $this->create_file($file_name, $file_content);

    if (count($actions) > 0) {
      foreach ($actions as $action) {
        $this->create_view_file($controller_name, $action);
      }
    }
  }

  private function create_view_file(string $controller_name, string $action)
  {
    $file_directory = strtolower(self::$VIEWS_DIRECTORY . "\\" . $controller_name);
    $file_name =  strtolower($file_directory . "\\" . $action . ".view.php");

    // check if directory exist, create if not
    if (!is_dir($file_directory)) {
      if (!mkdir($file_directory, 0777, true)) {
        die("Failed to create directory: {$file_directory}");
      }
    }

    $this->create_file($file_name);
  }

  private function validate_filename(string $file_name)
  {
    // validate the filename using a regular expression
    if (!preg_match('/^[a-zA-Z_]+$/', $file_name)) {
      die("Invalid filename. Only letters and underscores are allowed.\n");
    }
  }

  private function create_file(string $file_name, string $file_content = '')
  {
    if (file_exists($file_name)) {
      die("File already exist: {$file_name}\n");
    }

    // create a new file with specified name
    $file = fopen(($file_name), 'w');
    fwrite($file, $file_content);
    fclose($file);

    // check if file was created successfully
    if (file_exists($file_name)) {
      echo "File created successfully: {$file_name}\n";

      // open the file with nano (for unix)
      // $text_editor = 'nano';
      // $output = shell_exec("{$text_editor} {$file_name}");

      // if ($output === null) {
      //   echo "Failed to open the file with '{$text_editor}'.\n";
      // } else {
      //   echo "File opened in '{$text_editor}'.\n";
      // }
    } else {
      echo "Failed to create file: {$file_name}\n";
    }
  }

  private function is_plural(string $word)
  {
    $plural_endings = ['s', 'es', 'ies'];

    foreach ($plural_endings as $ending) {
      if (substr($word, -strlen($ending)) === $ending) {
        return true;
      }
    }

    return false;
  }
}
