<?php

namespace App\Models;

use App\Models\Application_Record;

class User extends Application_Record
{
  public string $email;
  public string $password;
  public string $password_confirmation;

  protected $validations = [
    'email' => [
      'presence' => true,
      'uniqueness' => true,
    ],
    'password' => [
      'length' => [
        'minimum' => 5,
      ],
      'confirmation' => true,
    ],
    'password_confirmation' => [
      'presence' => true,
    ],
  ];

  protected static $before_save = [
    'remove_password_confirmation_attribute',
    'hash_password',
  ];

  public function register(array $user_params): array
  {
    $this->create($user_params);
    $this->save();

    return [$this, $this->ERRORS];
  }

  protected function remove_password_confirmation_attribute()
  {
    $this->remove_attribute('password_confirmation');
  }

  protected function hash_password()
  {
    $hashed_password = password_hash($this->password, PASSWORD_BCRYPT);
    $this->update_attribute('password', $hashed_password);
  }
}
