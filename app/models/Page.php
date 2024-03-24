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

  protected static $before_validate = [
    'normalize_slug',
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
}
