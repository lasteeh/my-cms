<?php

namespace App\Models;

use App\Models\Application_Record;

class County extends Application_Record
{
  public string $name;
  public string $created_at;
  public string $updated_at;
}
