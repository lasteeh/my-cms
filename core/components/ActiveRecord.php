<?php

namespace Core\Components;

use Core\Base;
use App\Models\Application_Record;
use Core\Database;

class ActiveRecord extends Base
{
  private static string $MODELS_DIRECTORY;

  private $TABLE;
  private $MODEL;
  private static $DB;
  private $ATTRIBUTES = [];

  protected $validations = [];

  protected static $skip_before_save = [];
  protected static $before_save = [];
  protected static $skip_after_save = [];
  protected static $after_save = [];

  public function __construct(array $model_object = [])
  {
    self::$MODELS_DIRECTORY = self::APP_DIR . '\\' . self::MODELS_DIR;

    $model_name =  str_replace(strtolower(self::$MODELS_DIRECTORY . '\\'), '', strtolower(get_class($this)));
    $this->TABLE = $model_name . 's';
    $this->MODEL = ucfirst($model_name);

    self::$DB = Database::$PDO;

    $this->create($model_object);

    // setup callbacks
    $this->get_before_save_callbacks();
    $this->get_skip_before_save_callbacks();
  }

  protected function get_before_save_callbacks()
  {
    $all_before_save = array_merge(
      self::$before_save,
      Application_Record::$before_save,
      static::$before_save
    );

    static::$before_save = $this->normalize_callback_array($all_before_save);
  }
  protected function get_skip_before_save_callbacks()
  {
    $all_skip_before_save = array_merge(
      self::$skip_before_save,
      Application_Record::$skip_before_save,
      static::$skip_before_save
    );

    static::$skip_before_save = $this->normalize_callback_array($all_skip_before_save);
  }

  public function create(array $model_object)
  {
    foreach ($model_object as $attribute => $value) {
      if (property_exists($this, $attribute)) {
        $this->$attribute = $value;
        $this->ATTRIBUTES[$attribute] = $value;
      } else {
        $this->ERRORS[] = "{$this->MODEL} property does not exist: {$attribute}";
        $this->handle_errors();
      }
    }
  }

  private function validate(): bool
  {
    if (empty($this->ATTRIBUTES)) {
      $this->ERRORS[] = "{$this->MODEL} is empty";
      return false;
    }

    $errors = [];

    // run each validator in foreach
    foreach ($this->validations as $field => $rules) {
      $data_type = \PDO::PARAM_STR;

      foreach ($rules as $rule => $value) {
        if ($rule === 'numericality' && isset($value['only_integer'])) {
          if (!is_numeric($this->ATTRIBUTES[$field]) || !is_int($this->ATTRIBUTES[$field] + 0)) {
            $data_type = \PDO::PARAM_INT;

            $errors[] = "{$field} must be an integer.";
          }
        }

        if ($rule === 'presence' && $value === true) {
          if (empty($this->ATTRIBUTES[$field])) {
            $errors[] = "{$field} can't be blank.";
          }
        }

        if ($rule === 'uniqueness' && $value === true) {
          $existing_record = $this->find_by($field, $this->ATTRIBUTES[$field]);

          if ($existing_record) {
            $errors[] = "{$field} '{$this->ATTRIBUTES[$field]}' already exists.";
          }
        }

        if ($rule === 'length' && isset($value['minimum'])) {
          if (strlen($this->ATTRIBUTES[$field]) < $value['minimum']) {
            $errors[] = "{$field} is too short (minimum length: " . $value['minimum'] . " characters).";
          }
        }

        if ($rule === 'confirmation' && $value === true) {
          $confirmation_field = "{$field}_confirmation";
          if ($this->ATTRIBUTES[$field] !== $this->ATTRIBUTES[$confirmation_field]) {
            $errors[] = "{$field} and {$confirmation_field} do not match.";
          }
        }
      }
    }


    if (empty($errors)) {
      return true;
    } else {
      $this->ERRORS = $errors;
      return false;
    }
  }

  protected function remove_attribute(string $attribute)
  {
    unset($this->ATTRIBUTES["{$attribute}"]);
  }

  protected function update_attribute(string $attribute, $value)
  {
    $this->ATTRIBUTES["{$attribute}"] = $value;
  }

  public function find_by($column, $value)
  {
    $sql = "SELECT * FROM {$this->TABLE} WHERE {$column} = :value";

    $statement = self::$DB->prepare($sql);
    $statement->bindParam(':value', $value);
    $statement->execute();

    $record = $statement->fetch(\PDO::FETCH_ASSOC);

    return $record;
  }

  public function save(): bool
  {
    // run before validations

    // run validations
    if (!$this->validate()) {
      return false;
    }

    // run after validations

    // run before save
    foreach (static::$before_save as $callback) {
      if (!$this->callback_should_skip($callback, static::$skip_before_save)) {
        $this->$callback();
      }
    }

    // write to DB
    $sql = "INSERT INTO {$this->TABLE} (";
    $values = "VALUES (";

    $param_values = [];

    foreach ($this->ATTRIBUTES as $key => $value) {
      $sql .= "{$key}, ";
      $values .= ":{$key}, ";
      $param_values[":{$key}"] = $value;
    }

    $sql = rtrim($sql, ", ") . ") ";
    $values = rtrim($values, ", ") . ")";

    $sql .= $values;

    try {
      $statement = self::$DB->prepare($sql);

      foreach ($param_values as $key => $value) {
        $statement->bindValue($key, $value);
      }

      $result = $statement->execute();

      return $result;
    } catch (\PDOException $e) {
      $this->ERRORS[] = $e->getMessage();
      $this->handle_errors();
    }

    // run after save
    return true;
  }

  protected function callback_should_skip(string $callback, array $skip_after_save_array): bool
  {
    return in_array($callback, $skip_after_save_array);
  }

  protected function normalize_callback_array(array $callbacks)
  {
    $normalized_callbacks = [];

    foreach ($callbacks as $callback) {
      if (is_string($callback) && method_exists($this, $callback)) {
        $normalized_callbacks[] = $callback;
      } else {
        $this->ERRORS[] = "Function does not exist: {$callback}()";
        $this->handle_errors();
      }
    }

    return $normalized_callbacks;
  }
}
