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

  public function show()
  {
    // TODO: implement showing of pages here

    $this->set_layout('page');
    $this->render();
  }

  public function new()
  {
    $this->render();
  }

  public function edit()
  {
    $page = $this->set_page();

    if (!$page) {
      $this->redirect('/not-found');
    }

    $this->set_object('page', $page);
    $this->render();
  }

  public function update()
  {
    $page = $this->set_page();

    if (!$page) {
      $this->redirect('/not-found');
    }

    list($page, $error_messages) = $page->revise($this->page_params());
    if ($error_messages) {
      $this->set_errors($error_messages);
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
      $this->set_errors($error_messages);
      $this->render('new');
    } else {
      $this->redirect('/dashboard/pages');
    }
  }

  protected function set_page()
  {
    return (new Page)->find_by(['id' => $this->get_route_param('id')]);
  }

  private function page_params(): array
  {
    return $this->params_permit(['slug', 'title', 'sub_title', 'description', 'content'], $_POST);
  }
}
