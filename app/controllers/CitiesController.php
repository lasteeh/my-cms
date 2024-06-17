<?php

namespace App\Controllers;

use App\Controllers\ApplicationController;
use App\Models\City;
use App\Models\County;

class CitiesController extends ApplicationController
{
  public function index()
  {
    $cities = (new City)->fetch_cities();
    $counties = (new County)->all();

    $county_map = [];
    foreach ($counties as $county) {
      $county_map[$county['id']] = $county['name'];
    }

    foreach ($cities as &$city) {
      $city['county_name'] = $county_map[$city['county_id']] ?? '';
    }

    $this->set_object('cities', $cities);
    $this->render();
  }
}
