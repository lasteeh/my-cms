<?php

namespace App\Controllers;

use Core\Components\ActionController;
use App\Models\User;

class ApplicationController extends ActionController
{
  private static $CURRENT_USER;

  protected static $before_action = [
    'authenticate_request'  => [
      'except' => ['home'],
    ],
  ];

  public function home()
  {
    $this->render();
  }

  public function dashboard()
  {
    $this->render();
  }

  protected function authenticate_request()
  {
    if (!$this->current_user()) {
      $this->redirect('/login');
    }
  }

  public function current_user(): ?object
  {
    if (!isset($_SESSION['user_id'])) {
      return null;
    }

    if (empty(self::$CURRENT_USER)) {
      $current_user = new User;
      $current_user->find_user_by_id($_SESSION['user_id']);
      self::$CURRENT_USER = $current_user;
    }

    return self::$CURRENT_USER;
  }
}
