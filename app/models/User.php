<?php

namespace App\Models;

use App\Models\Application_Record;
use DateTimeImmutable;

class User extends Application_Record
{
  public string $email;
  public string $password;
  public ?string $token;
  public ?string $password_confirmation;
  public string $created_at;
  public string $updated_at;

  protected static $before_validate = [
    'normalize_email',
  ];

  protected static $before_save = [
    'remove_password_confirmation_attribute',
    'hash_password',
  ];

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

  // funtion to register a new user
  public function register(array $user_params): array
  {
    $this->new($user_params);
    $this->save();

    return [$this, $this->ERRORS];
  }

  protected function normalize_email()
  {
    $normalized_email = strtolower($this->email);
    $this->update_attribute('email', $normalized_email);
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

  public function login(array $login_params): array
  {
    $record = $this->find_user_by_email($login_params['email']);

    if (!$record) {
      $this->ERRORS[] = 'invalid email';
      return [null, $this->ERRORS];
    }

    if (!password_verify($login_params['password'], $this->password)) {
      $this->ERRORS[] = 'invalid password';
      return [null, $this->ERRORS];
    }


    // generate token
    $token = $this->generate_token();
    // update user token to db
    $this->update_column('token', $token);
    // store token to session
    $_SESSION['token'] = $this->token;

    return [$this, null];
  }

  private function generate_token()
  {
    $random_string = bin2hex(random_bytes(32));
    $current_date_time = new DateTimeImmutable();
    $formatted_date_time = $current_date_time->format('YmdHisv');
    $identifier = "{$random_string}-{$this->id}-{$formatted_date_time}";
    $token = hash('sha256', $identifier);

    return $token;
  }

  private function find_user_by_email(string $email): ?User
  {
    return $this->find_by(['email' => $email]);
  }

  public function find_user_by_token(string $token): ?User
  {
    return $this->find_by(['token' => $token]);
  }
}
