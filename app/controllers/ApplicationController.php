<?php

namespace App\Controllers;

use Core\Components\ActionController;
use App\Models\User;

class ApplicationController extends ActionController
{
  private static $CURRENT_USER;

  protected static $before_action = [
    'authenticate_request'  => [
      'except' => ['home', 'not_found'],
    ],
  ];

  public function home()
  {
    $this->set_layout('page');
    $this->render();
  }


  public function not_found()
  {
    $this->PAGE_INFO = [
      'page_title' => 'Page Not Found',
    ];
    $this->set_layout('page');
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

  public function current_user(): ?array
  {
    if (!isset($_SESSION['token'])) {
      return null;
    }

    if (empty(self::$CURRENT_USER)) {
      $record = (new User)->find_user_by_token($_SESSION['token']);
      if ($record) {
        self::$CURRENT_USER = [
          'id' => $record->id,
          'email' => $record->email,
        ];
      }
    }

    return self::$CURRENT_USER;
  }
}
