<?php

namespace App\Controllers;

use App\Controllers\ApplicationController;
use App\Models\City;
use App\Models\County;

class CitiesController extends ApplicationController
{
  public function index()
  {
    list($cities, $total_pages) = (new City)->paginate(1, 500, 'asc', 'name');
    $counties = (new County)->all();

    $county_map = [];
    foreach ($counties as $county) {
      $county_map[$county['id']] = $county['name'];
    }

    $cities_with_county_names = [];
    foreach ($cities as $city) {
      $city['county_name'] = $county_map[$city['county_id']] ?? '';
      $cities_with_county_names[] = $city;
    }

    $this->set_object('cities', $cities_with_county_names);
    $this->set_object('counties', $counties);
    $this->render();
  }

  public function create()
  {
    $city = new City;
    list($city, $error_messages) = $city->add($this->city_params());

    $this->redirect('/dashboard/cities', ['errors' => $error_messages]);
  }

  public function edit()
  {
    $current_city = $this->set_current_city();
    $counties = (new County)->all();

    if (!$current_city) {
      $this->render('not_found', 'application');
    }

    $this->set_object('current_city', $current_city);
    $this->set_object('counties', $counties);
    $this->render();
  }

  public function update()
  {
    $current_city = $this->set_current_city();

    if (!$current_city) {
      $this->render('not_found', 'application');
    }

    list($current_city, $error_messages) = $current_city->update($this->city_params());

    $this->redirect("/dashboard/cities/{$current_city->id}/edit", ['errors' => $error_messages]);
  }

  private function set_current_city()
  {
    return (new City)->find_by(['id' => $this->get_route_param('id')]);
  }

  private function city_params(): array
  {
    return $this->params_permit(['name', 'state', 'zip_codes', 'county_id', 'latitude', 'longitude', 'bound_nw', 'bound_se', 'viewport_nw', 'viewport_se'], $_POST);
  }
}
