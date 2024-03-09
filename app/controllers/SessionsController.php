<?php

namespace App\Controllers;

use App\Controllers\ApplicationController;
use App\Models\User;

class SessionsController extends ApplicationController
{
  protected static $before_action = [
    'set_layout_page',
  ];
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

  protected function set_layout_page()
  {
    $this->set_layout('page');
  }

  private function login_params(array $user_input): array
  {
    return $this->params_permit(['email', 'password'], $user_input);
  }
}
