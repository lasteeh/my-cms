<?php

namespace App\Controllers;

use App\Controllers\ApplicationController;
use App\Models\Page;

class PagesController extends ApplicationController
{
  public function index()
  {
    $pages = (new Page)->all();

    $this->set_object('pages', $pages);
    $this->render();
  }
  public function new()
  {
    $this->render();
  }
  public function edit()
  {
    $page = (new Page)->find_by(['id' => $this->get_route_param('id')]);
    $this->set_object('page', $page);
    $this->render();
  }
  public function update()
  {
    $page = (new Page)->find_by(['id' => $this->get_route_param('id')]);
    list($page, $error_messages) = $page->revise($this->page_params());
    if ($error_messages) {
      $this->ERRORS = $error_messages;
      $this->set_object('page', $page);
      $this->render('edit');
    } else {
      $this->redirect('/dashboard/pages');
    }
  }

  public function create()
  {
    $page = new Page;
    list($page, $error_messages) = $page->publish($this->page_params());

    if ($error_messages) {
      $this->ERRORS = $error_messages;
      $this->render('new');
    } else {
      $this->redirect('/dashboard/pages');
    }
  }
  protected function set_page()
  {
  }

  private function page_params(): array
  {
    return $this->params_permit(['slug', 'title', 'sub_title', 'description', 'content'], $_POST);
  }
}
