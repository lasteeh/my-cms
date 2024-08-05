<?php

namespace App\Controllers;

use App\Controllers\ApplicationController;
use App\Models\County;
use App\Models\Lead;
use DateTime;

class LeadsController extends ApplicationController
{
  public function index()
  {
    $category = $this->get_route_param('category') ?? '';
    $category_listing_statuses = config('mrcleads.categories');
    $area = $this->get_route_param('area') ?? '';

    $leads_per_page = $_GET['leads_per_page'] ?? 100;
    $sort_by = $_GET['sort_by'] ?? 'id';
    $sort_order = $_GET['sort_order'] ?? 'desc';
    $current_page = $_GET['page'] ?? 1;
    $total_pages = $_GET['total_pages'] ?? 1;
    $filter_by = [];


    switch ($category) {
      case 'unassigned':
        $page_title = "Unassigned Leads";
        $filter_by['lead_assigned'] = false;
        break;
      case 'absentee_owner':
        $page_title = "Absentee Owners";
        $filter_by['absentee_owner'] = true;
        $filter_by['listing_status'] = $category_listing_statuses['expired'];
        break;
      case 'expired':
        $page_title = "Expireds";
        $filter_by['absentee_owner'] = false;
        $filter_by['listing_status'] = $category_listing_statuses['expired'];
        break;
      case 'frbo':
        $page_title = "FRBO";
        $filter_by['listing_status'] = $category_listing_statuses['frbo'];
        break;
      case 'fsbo':
        $page_title = "FSBO";
        $filter_by['listing_status'] = $category_listing_statuses['fsbo'];
        break;
      default:
        $page_title = "Leads";
        $filter_by = $_GET['filter_by'] ?? [];
        break;
    }

    switch ($area) {
      case 'montgomery':
        $filter_by['assigned_area'] = 'montgomery';
        break;
      case 'auburn':
        $filter_by['assigned_area'] = 'auburn';
        break;
      default:
        break;
    }

    $leads = [];
    $search_params = [
      'leads_per_page' => (int)$leads_per_page,
      'sort_by' => $sort_by,
      'sort_order' => $sort_order,
      'page' => (int)$current_page,
      'total_pages' => (int)$total_pages,
      'filter_by' => $filter_by,
    ];

    list($leads, $total_pages) = (new Lead)->paginate_leads($current_page, $leads_per_page, $sort_order, $sort_by, $filter_by);
    $search_params['total_pages'] = (int)$total_pages;

    $this->set_page_info(['title' => $page_title]);
    $this->set_object('leads', $leads);
    $this->set_object('search_params', $search_params);
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

      if ($lead_count > 0) {
        $alert_messages[] = "{$lead_count} new leads found.";
      } else {
        $error_messages[] = "No new leads found.";
      }
    }

