<?php

namespace App\Models;

use App\Models\Application_Record;

class Page extends Application_Record
{
  public string $slug;
  public string $title;
  public string $sub_title;
  public string $description;
  public string $content;
  public string $created_at;
  public string $updated_at;
  public $parent_id;

  protected static $before_validate = [
    'nullify_empty_parent_id',
  ];
  protected static $validate = [
    'prevent_parent_self_reference',
    'prevent_slug_special_chars',
  ];

  protected $validations = [
    'title' => [
      'presence' => true,
      'uniqueness' => true,
    ],
    'slug' => [
      'presence' => true,
      'uniqueness' => true,
    ],
  ];

  protected static $after_validate = [
    'normalize_slug',
  ];

  public function publish(array $page_params): array
  {
    $this->new($page_params);
    $this->save();

    return [$this, $this->ERRORS];
  }

  public function revise(array $page_params): array
  {
    $this->update_attributes($page_params);
    $this->save();

    return [$this, $this->ERRORS];
  }

  public function fetch_all_pages_for_index(): array
  {
    return $this->fetch_by([], ['id', 'title', 'slug', 'created_at']);
  }

  public function fetch_all_pages_for_edit(): array
  {
    return $this->fetch_by([], ['id', 'title']);
  }

  protected function normalize_slug()
  {
    $slug = $this->slug;

    if (empty($slug)) {
      $slug = $this->title;
    }

    $slug = strtolower($slug);
    $slug = iconv('utf-8', 'ascii//TRANSLIT', $slug);
    $slug = preg_replace('/[^a-zA-Z0-9_-]/', '-', $slug);
    $slug = trim($slug, '-');

    $this->update_attribute('slug', $slug);
  }

  protected function prevent_parent_self_reference()
  {
    if ($this->id === (int)$this->parent_id) {
      $this->add_error("Parent cannot be itself");
    }
  }

  protected function prevent_slug_special_chars()
  {
    $invalid_chars = preg_match('/[^a-zA-Z0-9_-]/', $this->slug);

    if ($invalid_chars) {
      $this->add_error("Slug can only contain letters, numbers, underscores, and hyphens.");
    }
  }

  protected function nullify_empty_parent_id()
  {
    if ($this->parent_id === '') {
      $this->update_attribute('parent_id', null);
    }
  }
}
