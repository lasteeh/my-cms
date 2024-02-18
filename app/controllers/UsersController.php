<?php

namespace App\Controllers;

use App\Controllers\ApplicationController;
use App\Models\User;

class UsersController extends ApplicationController
{
  public function index()
  {
    $this->render();
  }

  public function new()
  {
    $this->render();
  }

  public function create()
  {
    $user_params = $this->user_params($_POST);
    $user = new User;

    list($user, $error_messages) = $user->register($user_params);

    if ($error_messages) {
      $this->ERRORS = $error_messages;
      $this->render('new');
    } else {
      $this->redirect('/login');
    }
  }

  private function user_params(array $user_input): array
  {
    $permitted_fields = ['email', 'password', 'password_confirmation'];
    $user_params = [];

    foreach ($permitted_fields as $field) {
      if (isset($user_input[$field])) {
        $user_params[$field] = $user_input[$field];
      }
    }

    return $user_params;
  }
}
