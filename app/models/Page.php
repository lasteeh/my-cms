<?php

namespace App\Models;

use App\Models\Application_Record;

class Page extends Application_Record
{
  public string $slug;
  public string $title;
  public ?string $sub_title;
  public ?string $description;
  public ?string $custom_css;
  public ?string $custom_js;
  public ?string $content;
  public string $created_at;
  public string $updated_at;
  public $parent_id;

  protected static $before_validate = [
    'nullify_empty_parent_id',
    'normalize_slug',
  ];
  protected static $validate = [
    'validate_parent_id',
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

  protected static $before_destroy = [
    'nullify_descendant_parent_id',
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

  public function trash(): array
  {
    $this->destroy();

    return [$this, $this->ERRORS];
  }

  public function show_page(array $uri_params): ?Page
  {
    // reverse the URI parameters to start from the last part
    $uri_params = array_reverse($uri_params);

    if (empty($uri_params)) {
      return null;
    }

    $page_to_show = $this->find_by(['slug' => $uri_params[0]]);

    if ($page_to_show === null) {
      return null;
    }

    if (count($uri_params) > 1) {
      for ($index = 0; $index < count($uri_params); $index++) {
        $current_page = (new Page)->find_by(['slug' => $uri_params[$index]]);

        if ($current_page === null) {
          return null;
        }

        if ($index !== (count($uri_params) - 1)) {
          if ($current_page === null || $current_page->parent_id === null) {
            return null;
          }

          $parent_page = (new Page)->find_by(['slug' => $uri_params[$index + 1]]);

          if ($parent_page === null || (string)$parent_page->id !== (string)$current_page->parent_id) {
            return null;
          }
        } else {
          if ($current_page->parent_id !== null) {
            return null;
          }
        }
      }
    } else {
      if ($page_to_show->parent_id !== null) {
        return null;
      }
    }

    // return the page to show if all checks passed
    return $page_to_show;
  }

  public function fetch_all_pages_for_index(): array
  {
    return $this->fetch_by([], ['id', 'title', 'slug', 'parent_id', 'created_at']);
  }

  public function fetch_all_pages_for_new(): array
  {
    return $this->fetch_by([], ['id', 'title']);
  }
  public function fetch_all_pages_for_edit(): array
  {
    $pages = $this->fetch_by([], ['id', 'title', 'parent_id']);
    $filtered_pages = [];

    // recursive function to find all descendants of a page
    $find_descendants = function ($id, &$descendants) use ($pages, &$find_descendants) {
      foreach ($pages as $page) {
        if ($page['parent_id'] == $id) {
          $descendants[] = $page['id'];
          $find_descendants($page['id'], $descendants);
        }
      }
    };

    // find all descendants of the current page
    $descendants = [$this->id];
    $find_descendants($this->id, $descendants);

    // filter out pages that are descendants of the current page
    foreach ($pages as $page) {
      if (!in_array($page['id'], $descendants)) {
        $filtered_pages[] = $page;
      }
    }

    return $filtered_pages;
  }


  protected function normalize_slug()
  {
    $slug = $this->slug;

    if (!$this->is_an_existing_record()) {
      if (empty($slug)) {
        $slug = $this->title;
      }
    }

    $slug = strtolower($slug);
    $slug = iconv('utf-8', 'ascii//TRANSLIT', $slug);
    $slug = preg_replace('/[^a-zA-Z0-9_-]/', '-', $slug);
    $slug = trim($slug, '-');

    $this->update_attribute('slug', $slug);
  }

  protected function validate_parent_id()
  {
    // check if no parent_id is set (no validation required)
    if (empty($this->parent_id)) {
      return;
    }

    // check if new record (no validation required)
    if (empty($this->id)) {
      return;
    }

    // check if parent_id is set and not itself
    if ((string)$this->id === (string)$this->parent_id) {
      $this->add_error("Parent cannot be itself");
    }

    // fetch all pages
    $pages = $this->fetch_by([], ['id', 'title', 'parent_id']);

    // recursive function to find all descendants of a page
    $find_descendants = function ($id, &$descendants) use ($pages, &$find_descendants) {
      foreach ($pages as $page) {
        if ($page['parent_id'] == $id) {
          $descendants[] = $page['id'];
          $find_descendants($page['id'], $descendants);
        }
      }
    };

    // find all descendants of the current page
    $descendants = [];
    $find_descendants($this->id, $descendants);

    // check if the parent_id is the current page or one of its descendants
    if (in_array($this->parent_id, $descendants)) {
      $this->add_error("Parent cannot be a descendant of this page.");
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

  protected function nullify_descendant_parent_id()
  {
    $descendants = $this->fetch_by(['parent_id' => $this->id], ['id']);
    foreach ($descendants as $descendant) {
      $page = (new Page)->find_by(['id' => $descendant['id']]);

      $page->update_column('parent_id', null);
    }
  }
}
