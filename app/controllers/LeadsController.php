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
    $range = [];

    $leads = [
      'items' => [],
      'config' => [],
    ];


    switch ($category) {
      case 'unassigned':
        $page_title = "Unassigned Leads";
        $filter_by['lead_assigned'] = ['0'];
        break;
      case 'absentee_owner':
        $page_title = "Absentee Owners";
        $filter_by['absentee_owner'] = ['1'];
        $filter_by['listing_status'] = $category_listing_statuses['expired'];
        break;
      case 'expired':
        $page_title = "Expireds";
        $filter_by['absentee_owner'] = ['0'];
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
      case 'montgomery':
        $page_title = "Leads (Montgomery)";
        $area = 'montgomery';
        break;
      case 'auburn':
        $page_title = "Leads (Auburn)";
        $area = 'auburn';
        break;
      default:
        $page_title = "Leads";
        $filter_by = $_GET['filter_by'] ?? [];
        $range = $_GET['range'] ?? [];
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

    // only fetch todays leads
    switch ($category) {
      case 'absentee_owner':
      case 'expired':
      case 'frbo':
      case 'fsbo':
        switch ($area) {
          case 'montgomery':
          case 'auburn':
            $category_title = str_replace("_", " ", $category);
            if ($category === 'frbo' || $category === 'fsbo') {
              $formatted_category_title = strtoupper($category_title);
            } else {
              $formatted_category_title = ucwords($category_title);
            }

            $area_title = ucwords($area);

            $page_title = "{$formatted_category_title} ($area_title)";

            $date_today = new DateTime();
            $formatted_date_today = $date_today->format('Y-m-d');

            $range['created_at'][] = $formatted_date_today;
            $range['created_at'][] = $formatted_date_today;

            $filter_by['import_lead'] = ['1'];
            break;
        }
        break;
    }


    $search_params = [
      'leads_per_page' => (int)$leads_per_page,
      'sort_by' => $sort_by,
      'sort_order' => $sort_order,
      'page' => (int)$current_page,
      'total_pages' => (int)$total_pages,
      'filter_by' => $filter_by,
      'range' => $range,
    ];

    $processed_range = [];
    if (isset($range['created_at']) && is_array($range['created_at'])) {
      foreach ($range['created_at'] as $date) {
        if (empty($date)) continue;

        $processed_date = $date . " 00:00:00";
        $processed_range['created_at'][] = $processed_date;
      }
    }

    $all_leads = [];
    list($all_leads, $total_pages) = (new Lead)->paginate_leads($current_page, $leads_per_page, $sort_order, $sort_by, $filter_by, $processed_range);
    $search_params['total_pages'] = (int)$total_pages;

    $pagination = [];
    $pagination = $this->get_pages($search_params);

    $leads['items'] = $all_leads;
    $leads['config']['pagination'] = $pagination;

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
          $absentee_owner = $lead->absentee_owner ?? false;
          $listing_status = $lead->listing_status ?? '';

          if ($listing_status === "FRBO" || $listing_status === "FSBO") {
            $source = "REDX";
          } else {
            $source = $lead->absentee_owner ? "REDX" : "Absentee Owner";
          }

          $lead->update_column('absentee_owner', !$absentee_owner);
          $lead->update_column('source', $source);
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

  private function get_pages(array $search_params = [], int $maximum_page_links = 10): array
  {
    $links = [];

    $current_page = $search_params['page'] ?? 1;
    $midpoint = (int) floor($maximum_page_links / 2);
    $start = max(1, $current_page - $midpoint);
    $end = min($search_params['total_pages'], $start + $maximum_page_links - 1);

    $start = max(1, $end - $maximum_page_links + 1);

    // previous page link
    if ($current_page  > 1) {
      $search_params['page'] = $current_page - 1;
      $pagination_params = http_build_query($search_params);
      $links[] = ['label' => '&laquo; Previous', 'href' => "?{$pagination_params}"];
    }

    // first page link and ellipsis if needed
    if ($start > 1) {
      $search_params['page'] = 1;
      $pagination_params = http_build_query($search_params);
      $links[] = ['label' => '1', 'href' => "?{$pagination_params}"];
      $links[] = ['label' => '...', 'href' => '#'];
    }

    // page number links
    for ($i = $start; $i <= $end; $i++) {
      $search_params['page'] = $i;
      $pagination_params = http_build_query($search_params);

      if ($i == $current_page) {
        $links[] = ['label' => (string)$i, 'href' => '#', 'current' => true];
      } else {
        $links[] = ['label' => (string)$i, 'href' => "?$pagination_params"];
      }
    }

    // last page link and ellipsis if needed
    if ($end < $search_params['total_pages']) {
      $search_params['page'] = $search_params['total_pages'];
      $pagination_params = http_build_query($search_params);

      $links[] = ['label' => '...', 'href' => '#'];
      $links[] = ['label' => (string)$search_params['total_pages'], 'href' => "?{$pagination_params}"];
    }

    // next page link
    if ($current_page < $search_params['total_pages']) {
      $search_params['page'] = $current_page + 1;
      $pagination_params = http_build_query($search_params);
      $links[] = ['label' => 'Next &raquo;', 'href' => "?$pagination_params"];
    }

    return $links;
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
      'Vortex ID',
      'Listing Status',
      'Name',
      'Name 2',
      'Name 3',
      'Name 4',
      'Name 5',
      'Name 6',
      'Name 7',
      'MLS Name',
      'MLS Name 2',
      'MLS Name 3',
      'MLS Name 4',
      'MLS Name 5',
      'MLS Name 6',
      'MLS Name 7',
      'Phone',
      'Phone 2',
      'Phone 3',
      'Phone 4',
      'Phone 5',
      'Phone 6',
      'Phone 7',
      'Phone Status',
      'Phone 2 Status',
      'Phone 3 Status',
      'Phone 4 Status',
      'Phone 5 Status',
      'Phone 6 Status',
      'Phone 7 Status',
      'Email',
      'Email 2',
      'Email 3',
      'Email 4',
      'Email 5',
      'Email 6',
      'Email 7',
      'Address',
      'Address 2',
      'Address 3',
      'Address 4',
      'Address 5',
      'Address 6',
      'Address 7',
      'First Name',
      'Last Name',
      'Mailing Street',
      'Mailing City',
      'Mailing State',
      'Mailing Zip',
      'List Date',
      'List Price',
      'Days On Market',
      'Lead Date',
      'Expired Date',
      'Withdrawn Date',
      'Status Date',
      'Listing Agent',
      'Listing Broker',
      'MLS/FSBO ID',
      'Property Address',
      'Property City',
      'Property State',
      'Property Zip',
    ];
    $required_fields = [
      'Vortex ID',
      'Listing Status',
      'Name',
      'Phone',
      'Email',
      'Mailing Street',
      'Mailing City',
      'Mailing State',
      'Mailing Zip',
      'List Price',
      'Status Date',
      'Property Address',
      'Property City',
      'Property State',
      'Property Zip',
    ];
    $files = $this->params_permit(['leads'], $_FILES);

    return ['files' => $files, 'permitted_fields' => $permitted_fields, 'required_fields' => $required_fields];
  }
}
