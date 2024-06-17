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

  public function fetch_cities()
  {
    return $this->all();
  }
}
