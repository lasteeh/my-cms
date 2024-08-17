<?php

namespace App\Controllers;

use App\Controllers\ApplicationController;
use App\Models\City;
use App\Models\County;
use App\Models\Lead;

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
    $error_messages = [];
    $alert_messages = [];

    $city = new City;
    list($city, $errors) = $city->add($this->city_params());
    $error_messages = array_merge($error_messages, $errors);

    if (empty($error_messages)) {
      $alert_messages[] = "City added";
    }

    // assign leads automatically if from other pages
    if (!empty($_POST['origin_url'])) {
      list($assigned_leads, $errors) = (new Lead)->assign_leads();
      $error_messages = array_merge($error_messages, $errors);

      if (is_array($assigned_leads)) {
        $assigned_leads_count = count($assigned_leads);
        $alert_messages[] = "{$assigned_leads_count} leads assigned.";
      }
    }

    $origin_url = $_POST['origin_url'] ?? '/dashboard/cities';
    $this->redirect("{$origin_url}", ['errors' => $error_messages, 'alerts' => $alert_messages]);
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
    $alert_messages = [];
    $error_messages = [];

    $current_city = $this->set_current_city();

    if (!$current_city) {
      $this->render('not_found', 'application');
    }

    list($current_city, $errors) = $current_city->update($this->city_params());
    $error_messages = array_merge($error_messages, $errors);

    if (empty($error_messages)) {
      $alert_messages[] = "City updated";
    }

    $this->redirect("/dashboard/cities/{$current_city->id}/edit", ['errors' => $error_messages, 'alerts' => $alert_messages]);
  }

  public function delete()
  {
    $current_city = $this->set_current_city();

    if ($current_city) {
      list($current_city, $error_messages) = $current_city->delete();

      if ($error_messages) {
        $this->redirect("/dashboard/cities/{$current_city->id}/edit", ['errors' => $error_messages]);
      } else {
        $alerts[] = "City deleted.";
        $this->redirect('/dashboard/cities', ['alerts' => $alerts]);
      }
    }
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
