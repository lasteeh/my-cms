<?php

namespace App\Controllers;

use App\Controllers\ApplicationController;
use App\Models\Page;

class PagesController extends ApplicationController
{
  protected static $skip_before_action = [
    'authenticate_request'  => [
      'only' => ['show'],
    ],
  ];

  public function index()
  {
    $pages = (new Page)->all();

    $this->set_object('pages', $pages);
    $this->render();
  }

  public function show()
  {
    // TODO: implement showing of pages here
    $uri = $this->get_request_uri();
    $uri_params = explode('/', $uri);
    var_dump($this->get_request_uri());
    var_dump($uri_params);

    $this->set_layout('page');
    if (false) {
      $this->render();
    } else {
      $this->render('not_found', 'application');
    }
  }

  public function new()
  {
    $pages = (new Page)->all();
    $this->set_object('pages', $pages);
    $this->render();
  }

  public function edit()
  {
    $current_page = $this->set_current_page();

    if ($current_page) {
      $pages = $current_page->all();

      $this->set_object('current_page', $current_page);
      $this->set_object('pages', $pages);
      $this->render();
    } else {
      $this->render('not_found', 'application');
    }
  }

  public function update()
  {
    $current_page = $this->set_current_page();

    if ($current_page) {
      $pages = $current_page->all();

      list($current_page, $error_messages) = $current_page->revise($this->page_params());
      if ($error_messages) {
        $this->set_errors($error_messages);
        $this->set_object('current_page', $current_page);
        $this->set_object('pages', $pages);
        $this->render('edit');
      } else {
        $this->redirect('/dashboard/pages');
      }
    } else {
      $this->render('not_found', 'application');
    }
  }

  public function create()
  {
    $page = new Page;
    $pages = $page->all();
    list($page, $error_messages) = $page->publish($this->page_params());

    if ($error_messages) {
      $this->set_object('pages', $pages);
      $this->set_errors($error_messages);
      $this->render('new');
    } else {
      $this->redirect('/dashboard/pages');
    }
  }

  protected function set_current_page()
  {
    return (new Page)->find_by(['id' => $this->get_route_param('id')]);
  }

  private function page_params(): array
  {
    return $this->params_permit(['slug', 'title', 'sub_title', 'description', 'content', 'parent_id'], $_POST);
  }
}
