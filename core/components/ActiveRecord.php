<?php

namespace Core\Components;

use Core\Base;
use App\Models\Application_Record;
use Core\Database;

class ActiveRecord extends Base
{
  private static string $MODELS_DIRECTORY;

  private $TABLE;
  private static $DB;
  private $ATTRIBUTES = [];

  protected static $validations = [];
  protected static $skip_before_save = [];
  protected static $before_save = [];
  protected static $skip_after_save = [];
  protected static $after_save = [];

  public function __construct(array $model_object = [])
  {
    self::$MODELS_DIRECTORY = self::APP_DIR . '\\' . self::MODELS_DIR;
    $this->TABLE = str_replace(strtolower(self::$MODELS_DIRECTORY . '\\'), '', strtolower(get_class($this))) . 's';

    self::$DB = Database::$PDO;

    $this->create($model_object);
  }

  public function create(array $model_object)
  {
    foreach ($model_object as $attribute => $value) {
      if (property_exists($this, $attribute)) {
        $this->$attribute = $value;
        $this->ATTRIBUTES[$attribute] = $value;
      }
    }
  }

  private function validate(): bool
  {
    if (empty($this->ATTRIBUTES)) {
      $this->ERRORS[] = 'Object is empty';
      return false;
    }

    $errors = [];

    if (empty($errors)) {
      return true;
    } else {
      $this->ERRORS[] = $errors;
      return false;
    }
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

    // write to DB


    // run after save

    var_dump($this);
    echo '<br/ >saved <br/ >';
    return true;
  }
}
