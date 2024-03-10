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
    $page = (new Page)->find_by('id', $this->get_route_param('id'));
    $this->set_object('page', $page);
    $this->render();
  }
  public function update()
  {
    $page_params = $this->page_params($_POST);
    $page = new Page;

    // TODO:
    // below should return an object
    $record = $page->find_by('id', $this->get_route_param('id'));

    list($page, $error_messages) = $page->publish_updates($page_params);

    // if ($error_messages) {
    //   $this->ERRORS = $error_messages;
    //   $this->render('edit');
    // } else {
    // }
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
  protected function set_page()
  {
  }

  private function page_params(array $user_input): array
  {
    return $this->params_permit(['slug', 'title', 'sub_title', 'description', 'content'], $user_input);
  }
}