    $redirect_link = $_POST['origin_url'] ?? '/dashboard/leads';
    $this->redirect($redirect_link, ['errors' => $error_messages, 'alerts' => $alert_messages]);
  }

  public function assign()
  {
    $error_messages = [];
    $alert_messages = [];

    list($assigned_leads, $errors) = (new Lead)->assign_leads();
    $error_messages = array_merge($error_messages, $errors);

    if (is_array($assigned_leads)) {
      $assigned_leads_count = count($assigned_leads);
      $alert_messages[] = "{$assigned_leads_count} leads assigned.";
    }

    $redirect_link = $_POST['origin_url'] ?? "/dashboard/leads/unassigned";
    $this->redirect($redirect_link, ['errors' => $error_messages, 'alerts' => $alert_messages]);
  }

  public function toggle()
  {
    $property = $this->get_route_param('property');

    $error_messages = [];
    $lead = $this->set_current_lead();

    if (!$lead) {
      $error_messages[] = "Lead not found.";
    } else {
      switch ($property) {
        case 'absentee_owner':
          $absentee_owner = $lead->absentee_owner ?? true;
          $lead->update_column('absentee_owner', !$absentee_owner);
          break;

        case 'import_lead':
          $import_lead = $lead->import_lead ?? true;
          $lead->update_column('import_lead', !$import_lead);
          break;
      }
    }

    $row = $_POST['row'] ?? '';
    $origin_url = $_POST['origin_url'] ?? '/dashboard/leads';

    $this->redirect("{$origin_url}#{$row}", ['errors' => $error_messages]);
  }

  public function export()
  {
    $error_messages = [];
    $alert_messages = [];

    $origin_url = $_POST['origin_url'] ?? '/dashboard/leads';
    $area = $this->get_route_param('area') ?? "";
    $category = $this->get_route_param('category') ?? "";

    list($exported_data, $errors) = (new Lead)->export_leads($area, $category);
    $error_messages = array_merge($error_messages, $errors);

    if (!empty($exported_data)) {
      if (isset($exported_data['leads'])) {
        $exported_data_count = count($exported_data['leads']);
        $alert_messages[] = "{$exported_data_count} leads exported.";
      }
    }

    $this->redirect($origin_url, ['errors' => $error_messages, 'alerts' => $alert_messages]);
  }

  private function list(array $options = [], array $filters = [])
  {
    $view = "index";
    $page_title = $options['title'] ?? 'Leads';
    $lead_category = $options['lead_category'] ?? "";
    $lead_area = $options['lead_area'] ?? "";

    if ($lead_category === "unassigned") {
      $counties = (new County)->all();
      $this->set_object('counties', $counties);
    }

    // default search params
    $default = [
      'leads_per_page' => 100,
      'sort_by' => 'id',
      'sort_order' => 'desc',
      'current_page' => 1,
      'total_page' => 1,
      'filter_by' => $filters,
    ];

    // determine lead count to show
    $leads_per_page = isset($_GET['leads_per_page'])
      && is_int((int)($_GET['leads_per_page']))
      ? $_GET['leads_per_page'] : $default['leads_per_page'];

    // determine current page number
    $current_page = (int)($_GET['page'] ?? $default['current_page']);

    // determine sort params
    $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : $default['sort_by'];
    $sort_order = (isset($_GET['sort_order']) && (strtolower($_GET['sort_order']) === 'asc')) ? 'asc' : 'desc';

    // determine filter params
    $filter_by = isset($_GET['filter_by']) ? $_GET['filter_by'] : $default['filter_by'];

    // fetch leads for current page
    list($leads, $total_pages) = (new Lead)->paginate_leads($current_page, $leads_per_page, $sort_order, $sort_by, $filter_by);

    if ($current_page > (int)$total_pages) {
      $current_page = (int)$total_pages;
    }

    $search_params = [
      'leads_per_page' => $leads_per_page,
      'sort_by' => $sort_by,
      'sort_order' => $sort_order,
      'page' => $current_page,
      'total_pages' => $total_pages,
      'filter_by' => $filter_by,
    ];


    $this->set_page_info(['title' => $page_title]);
    $this->set_object('leads', $leads);
    $this->set_object('lead_category', $lead_category);
    $this->set_object('lead_area', $lead_area);
    $this->set_object('search_params', $search_params);
    $this->render($view);
  }

  private function save_files(array $files_params): array
  {
    $files = $files_params['files']['leads'] ?? [];
    $permitted_fields = $files_params['permitted_fields'] ?? [];
    $required_fields = $files_params['required_fields'] ?? [];

    // check directory existence
    $directory = self::$ROOT_DIR . self::STORAGE_DIR . "\\leads\\source\\";
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
      $file_destination = $directory . $formatted_date . "_leads-source (" . $trimmed_file_name . ").csv";

      // write content to file
      if (!(file_put_contents($file_destination, $file_content))) {
        $error_messages[] = "Failed to save source file: $file_name";
        continue;
      }

      $saved_files[] = $file_destination;
    }

    return [$saved_files, $error_messages];
  }

  private function set_current_lead()
  {
    return (new Lead)->find_by(['id' => $this->get_route_param('id')]);
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
}
