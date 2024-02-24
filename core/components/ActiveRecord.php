<?php

namespace Core\Components;

use Core\Base;
use App\Models\Application_Record;
use Core\Database;

class ActiveRecord extends Base
{
  public $id;

  private static string $MODELS_DIRECTORY;

  private $TABLE;
  private $MODEL;
  private static $DB;
  private $ATTRIBUTES = [];

  protected static $skip_before_validate = [];
  protected static $before_validate = [];

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

    $this->new($model_object);

    // setup callbacks
    $this->setup_callback('before_validate');
    $this->setup_callback('skip_before_validate');
    $this->setup_callback('before_save');
    $this->setup_callback('skip_before_save');
  }

  private function setup_callback(string $callback_name)
  {
    $all_callbacks = array_merge(self::$$callback_name, Application_Record::$$callback_name, static::$$callback_name);

    static::$$callback_name = $this->normalize_callback_array($all_callbacks);
  }

  public function new(array $model_object)
  {
    foreach ($model_object as $attribute => $value) {
      if (property_exists($this, $attribute)) {
        $this->update_attribute($attribute, $value);
      } else {
        $this->ERRORS[] = "{$this->MODEL} property does not exist: {$attribute}";
        $this->handle_errors();
      }
    }
  }

  private function validate_column_update($column): bool
  {
    if (!$this->ATTRIBUTES[$column]) {
      $this->ERRORS[] = "{$this->$column} does not exist.";
      return false;
    }

    // run only for column
    $errors = $this->validate_field($column);


    if (empty($errors)) {
      return true;
    } else {
      $this->ERRORS = $errors;
      return false;
    }
  }

  private function validate_save(): bool
  {
    if (empty($this->ATTRIBUTES)) {
      $this->ERRORS[] = "{$this->MODEL} is empty";
      return false;
    }

    // run each validator in foreach
    $errors = [];
    foreach ($this->validations as $field => $rules) {
      $errors = array_merge($errors, $this->validate_field($field));
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
    unset($this->$attribute);
    unset($this->ATTRIBUTES[$attribute]);
  }

  protected function update_attribute(string $attribute, $value)
  {
    $this->$attribute = $value;
    $this->ATTRIBUTES[$attribute] = $value;
  }

  protected function update_column(string $column, $value = null): bool
  {
    $old_value = $this->$column;

    if ($value === null) {
      $value = $old_value;
    }
    $this->update_attribute($column, $value);

    // run before validations
    $this->run_before_validate();

    // run validations
    if (!$this->validate_column_update($column)) {
      return false;
    }

    // run after validation

    // run before update

    // write update to DB
    $sql = "UPDATE {$this->TABLE} SET {$column} = :value WHERE id = :id";

    var_dump($this);

    try {
      $statement = self::$DB->prepare($sql);
      $statement->bindValue(':value', $value);
      $statement->bindParam(':id', $this->id);

      $statement->execute();
    } catch (\PDOException $e) {
      $this->ERRORS[] = $e->getMessage();
      $this->handle_errors();
    }

    // run after update


    return true;
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

  public function find_uniquness_by($column, $value)
  {
    $sql = "SELECT id FROM {$this->TABLE} WHERE {$column} = :value";

    $statement = self::$DB->prepare($sql);
    $statement->bindParam(':value', $value);
    $statement->execute();

    $record = $statement->fetch(\PDO::FETCH_ASSOC);

    return $record;
  }

  // TODO: replace with query builder!!!
  public function find_user_by_email(string $email)
  {
    $sql = "SELECT id, email, password, token FROM {$this->TABLE} WHERE email = :value";

    $statement = self::$DB->prepare($sql);
    $statement->bindParam(':value', $email);
    $statement->execute();

    $record = $statement->fetch(\PDO::FETCH_ASSOC);

    return $record;
  }
  public function find_user_by_token(string $token)
  {
    $sql = "SELECT id, email, token FROM {$this->TABLE} WHERE token = :value";

    $statement = self::$DB->prepare($sql);
    $statement->bindParam(':value', $token);
    $statement->execute();

    $record = $statement->fetch(\PDO::FETCH_ASSOC);

    return $record;
  }

  public function save(): bool
  {
    // run before validations
    $this->run_before_validate();

    // run validations
    if (!$this->validate_save()) {
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

      $statement->execute();
    } catch (\PDOException $e) {
      $this->ERRORS[] = $e->getMessage();
      $this->handle_errors();
    }

    // run after save

    return true;
  }

  private function run_before_validate()
  {
    foreach (static::$before_validate as $callback) {
      if (!$this->callback_should_skip($callback, static::$skip_before_validate)) {
        $this->$callback();
      }
    }
  }

  private function callback_should_skip(string $callback, array $skip_after_save_array): bool
  {
    return in_array($callback, $skip_after_save_array);
  }

  private function normalize_callback_array(array $callbacks)
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

  private function validate_field(string $field): array
  {
    $errors = [];

    foreach ($this->validations[$field] as $rule => $value) {
      switch ($rule) {
        case 'numericality':
          if (isset($value['only_integer'])) {
            if (!is_numeric($this->ATTRIBUTES[$field]) || !is_int($this->ATTRIBUTES[$field] + 0)) {
              $errors[] = "{$field} must be an integer.";
            }
          }
          break;

        case 'presence':
          if ($value === true && empty($this->ATTRIBUTES[$field])) {
            $errors[] = "{$field} can't be blank.";
          }
          break;

        case 'uniqueness':
          if ($value === true) {
            $existing_record = $this->find_uniquness_by($field, $this->ATTRIBUTES[$field]);
            if ($existing_record) {
              $errors[] = "{$field} '{$this->ATTRIBUTES[$field]}' already exists.";
            }
          }
          break;

        case 'length':
          if (isset($value['minimum']) && strlen($this->ATTRIBUTES[$field]) < $value['minimum']) {
            $errors[] = "{$field} is too short (minimum length: {$value['minimum']} characters).";
          }
          break;

        case 'confirmation':
          $confirmation_field = "{$field}_confirmation";
          if ($value === true && $this->ATTRIBUTES[$field] !== $this->ATTRIBUTES[$confirmation_field]) {
            $errors[] = "{$field} and {$confirmation_field} do not match.";
          }
          break;
      }
    }

    return $errors;
  }
}
