<?php

namespace App\Controllers;

use App\Controllers\ApplicationController;
use App\Models\Post;

class PostsController extends ApplicationController
{
  protected static $skip_before_action = [
    'authenticate_request' => [
      'only' => ['show'],
    ],
  ];

  public function index()
  {
    $posts = (new Post())->fetch_posts_for_index();

    $this->set_object('posts', $posts);
    $this->render();
  }

  public function show()
  {
    $route_param = $this->get_route_param('title');
    $current_post = (new Post)->find_by(['title' => $route_param]);

    $this->set_layout('page');
    if ($current_post) {
      $this->set_page_info(['title' => $current_post->title]);
      $this->set_inline_style($current_post->custom_css);
      $this->set_inline_script($current_post->custom_js);
      $this->set_object('current_post', $current_post);
      $this->render();
    } else {
      $this->render('not_found', 'application');
    }
  }

  public function new()
  {
    $this->render();
  }

  public function create()
  {
    $post = new Post;
    list($post, $error_messages) = $post->publish($this->post_params());

    if ($error_messages) {
      $this->redirect('/dashboard/posts/new', ['errors' => $error_messages]);
    } else {
      $this->redirect('/dashboard/posts');
    }
  }

  public function edit()
  {
    $current_post = $this->set_current_post();

    if ($current_post) {
      $this->set_object('current_post', $current_post);
      $this->render();
    } else {
      $this->render('not_found', 'application');
    }
  }

  public function update()
  {
    $current_post = $this->set_current_post();


    if ($current_post) {
      list($current_post, $error_messages) = $current_post->revise($this->post_params());

      if ($error_messages) {
        $this->redirect("/dashboard/posts/{$current_post->id}/edit", ['errors' => $error_messages]);
      } else {
        $this->redirect('/dashboard/posts');
      }
    } else {
      $this->render('not_found', 'application');
    }
  }

  public function delete()
  {
    $current_post = $this->set_current_post();

    if ($current_post) {
      list($current_post, $error_messages) = $current_post->trash();

      if ($error_messages) {
        $this->redirect("/dashboard/posts/{$current_post->id}/edit", ['errors' => $error_messages]);
      } else {
        $this->redirect('/dashboard/posts');
      }
    }
  }

  protected function set_current_post()
  {
    return (new Post)->find_by(['id' => $this->get_route_param('id')]);
  }

  private function post_params(): array
  {
    return $this->params_permit(['slug', 'title', 'sub_title', 'description', 'excerpt', 'content', 'custom_css', 'custom_js'], $_POST);
  }
}
