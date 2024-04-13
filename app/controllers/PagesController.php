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
    $pages = (new Page)->fetch_all_pages_for_index();

    $this->set_object('pages', $pages);
    $this->render();
  }

  public function show()
  {
    $uri_params = $this->get_request_uri_params();
    $current_page = (new Page)->show_page($uri_params);

    $this->set_layout('page');
    if ($current_page) {
      $this->set_object('current_page', $current_page);
      $this->render();
    } else {
      $this->render('not_found', 'application');
    }
  }

  public function new()
  {
    $pages = (new Page)->fetch_all_pages_for_new();
    $this->set_object('pages', $pages);
    $this->render();
  }

  public function edit()
  {
    $current_page = $this->set_current_page();

    if ($current_page) {
      $pages = $current_page->fetch_all_pages_for_edit();

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
      list($current_page, $error_messages) = $current_page->revise($this->page_params());

      if ($error_messages) {
        $this->redirect("/dashboard/pages/{$current_page->id}/edit", ['errors' => $error_messages]);
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
    list($page, $error_messages) = $page->publish($this->page_params());

    if ($error_messages) {
      $this->redirect('/dashboard/pages/new', ['errors' => $error_messages]);
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
