<?php

namespace App\Models;

use App\Models\Application_Record;

class Post extends Application_Record
{
  public string $slug;
  public string $title;
  public ?string $sub_title;
  public ?string $description;
  public ?string $excerpt;
  public ?string $custom_css;
  public ?string $custom_js;
  public ?string $content;
  public string $created_at;
  public string $updated_at;

  protected static $before_validate = [
    'normalize_slug',
  ];

  protected static $validate = [
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

  public function publish(array $post_params): array
  {
    $this->new($post_params);
    $this->save();

    return [$this, $this->ERRORS];
  }

  public function revise(array $post_params): array
  {
    $this->update_attributes($post_params);
    $this->save();

    return [$this, $this->ERRORS];
  }

  public function trash(): array
  {
    $this->destroy();

    return [$this, $this->ERRORS];
  }

  public function fetch_posts_for_index()
  {
    return $this->fetch_by([], ['id', 'title', 'slug', 'created_at']);
  }

  protected function prevent_slug_special_chars()
  {
    $invalid_chars = preg_match('/[^a-zA-Z0-9_-]/', $this->slug);

    if ($invalid_chars) {
      $this->add_error("Slug can only contain letters, numbers, underscores, and hyphens.");
    }
  }

  protected function normalize_slug()
  {
    $slug = $this->slug;

    if (!$this->is_an_existing_record() && empty($slug)) {
      $slug = $this->title;
    }

    $slug = strtolower($slug);
    $slug = iconv('utf-8', 'ascii//TRANSLIT', $slug);
    $slug = preg_replace('/[^a-zA-Z0-9_-]/', '-', $slug);
    $slug = trim($slug, '-');

    $this->update_attribute('slug', $slug);
  }
}
