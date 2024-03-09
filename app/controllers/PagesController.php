<?php

namespace App\Controllers;

use App\Controllers\ApplicationController;
use App\Models\Page;

class PagesController extends ApplicationController
{
  public function index()
  {
    $this->render();
  }
  public function new()
  {
    $this->render();
  }
  public function edit()
  {
    var_dump($this->get_route_param('id'));
  }

  public function create()
  {
    $page_params = $this->page_params($_POST);
    $page = new Page;

    list($page, $error_messages) = $page->publish($page_params);

    if ($error_messages) {
      $this->ERRORS = $error_messages;
      $this->render('new');
    } else {
      $this->redirect('/dashboard/pages');
    }
  }

  private function page_params(array $user_input): array
  {
    return $this->params_permit(['slug', 'title', 'sub_title', 'description', 'content'], $user_input);
  }
}
