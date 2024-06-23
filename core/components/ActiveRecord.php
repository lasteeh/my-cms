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
  private $OLD_ATTRIBUTES = [];
  private bool $EXISTING_RECORD = false;

  protected static $skip_before_validate = [];
  protected static $before_validate = [];
  protected static $skip_after_validate = [];
  protected static $after_validate = [];

  protected static $validate = [];
  protected static $skip_validate = [];

  protected $validations = [];

  protected static $skip_before_update = [];
  protected static $before_update = [];
  protected static $skip_after_update = [];
  protected static $after_update = [];

  protected static $skip_before_save = [];
  protected static $before_save = [];
  protected static $skip_after_save = [];
  protected static $after_save = [];

  protected static $skip_before_create = [];
  protected static $before_create = [];
  protected static $skip_after_create = [];
  protected static $after_create = [];

  protected static $skip_before_destroy = [];
  protected static $before_destroy = [];
  protected static $skip_after_destroy = [];
  protected static $after_destroy = [];

  public function __construct(array $model_object = [])
  {
    self::$MODELS_DIRECTORY = self::APP_DIR . '\\' . self::MODELS_DIR;

    $model_name =  str_replace(strtolower(self::$MODELS_DIRECTORY . '\\'), '', strtolower(get_class($this)));
    $this->TABLE = $this->pluralize($model_name);
    $this->MODEL = ucfirst($model_name);

    self::$DB = Database::$PDO;

    $this->map_attributes($model_object);

    // setup callbacks
    $this->setup_callback('skip_before_validate');
    $this->setup_callback('before_validate');
    $this->setup_callback('skip_validate');
    $this->setup_callback('validate');
    $this->setup_callback('skip_after_validate');
    $this->setup_callback('after_validate');

    $this->setup_callback('skip_before_update');
    $this->setup_callback('before_update');
    $this->setup_callback('skip_after_update');
    $this->setup_callback('after_update');

    $this->setup_callback('skip_before_create');
    $this->setup_callback('before_create');
    $this->setup_callback('skip_after_create');
    $this->setup_callback('after_create');

    $this->setup_callback('skip_before_save');
    $this->setup_callback('before_save');
    $this->setup_callback('skip_after_save');
    $this->setup_callback('after_save');

    $this->setup_callback('skip_before_destroy');
    $this->setup_callback('before_destroy');
    $this->setup_callback('skip_after_destroy');
    $this->setup_callback('after_destroy');
  }

  private function pluralize(string $word): string
  {
    $last_letter = strtolower($word[strlen($word) - 1]);

    if ($last_letter == 'y') {
      return substr($word, 0, -1) . 'ies';
    }

    // default rule: just add 's'
    return $word . 's';
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
    new self();
    $this->map_attributes($object_params);

    return $this;
  }
  // set array key value pairs to object variables for a existing record
  public function exist(array $object_params): object
  {
    $this->reset();
    new self();
    $this->set_as_existing_record();
    $this->map_attributes($object_params);

    return $this;
  }

  private function set_as_existing_record()
  {
    $this->EXISTING_RECORD = true;
  }

  private function reset()
  {
    // reset attributes and errors
    unset($this->id);
    $this->EXISTING_RECORD = false;
    $this->ATTRIBUTES = [];
    $this->OLD_ATTRIBUTES = [];
    $this->ERRORS = [];
  }

  private function map_attributes(array $attributes)
  {
    foreach ($attributes as $attribute => $value) {
      if (property_exists($this, $attribute)) {
        $this->set_attribute($attribute, $value);
        if ($this->is_an_existing_record()) {
          $this->store_attribute($attribute, $value);
        }
      } else {
        $this->ERRORS[] = "{$this->MODEL} property does not exist: {$attribute}";
        $this->handle_errors();
      }
    }
  }

  private function store_attribute(string $attribute, $value)
  {
    $this->OLD_ATTRIBUTES[$attribute] = $value;
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
    // check if record exists
    $exists = $this->is_an_existing_record();
    $columns = $exists ? $this->get_updated_columns() : array_keys($this->validations);

    // run before validations
    $this->run_callback('before_validate');

    // run validations
    if (!$this->validate($columns)) {
      return false;
    }

    // run custom validations
    $this->run_callback('validate');

    // check for errors
    if ($this->has_errors()) {
      return false;
    }

    // run after validations
    $this->run_callback('after_validate');

    // run before save
    $this->run_callback('before_save');

    // actual save function
    $sql = "";
    $param_values = [];
    if ($exists) {

      // run before update
      $this->run_callback('before_update');

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

      // run before create
      $this->run_callback('before_create');

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

      if ($exists) {
        // run after update
        $this->run_callback('after_update');
      } else {
        // run after create
        $this->run_callback('after_create');
      }
    } catch (\PDOException $e) {
      $this->ERRORS[] = $e->getMessage();
      $this->handle_errors();
    }


    // run after save
    $this->run_callback('after_save');

    return true;
  }

  public function insert_all(array $objects, array $options = []): array|false
  {
    if (empty($objects)) {
      return false;
    }

    $inserted_rows = []; // the array that we will return later

    // set options
    $unique_column = $options['unique_by'] ?? null;
    $batch_size = $options['batch_size'] ?? 100;

    // prepare fields and values for batch insert
    $fields  = array_keys(reset($objects));

    // split objects into chunks
    $chunks = array_chunk($objects, $batch_size);

    foreach ($chunks as $chunk) {
      // prepare values and placeholders for current batch

      $values = [];
      $placeholders = [];

      // prepare sql parts
      $insert_sql = "INSERT INTO {$this->TABLE} (" . implode(",", $fields) . ") VALUES ";
      $update_sql = $unique_column ? " ON DUPLICATE KEY UPDATE " : "";

      if ($unique_column) {
        foreach ($fields as $field) {
          if ($field === $unique_column) continue;

          $update_sql .= "{$field} = VALUES({$field}), ";
        }

        $update_sql = rtrim($update_sql, ", ");
      }

      foreach ($chunk as $object) {
        $row = [];
        foreach ($fields as $field) {
          $row[] = $object[$field];
        }
        $values =  array_merge($values, $row);
        $placeholders[] = "(" . implode(",", array_fill(0, count($fields), "?")) . ")";
      }

      $sql = $insert_sql . implode(",", $placeholders) . $update_sql;

      try {
        $statement = self::$DB->prepare($sql);

        foreach ($values as $key => $value) {
          $statement->bindValue($key + 1, $value);
        }

        $statement->execute();

        $last_inserted_id = self::$DB->lastInsertId();
        $affected_rows = $statement->rowCount();

        for ($i = 0; $i < $affected_rows; $i++) {
          $inserted_rows[] = $last_inserted_id + $i;
        }
      } catch (\PDOException $e) {
        $this->ERRORS[] = $e->getMessage();
        $this->handle_errors();
      }
    }



    return $inserted_rows;
  }

  public function destroy(): bool
  {
    if (!$this->is_an_existing_record()) {
      $this->ERRORS[] = "{$this->MODEL} does not exist.";
      return false;
    }

    // run before destroy callback
    $this->run_callback('before_destroy');

    // perform deletion
    $sql = "DELETE FROM {$this->TABLE} WHERE id = :id";
    try {
      $statement = self::$DB->prepare($sql);
      $statement->bindParam(":id", $this->id);
      $statement->execute();

      // run after destroy callback
      $this->run_callback('after_destroy');
    } catch (\PDOException $e) {
      $this->ERRORS[] = $e->getMessage();
      $this->handle_errors();
    }
    return true;
  }

  public function is_an_existing_record(): bool
  {
    if ($this->EXISTING_RECORD) {
      return true;
    }

    if (isset($this->id) && isset($this->ATTRIBUTES['id'])) {
      $sql = "SELECT 1 FROM {$this->TABLE} WHERE id = ?";
      $statement = self::$DB->prepare($sql);
      $statement->execute([$this->id]);
      return $statement->fetchColumn() > 0;
    }

    return false;
  }

  private function get_updated_columns(): array
  {
    $updated_columns = [];

    foreach ($this->ATTRIBUTES as $key => $value) {
      if ($value !== $this->OLD_ATTRIBUTES[$key]) {
        $updated_columns[] = $key;
      }
    }

    return $updated_columns;
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

  private function callback_should_skip(string $callback, array $skip_callback_array): bool
  {
    return in_array($callback, $skip_callback_array);
  }

  private function validate(array $columns = []): bool
  {
    if (empty($this->ATTRIBUTES)) {
      $this->ERRORS[] = "{$this->MODEL} is empty";
      return false;
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

  public function get_attribute(string $attribute)
  {
    return $this->ATTRIBUTES[$attribute] ?? null;
  }

  public function update_attribute(string $attribute, $value)
  {
    $this->set_attribute($attribute, $value);
  }

  public function update_attributes(array $attributes)
  {
    foreach ($attributes as $attribute => $value) {
      $this->set_attribute($attribute, $value);
    }
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

    // run custom validations
    $this->run_callback('validate');

    // check for errors
    if ($this->has_errors()) {
      return false;
    }

    // run after validations
    $this->run_callback('after_validate');

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
    $this->run_callback('after_update');

    return true;
  }

  public function paginate(int $current_page, int $limit, string $sort_order = 'asc',  string $sort_by = 'id', array $sort_columns = ['id']): array
  {
    $results = [];
    $total_pages = 0;

    // get total number of records
    $total_records = $this->count();

    // skip second query if no records found
    if (empty($total_records)) {
      return [$results, $total_pages];
    }

    // determine total pages
    $total_pages = ceil($total_records / $limit);

    // prevent out of bounds page number
    if ($current_page < 1) {
      $current_page = 1;
    } elseif ($current_page > $total_pages) {
      $current_page = $total_pages;
    }

    // calculate offset for sql query
    $offset = ($current_page - 1) * $limit;


    $sql = "SELECT * FROM {$this->TABLE} ORDER BY {$sort_by} {$sort_order} LIMIT {$offset}, {$limit}";

    try {
      $statement = self::$DB->prepare($sql);
      $statement->execute();
      $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
      $this->ERRORS[] = $e->getMessage();
      $this->handle_errors();
    }

    return [$results, $total_pages];
  }


  // QUERIES

  public function count(): int
  {
    $sql = "SELECT COUNT(*) AS count from {$this->TABLE}";

    $row_count = false;

    try {
      $statement = self::$DB->prepare($sql);
      $statement->execute();

      $row_count = $statement->fetchColumn();
    } catch (\PDOException $e) {
      $this->ERRORS[] = $e->getMessage();
      $this->handle_errors();
    }

    return $row_count;
  }

  public function all(): array
  {
    $sql = "SELECT * FROM {$this->TABLE}";

    $statement = self::$DB->query($sql);
    $records = $statement->fetchAll(\PDO::FETCH_ASSOC);

    return $records;
  }

  public function fetch_by(array $conditions, array $returned_columns = []): array
  {
    $select_clause = QueryBuilder::build_select_clause($returned_columns, $this);

    if (empty($select_clause)) {
      $this->ERRORS[] = "No valid columns.";
      $this->handle_errors();
    }

    try {
      $sql = "SELECT {$select_clause} FROM {$this->TABLE} WHERE ";
      [$sql, $placeholders] = QueryBuilder::build_where_clause($sql, $conditions);

      $statement = self::$DB->prepare($sql);
      foreach ($placeholders as $placeholder => $value) {
        $statement->bindValue($placeholder, $value);
      }

      $statement->execute();
      return $statement->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
      $this->ERRORS[] = $e->getMessage();
      $this->handle_errors();
    }
  }

  public function find_by(array $conditions, array $returned_columns = []): ?object
  {
    $select_clause = QueryBuilder::build_select_clause($returned_columns, $this);

    if (empty($select_clause)) {
      $this->ERRORS[] = "No valid columns.";
      $this->handle_errors();
    }

    try {
      $sql = "SELECT {$select_clause} FROM {$this->TABLE} WHERE ";
      [$sql, $placeholders] = QueryBuilder::build_where_clause($sql, $conditions);

      $statement = self::$DB->prepare($sql);
      foreach ($placeholders as $placeholder => $value) {
        $statement->bindValue($placeholder, $value);
      }
      $statement->execute();

      $result = $statement->fetch(\PDO::FETCH_ASSOC);

      return $result ? $this->exist($result) : null;
    } catch (\PDOException $e) {
      $this->ERRORS[] = $e->getMessage();
      $this->handle_errors();
    }
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
}
