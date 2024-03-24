<?php

namespace Core\Components;

use Core\Base;
use App\Models\Application_Record;
use Core\Database;

class ActiveRecord extends Base
{
  private static string $MODELS_DIRECTORY;

  public $id;

  private $TABLE;
  private $MODEL;
  private static $DB;
  private $ATTRIBUTES = [];

  protected static $skip_before_validate = [];
  protected static $before_validate = [];
  protected static $skip_after_validate = [];
  protected static $after_validate = [];

  protected $validations = [];

  protected static $skip_before_save = [];
  protected static $before_save = [];
  protected static $skip_after_save = [];
  protected static $after_save = [];

  protected static $skip_before_update = [];
  protected static $before_update = [];
  protected static $skip_after_update = [];
  protected static $after_update = [];

  public function __construct(array $model_object = [])
  {
    self::$MODELS_DIRECTORY = self::APP_DIR . '\\' . self::MODELS_DIR;

    $model_name =  str_replace(strtolower(self::$MODELS_DIRECTORY . '\\'), '', strtolower(get_class($this)));
    $this->TABLE = $model_name . 's';
    $this->MODEL = ucfirst($model_name);

    self::$DB = Database::$PDO;

    $this->new($model_object);

    // setup callbacks
    $this->setup_callback('skip_before_validate');
    $this->setup_callback('before_validate');
    $this->setup_callback('skip_after_validate');
    $this->setup_callback('after_validate');

    $this->setup_callback('skip_before_save');
    $this->setup_callback('before_save');
    $this->setup_callback('skip_after_save');
    $this->setup_callback('after_save');

    $this->setup_callback('skip_before_update');
    $this->setup_callback('before_update');
    $this->setup_callback('skip_before_save');
    $this->setup_callback('before_save');
  }

