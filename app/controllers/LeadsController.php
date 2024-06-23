<?php

namespace App\Controllers;

use App\Controllers\ApplicationController;
use App\Models\Lead;
use DateTime;

class LeadsController extends ApplicationController
{
  const LEADS_PER_PAGE = 100;

  public function index()
  {
    $leads_per_index_page = isset($_GET['leads_per_page']) && is_int((int)($_GET['leads_per_page'])) ? $_GET['leads_per_page'] : self::LEADS_PER_PAGE; // customize page's maximum lead count
    $default_sort_by = 'id';
    $params = $this->files_params();

    // determin current page number
    $current_page = (int)($_GET['page'] ?? 1);

    // determine sort params
    $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : $default_sort_by;
    $sort_order = (isset($_GET['sort_order']) && (strtolower($_GET['sort_order']) === 'asc')) ? 'asc' : 'desc';

    // fetch leads for current page
    list($leads, $total_pages) = (new Lead)->paginate($current_page, $leads_per_index_page, $sort_order, $sort_by, $params['permitted_fields']);

    $this->set_object('leads', $leads);
    $this->set_object('current_page', $current_page);
    $this->set_object('total_pages', $total_pages);
    $this->set_object('sort_order', $sort_order);
    $this->set_object('sort_by', $sort_by);
    $this->render();
  }

  public function batch_add()
  {
    $error_messages = [];
    $alert_messages = [];
    $files_params = $this->files_params();

    // process and save files to app storage
    list($saved_files, $file_error_messages) = $this->save_files($files_params);
    $error_messages = array_merge($error_messages, $file_error_messages);

    // grab leads from saved files in storage
    list($leads, $lead_error_messages) = (new Lead)->get_leads_from_files($saved_files, $files_params['permitted_fields']);
    $error_messages = array_merge($error_messages, $lead_error_messages);

    if (is_array($leads)) {
      $lead_count = count($leads);
      $alert_messages = ["{$lead_count} new leads found."];
    }

    $this->redirect('/dashboard/leads', ['errors' => $error_messages, 'alerts' => $alert_messages]);
  }

  private function files_params()
  {
    $permitted_fields = [
      'Vortex ID', 'Listing Status', 'Name', 'Name 2', 'Name 3', 'Name 4', 'Name 5', 'Name 6', 'Name 7', 'MLS Name', 'MLS Name 2', 'MLS Name 3', 'MLS Name 4', 'MLS Name 5', 'MLS Name 6', 'MLS Name 7', 'Phone', 'Phone 2', 'Phone 3', 'Phone 4', 'Phone 5', 'Phone 6', 'Phone 7', 'Phone Status', 'Phone 2 Status', 'Phone 3 Status', 'Phone 4 Status', 'Phone 5 Status', 'Phone 6 Status', 'Phone 7 Status', 'Email', 'Email 2', 'Email 3', 'Email 4', 'Email 5', 'Email 6', 'Email 7', 'Address', 'Address 2', 'Address 3', 'Address 4', 'Address 5', 'Address 6', 'Address 7', 'First Name', 'Last Name', 'Mailing Street', 'Mailing City', 'Mailing State', 'Mailing Zip', 'List Date', 'List Price', 'Days On Market', 'Lead Date', 'Expired Date', 'Withdrawn Date', 'Status Date', 'Listing Agent', 'Listing Broker', 'MLS/FSBO ID', 'Property Address', 'Property City', 'Property State', 'Property Zip',
    ];
    $required_fields = [
      'Vortex ID', 'Listing Status', 'Name', 'Phone', 'Email', 'Mailing Street', 'Mailing City', 'Mailing State', 'Mailing Zip',  'List Price', 'Status Date', 'Property Address', 'Property City', 'Property State', 'Property Zip',
    ];
    $files = $this->params_permit(['leads'], $_FILES);

    return ['files' => $files, 'permitted_fields' => $permitted_fields, 'required_fields' => $required_fields];
  }


  private function save_files(array $files_params): array
  {
    $files = $files_params['files']['leads'] ?? [];
    $permitted_fields = $files_params['permitted_fields'] ?? [];
    $required_fields = $files_params['required_fields'] ?? [];

    // check directory existence
    $directory = self::$ROOT_DIR . self::STORAGE_DIR . "\\leads\\processed\\";
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

      // get header line
      $lines = explode("\n", $file_content);
      $header = str_getcsv($lines[0]);

      // check if all required fields are in the header
      $missing_fields = array_diff($required_fields, $header);
      if (!empty($missing_fields)) {
        $error_messages[] = "Missing required fields in $file_name: " . implode(", ", $missing_fields);
        continue;
      }

      // save processed content to new file
      $date = new DateTime(); // system's default timezone
      $formatted_date = $date->format('Y-m-d_Hisu');
      $trimmed_file_name = str_replace(".csv", "", $file_name);
      $file_destination = $directory . $formatted_date . "_leads-processed(" . $trimmed_file_name . ").csv";

      // write content to file
      if (!(file_put_contents($file_destination, $file_content))) {
        $error_messages[] = "Failed to save raw file: $file_name";
        continue;
      }

      $saved_files[] = $file_destination;
    }

    return [$saved_files, $error_messages];
  }
}
