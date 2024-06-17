<?php

namespace App\Controllers;

use App\Controllers\ApplicationController;
use App\Models\Lead;

class LeadsController extends ApplicationController
{

  public function index()
  {
    $leads = (new Lead)->all();

    $this->set_object('leads', $leads);
    $this->render();
  }

  public function batch_add()
  {
    $lead = new Lead;
    $leads_params = $this->leads_params();
    // list($lead, $error_messages) = $lead->batch_add($this->leads_params());
  }

  public function leads_params()
  {
    if (!isset($_FILES['leads']) || $_FILES['leads']['error'] !== 0) {
      $this->add_error("File Error: {$_FILES['leads']['error']}");
    }
    // WIP: implement batch add


    var_dump($_FILES);
    // array(1) { ["leads"]=> array(6) { ["name"]=> string(10) "cities.csv" ["full_path"]=> string(10) "cities.csv" ["type"]=> string(24) "application/vnd.ms-excel" ["tmp_name"]=> string(24) "C:\xampp\tmp\php62D0.tmp" ["error"]=> int(0) ["size"]=> int(25896) } } 
  }
}
