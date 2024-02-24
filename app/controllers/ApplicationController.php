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

  public function current_user()
  {
    if (!isset($_SESSION['token'])) {
      return null;
    }

    if (empty(self::$CURRENT_USER)) {
      $record = (new User)->find_user_by_token($_SESSION['token']);
      if ($record) {
        self::$CURRENT_USER = $record;
      }
    }

    return self::$CURRENT_USER;
  }
}
