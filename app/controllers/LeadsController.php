<?php

namespace App\Controllers;

use App\Controllers\ApplicationController;
use App\Models\Lead;
use DateTime;

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
    $error_messages = [];

    list($saved_files, $file_error_messages) = $this->save_files($this->files_params());
    $error_messages = array_merge($error_messages, $file_error_messages);

    list($leads, $lead_error_messages) = (new Lead)->get_leads_from_files($saved_files);
    $error_messages = array_merge($error_messages, $lead_error_messages);


    $this->redirect('/dashboard/leads', ['errors' => $error_messages]);
  }

  private function files_params()
  {
    return $this->params_permit(['files'], $_FILES);
  }


  private function save_files(array $files_params): array
  {
    $files = $files_params['files'] ?? [];

    // check directory existence
    $directory = self::$ROOT_DIR . self::STORAGE_DIR . "\\leads\\";
    if (!is_dir($directory)) {
      $dir_permissions = 0755;
      $recursive = true;
      mkdir($directory, $dir_permissions, $recursive);
    }


    $saved_files = [];
    $error_messages = [];
    foreach ($files['tmp_name'] as $key => $temp_name) {
      $file_name = basename($files['name'][$key]);
      $file_type = mime_content_type($temp_name);

      // only allow csv
      if ($file_type !== "text/csv") {
        $error_messages[] = "Invalid file type: $file_name";
        continue;
      }

      // read and process file content (replace carriage returns with semicolons)
      $file_content = file_get_contents($temp_name);
      $file_content = str_replace("\r", ";", $file_content);

      // save processed content to new file
      $date = new DateTime(); // system's default timezone
      $formatted_date = $date->format('Y-m-d_Hisu');
      $file_destination = $directory . $formatted_date . "_leads-processed.csv";

      // write content to file
      if (!(file_put_contents($file_destination, $file_content))) {
        $error_messages[] = "Failed to save processed file: $file_name";
        continue;
      }

      $saved_files[] = $file_destination;
    }

    return [$saved_files, $error_messages];
  }
}
