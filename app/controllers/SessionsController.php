<?php

namespace App\Controllers;

use App\Controllers\ApplicationController;
use App\Models\User;

class SessionsController extends ApplicationController
{
  public function new()
  {
    $this->render();
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