  private function setup_callback(string $callback_name)
  {
    $all_callbacks = array_merge(self::$$callback_name, Application_Record::$$callback_name, static::$$callback_name);

    static::$$callback_name = $this->normalize_callback_array($all_callbacks);
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


  // set array key value pairs to object variables for a new record
  public function new(array $object_params): object
  {
    $this->reset();
    $this->map_attributes($object_params);

    return $this;
  }

  private function reset()
  {
    // Reset attributes and errors
    unset($this->id);
    $this->ATTRIBUTES = [];
    $this->ERRORS = [];
  }

  private function set_attribute(string $attribute, $value)
  {
    $this->$attribute = $value;
    $this->ATTRIBUTES[$attribute] = $value;
  }

  private function unset_attribute(string $attribute)
  {
    unset($this->$attribute);
    unset($this->ATTRIBUTES[$attribute]);
  }

  // create new db entry or update existing one
  public function save(): bool
  {
    // run before validations
    $this->run_callback('before_validate');

    // run validations
    if (!$this->validate()) {
      return false;
    }

    // run after validations

    // run before save
    $this->run_callback('before_save');

    // check if record exists
    $exists = false;
    if (isset($this->id) && isset($this->ATTRIBUTES['id'])) {
      $sql = "SELECT 1 FROM users WHERE id = ?";
      $statement = self::$DB->prepare($sql);
      $statement->execute([$this->id]);
      $exists = $statement->fetchColumn() > 0;
    }
    // actual save function
    $sql = "";
    $param_values = [];
    if ($exists) {
      // update existing record here
      $sql = "UPDATE {$this->TABLE} SET ";
      $set = "";

      foreach ($this->ATTRIBUTES as $key => $value) {
        $set .= "{$key} = :{$key}, ";
        $param_values[":{$key}"] = $value;
      }

      $set = rtrim($set, ", ");

      $sql .= $set . " WHERE id = :id";
    } else {
      // insert new record here
      $sql = "INSERT INTO {$this->TABLE} (";
      $values = "VALUES (";

      foreach ($this->ATTRIBUTES as $key => $value) {
        $sql .= "{$key}, ";
        $values .= ":{$key}, ";
        $param_values[":{$key}"] = $value;
      }

      $sql = rtrim($sql, ", ") . ") ";
      $values = rtrim($values, ", ") . ")";

      $sql .= $values;
    }

    try {
      $statement = self::$DB->prepare($sql);

      foreach ($param_values as $key => $value) {
        $statement->bindValue($key, $value);
      }

      if ($exists) {
        $statement->bindValue(":id", $this->id);
      }

      $statement->execute();
    } catch (\PDOException $e) {
      $this->ERRORS[] = $e->getMessage();
      $this->handle_errors();
    }


    // run after save

    return true;
  }

  private function run_callback($callback_name)
  {
    $skip_callback_name = "skip_{$callback_name}";

    foreach (static::$$callback_name as $callback) {
      if (!$this->callback_should_skip($callback, static::$$skip_callback_name)) {
        $this->$callback();
      }
    }
  }

  private function callback_should_skip(string $callback, array $skip_after_save_array): bool
  {
    return in_array($callback, $skip_after_save_array);
  }

  private function validate(array $columns = []): bool
  {
    if (empty($this->ATTRIBUTES)) {
      $this->ERRORS[] = "{$this->MODEL} is empty";
      return false;
    }

    if (empty($columns)) {
      $columns = array_keys($this->validations);
    }

    // run each validators in foreach
    $errors = [];
    foreach ($columns as $column) {
      if (isset($this->validations[$column])) {
        $errors = array_merge($errors, $this->validate_field($column));
      }
    }

    if (empty($errors)) {
      return true;
    } else {
      $this->ERRORS = $errors;
      return false;
    }
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
            $existing_record = $this->validate_uniqueness_by($field, $this->ATTRIBUTES[$field]);
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

  public function validate_uniqueness_by($column, $value)
  {
    $sql = "SELECT id FROM {$this->TABLE} WHERE {$column} = :value";

    $statement = self::$DB->prepare($sql);
    $statement->bindParam(':value', $value);
    $statement->execute();

    $record = $statement->fetch(\PDO::FETCH_ASSOC);

    return $record;
  }

  public function update_attribute(string $attribute, $value)
  {
    $this->set_attribute($attribute, $value);
  }

  public function remove_attribute(string $attribute)
  {
    $this->unset_attribute($attribute);
  }

  public function update_column(string $column, $value): bool
  {
    $this->update_attribute($column, $value);

    // run before validations
    $this->run_callback('before_validate');

    // run validations
    if (!$this->validate([$column])) {
      return false;
    }

    // run after validations

    // run before update
    $this->run_callback('before_update');

    // actual update function
    $sql = "UPDATE {$this->TABLE} SET {$column} = :value WHERE id = :id";

    try {
      $statement = self::$DB->prepare($sql);
      $statement->bindParam(":value", $value);
      $statement->bindParam(":id", $this->id);

      $statement->execute();
    } catch (\PDOException $e) {
      $this->ERRORS[] = $e->getMessage();
      $this->handle_errors();
    }

    // run after update

    return true;
  }

  private function map_attributes(array $attributes)
  {
    foreach ($attributes as $attribute => $value) {
      if (property_exists($this, $attribute)) {
        $this->set_attribute($attribute, $value);
      } else {
        $this->ERRORS[] = "{$this->MODEL} property does not exist: {$attribute}";
        $this->handle_errors();
      }
    }
  }


  // QUERIES

  public function find_by(array $conditions): ?object
  {
    try {
      $sql = "SELECT * FROM {$this->TABLE} WHERE ";
      $placeholders = [];

      foreach ($conditions as $column => $value) {
        $placeholder = ":{$column}";

        $sql .= "{$column} = {$placeholder} AND ";
        $placeholders[$placeholder] = $value;
      }

      $sql = rtrim($sql, " AND ");

      $statement = self::$DB->prepare($sql);
      foreach ($placeholders as $placeholder => $value) {
        $statement->bindValue($placeholder, $value);
      }
      $statement->execute();

      $result = $statement->fetch(\PDO::FETCH_ASSOC);

      if (!$result) {
        return null;
      } else {
        $this->map_attributes($result);
        return $this;
      }
    } catch (\PDOException $e) {
      $this->ERRORS[] = $e->getMessage();
      $this->handle_errors();
    }
  }
}
