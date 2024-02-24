<?php

namespace App\Controllers;

use App\Controllers\ApplicationController;
use App\Models\User;

class SessionsController extends ApplicationController
{
  protected static $skip_before_action = [
    'authenticate_request',
  ];

  public function new()
  {
    $current_user = $this->current_user();

    if (empty($current_user)) {
      $this->render();
    } else {
      $this->redirect('/dashboard');
    }
  }

  public function create()
  {
    $login_params = $this->login_params($_POST);
    $user = new User;
    list($user, $error_messages) = $user->login($login_params);

    if ($error_messages) {
      $this->ERRORS = $error_messages;
      $this->render('new');
    } else {
      $this->redirect('/dashboard');
    }
  }

  public function destroy()
  {
    session_unset();
    session_destroy();
    $this->redirect('/');
  }

  private function login_params(array $user_input): array
  {
    $permitted_fields = ['email', 'password'];
    $login_params = [];

    foreach ($permitted_fields as $field) {
      if (isset($user_input[$field])) {
        $login_params[$field] = $user_input[$field];
      }
    }

    return $login_params;
  }
}
