<?php

namespace App\Models;

use App\Models\Application_Record;

class City extends Application_Record
{
  public string $name;
  public ?string $state;
  public ?string $zip_codes;
  public ?int $county_id;
  public ?string $latitude;
  public ?string $longitude;
  public ?string $bound_nw;
  public ?string $bound_se;
  public ?string $viewport_nw;
  public ?string $viewport_se;
  public string $created_at;
  public string $updated_at;

  protected static $before_validate = [
    'normalize_zip_codes',
    'normalize_coordinates',
  ];

  protected static $validate = [
    'prevent_zip_codes_special_chars',
    'prevent_coordinates_special_chars',
  ];

  protected $validations = [
    'name' => [
      'presence' => true,
      'uniqueness' => true,
    ],
  ];

  public function fetch_cities()
  {
    return $this->all();
  }

  public function add(array $city_params): array
  {
    $this->new($city_params);
    $this->save();

    return [$this, $this->ERRORS];
  }

  public function update(array $city_params): array
  {
    $this->update_attributes($city_params);
    $this->save();

    return [$this, $this->ERRORS];
  }

  protected function normalize_zip_codes()
  {
    $zip_codes = explode(",", $this->zip_codes);
    $normalized_zip_codes = [];

    foreach ($zip_codes as $zip) {
      if (strpos($zip, '-') === false) {
        $normalized_zip_codes[] = trim($zip);
        continue;
      }

      list($start, $end) = explode("-", $zip);
      $start = trim($start);
      $end = trim($end);

      if (strlen($start) !== strlen($end)) continue;

      for ($i = (int)$start; $i <= (int)$end; $i++) {
        $normalized_zip_codes[] = $i;
      }
    }

    $normalized_zip_codes = array_unique($normalized_zip_codes);
    sort($normalized_zip_codes);

    $normalized_zip_codes = implode(",", $normalized_zip_codes);

    $this->update_attribute('zip_codes', $normalized_zip_codes);
  }

  protected function normalize_coordinates()
  {
    $coordinate_entries = [
      'latitude' => $this->latitude,
      'longitude' => $this->longitude,
      'bound_nw' => $this->bound_nw,
      'bound_se' => $this->bound_se,
      'viewport_nw' => $this->viewport_nw,
      'viewport_se' => $this->viewport_se
    ];

    foreach ($coordinate_entries as $key => $coordinate) {
      if (!is_string($coordinate)) continue;

      $string = $coordinate;
      $string = str_replace(" ", "", $string);
      $string = trim($string);

      $this->update_attribute($key, $string);
    }
  }

  protected function prevent_zip_codes_special_chars()
  {
    $invalid_chars = preg_match('/[^0-9,\-]/', $this->zip_codes);

    if ($invalid_chars) {
      $this->add_error("Zip Codes can only contain numbers, commas, and hyphens.");
    }
  }
  protected function prevent_coordinates_special_chars()
  {
    $coordinate_entries = [
      'Latitude' => $this->latitude,
      'Longitude' => $this->longitude,
      'Bound NW' => $this->bound_nw,
      'Bound SE' => $this->bound_se,
      'Viewport NW' => $this->viewport_nw,
      'Viewport SE' => $this->viewport_se
    ];

    foreach ($coordinate_entries as $key => $coordinate) {
      if (!is_string($coordinate)) continue;

      $invalid_chars = preg_match('/[^0-9,.\-]/', $coordinate);

      if ($invalid_chars) {
        $this->add_error("{$key} can only contain numbers, commas, periods, and hyphens.");
      }
    }
  }
}
