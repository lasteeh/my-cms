<?php

namespace App\Controllers;

use Core\Components\ActionController;

class ApplicationController extends ActionController
{
  public function index()
  {
    $this->render();
  }
}
