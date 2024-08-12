<?php

namespace App\Models;

use App\Models\Application_Record;
use DateTime;

class Lead extends Application_Record
{
  public string $vortex_id;
  public bool $import_lead;
  public bool $lead_assigned;
  public ?string $listing_status;
  public ?string $name;
  public ?string $name_2;
  public ?string $name_3;
  public ?string $name_4;
  public ?string $name_5;
  public ?string $name_6;
  public ?string $name_7;
  public ?string $mls_name;
  public ?string $mls_name_2;
  public ?string $mls_name_3;
  public ?string $mls_name_4;
  public ?string $mls_name_5;
  public ?string $mls_name_6;
  public ?string $mls_name_7;
  public ?string $phone;
  public ?string $phone_2;
  public ?string $phone_3;
  public ?string $phone_4;
  public ?string $phone_5;
  public ?string $phone_6;
  public ?string $phone_7;
  public ?string $phone_status;
  public ?string $phone_2_status;
  public ?string $phone_3_status;
  public ?string $phone_4_status;
  public ?string $phone_5_status;
  public ?string $phone_6_status;
  public ?string $phone_7_status;
  public ?string $email;
  public ?string $email_2;
  public ?string $email_3;
  public ?string $email_4;
  public ?string $email_5;
  public ?string $email_6;
  public ?string $email_7;
  public ?string $address;
  public ?string $address_2;
  public ?string $address_3;
  public ?string $address_4;
  public ?string $address_5;
  public ?string $address_6;
  public ?string $address_7;
  public ?string $first_name;
  public ?string $last_name;
  public ?string $mailing_street;
  public ?string $mailing_city;
  public ?string $mailing_state;
  public ?string $mailing_zip;
  public ?string $list_date;
  public ?string $list_price;
  public ?string $days_on_market;
  public ?string $lead_date;
  public ?string $expired_date;
  public ?string $withdrawn_date;
  public ?string $status_date;
  public ?string $listing_agent;
  public ?string $listing_broker;
  public ?string $mls_fsbo_id;
  public ?string $standardized_mailing_street;
  public bool $absentee_owner;
  public ?string $standardized_property_street;
  public ?string $property_address;
  public ?string $property_city;
  public ?string $property_state;
  public ?string $property_zip;
  public ?string $property_county;
  public ?string $assigned_area;
  public ?string $source;
  public ?string $pipeline;
  public ?string $buyer_seller;
  public ?string $agent_assigned;
  public string $created_at;
  public string $updated_at;

  public function paginate_leads(int $current_page, int $leads_per_index_page, string $sort_order, string $sort_by, array $filters, array $ranges = []): array
  {
    list($leads, $total_pages) = (new Lead)->paginate($current_page, $leads_per_index_page, $sort_order, $sort_by, [], $filters, $ranges);
    $all_counties = (new County)->fetch_by([], ['id', 'name']);

    // create a county id-name map
    $county_map = [];
    foreach ($all_counties as $county) {
      $county_map[$county['id']] = $county['name'];
    }
    $county_map['N/A'] = "MISSING COUNTY INFO";

    $leads_to_show = [];
    // process leads additional column here
    foreach ($leads as $lead) {
      $is_county_info_available = !empty($lead['property_county']) ? true : false;

      // import_lead
      $lead['import_lead'] = $lead['import_lead'] ? "Import" : "Do Not Import";

      // property_county
      $county_id = $lead['property_county'] ?? 'N/A';
      $lead['property_county'] = $county_map[$county_id];

      // assigned_area
      if ($is_county_info_available) {
        if (isset($lead['assigned_area']) && !empty($lead['assigned_area'])) {
          $lead['assigned_area'] = ucfirst($lead['assigned_area']);
        } else {
          $lead['assigned_area'] = "IGNORE ROW";
        }
      } else {
        $lead['assigned_area'] = "MISSING COUNTY INFO";
      }

      // absentee owner
      $lead['absentee_owner'] = $lead['absentee_owner'] ? "Yes" : "No";

      // add to processed leads
      $leads_to_show[] = $lead;
    }

    return [$leads_to_show, $total_pages];
  }

  public function get_leads_from_files(array $files_params): array
  {
    $uploaded_files = $files_params['files']['leads'] ?? [];
    $permitted_fields = $files_params['permitted_fields'] ?? [];
    $required_fields = $files_params['required_fields'] ?? [];

    $new_leads = 0;
    $errors = [];

    // check directory existence
    $directory = self::$ROOT_DIR . self::STORAGE_DIR . "\\leads\\source\\";
    if (!is_dir($directory)) {
      $dir_permissions = 0755;
      $recursive = true;
      mkdir($directory, $dir_permissions, $recursive);
    }

    foreach ($uploaded_files['tmp_name'] as $key => $temp_name) {
      $uploaded_file_name = basename($uploaded_files['name'][$key]);
      $uploaded_file_type = mime_content_type($temp_name);

      // only allow csv
      if ($uploaded_file_type !== "text/csv") {
        $errors[] = "Invalid file type: $uploaded_file_name";
        continue;
      }

      // check if file can be opened
      $handle = fopen($temp_name, "r");
      if (!$handle) {
        $errors[] = "Failed to open file: $uploaded_file_name";
        continue;
      }

      // get headers
      $header = fgetcsv($handle, 0, ",", "\"", "\\");
      if (!$header) {
        $errors[] = "Failed to read header from file: $uploaded_file_name";
        fclose($handle);
        continue;
      }

      // check if required fields are in header
      $missing_fields = array_diff($required_fields, $header);
      if (!empty($missing_fields)) {
        $errors[] = "Missing required fields in $uploaded_file_name: " . implode(", ", $missing_fields);
        fclose($handle);
        continue;
      }

      $file_leads = []; // list of leads in a file
      while (($data = fgetcsv($handle, 0, ",", "\"", "\\")) !== false) {
        $lead_data = array_combine($header, $data);

        $lead = []; // a single lead
        foreach ($permitted_fields as $field) {
          $field_name = strtolower(str_replace([" ", "/"], "_", $field));

          $field_value = empty($lead_data[$field]) ? null : $lead_data[$field];

          // process date fields
          $date_fields = ['list_date', 'lead_date', 'expired_date', 'withdrawn_date', 'status_date'];
          if (in_array($field_name, $date_fields) && !empty($field_value)) {
            // convert date format
            $date = DateTime::createFromFormat('m-d-Y', $field_value);
            if ($date) {
              $field_value = $date->format('Y-m-d');
            }
          }

          $lead[$field_name] = $field_value;
        }

        $file_leads[] = $lead;
      }

      // skip database save if empty
      if (empty($file_leads)) {
        $errors[] = "No new leads found in file: $uploaded_file_name";
        continue;
      }

      // perform duplicate filtration and batch insert
      $inserted_leads = $this->insert_all($file_leads, ['unique_by' => 'vortex_id', 'batch_size' => 300]);
      if ($inserted_leads === false) {
        $errors[] = "Failed to save leads from file: $uploaded_file_name";
        continue;
      }

      if (empty($inserted_leads)) {
        $errors[] = "No new leads found in file: $uploaded_file_name";
        continue;
      }


      $new_leads = $new_leads + $inserted_leads;

      // close file handle
      fclose($handle);

      // move file to storage
      $date = new DateTime(); // system's default timezone
      $formatted_date = $date->format('Y-m-d_Hisu');
      $trimmed_file_name = str_replace(".csv", "", $uploaded_file_name);
      $file_destination = $directory . $formatted_date . "_leads-source (" . $trimmed_file_name . ").csv";

      if (!move_uploaded_file($temp_name, $file_destination)) {
        $errors[] = "Failed to move file to storage: $uploaded_file_name";
      }
    }

    return [$new_leads, $errors];
  }

  public function assign_leads(): array
  {
    $unassigned_leads = $this->fetch_by(['lead_assigned' => false, 'import_lead' => true], ['id', 'vortex_id', 'import_lead', 'listing_status', 'lead_assigned', 'property_city', 'property_county', 'assigned_area', 'mailing_street', 'absentee_owner', 'property_address', 'standardized_mailing_street', 'standardized_property_street', 'source', 'pipeline', 'buyer_seller', 'agent_assigned']);
    $all_cities = (new City)->fetch_by([], ['id', 'name', 'county_id']);

    $city_to_county = [];
    foreach ($all_cities as $city) {
      $city_name = strtolower($city['name']);
      $city_to_county[$city_name] = $city['county_id'];
    }

    $county_to_area = config('mrcleads.assigned_areas') ?? [];
    $street_suffix_lookup = config('mrcleads.street_suffix_lookup') ?? [];

    $leads_to_be_updated = [];
    $error_messages = [];
    $assigned_leads = [];

    // process each lead
    foreach ($unassigned_leads as $lead) {

      // === process property_county and assigned_area ===
      $lead_city = isset($lead['property_city']) ? strtolower($lead['property_city']) : '';

      // if found in cities list, process 
      if (isset($city_to_county[$lead_city])) {

        // fetch the matched county info
        $county_id = $city_to_county[$lead_city];
        $county = (new County)->find_by(['id' => $county_id], ['id', 'name']);
        $county_name = strtolower($county->name);

        // look up assigned areas if theres a match
        $assigned_area = null;
        foreach ($county_to_area as $area => $counties) {
          if (!in_array($county_name, $counties)) continue;

          $assigned_area = $area;
          break;
        }

        // assign property_county and assigned_area values to lead 
        $lead['property_county'] = $county_id;
        $lead['assigned_area'] = $assigned_area;
      }


      // === process absentee owner ===

      // standardize mailing street first
      $mailing_street  = $lead['mailing_street'] ?? '';
      $standardized_mailing_street = $this->standardize_street_address($mailing_street, $street_suffix_lookup);

      // standardize property address next
      $property_street = $lead['property_address'] ?? '';
      $standardized_property_street = $this->standardize_street_address($property_street, $street_suffix_lookup);

      // assign standardized_mailing_street and standardized_property_street
      $lead['standardized_mailing_street'] = $standardized_mailing_street;
      $lead['standardized_property_street'] = $standardized_property_street;

      // compare and determine absentee ownership
      if ($standardized_mailing_street !== $standardized_property_street) {
        $lead['absentee_owner'] = true;
      }


      // === process source, pipeline, buyer/seller, agent assigned ===

      // determine source if REDX or Absentee Owner
      $is_expired = (isset($lead['listing_status']) && in_array(trim($lead['listing_status']), ["FSBO", "FRBO"])) ? false : true;
      $absentee_owner = isset($lead['absentee_owner']) ? (bool) $lead['absentee_owner'] : false;
      $lead['source'] = $absentee_owner && $is_expired ? 'Absentee Owner' : 'REDX';

      // set pipeline, buyer/seller, and agent assigned
      $lead['pipeline'] = 'New Lead';
      $lead['buyer_seller'] = 'Seller';
      $lead['agent_assigned'] = 'Jessica Knight';

      // check if assigning is complete
      $county_checked = isset($lead['property_county']) && !empty($lead['property_county']);
      $source_checked = isset($lead['source']) && !empty($lead['source']);
      $pipeline_checked = isset($lead['pipeline']) && !empty($lead['pipeline']);
      $buyer_seller_checked = isset($lead['buyer_seller']) && !empty($lead['buyer_seller']);
      $agent_assigned_checked = isset($lead['agent_assigned']) && !empty($lead['agent_assigned']);

      if ($county_checked && $source_checked && $pipeline_checked & $buyer_seller_checked && $agent_assigned_checked) {
        $lead['lead_assigned'] = true;

        // count as assigned lead
        $assigned_leads[] = $lead['vortex_id'];
      }

      if ($county_checked && empty($lead['assigned_area'])) {
        $lead['import_lead'] = false;
      }

      // inlcude lead to be updated
      $leads_to_be_updated[] = $lead;
    }

    $updated_leads = $this->update_all($leads_to_be_updated, ['unique_by' => 'vortex_id', 'batch_size' => 300]);

    if ($updated_leads === false) {
      $error_messages[] = "No leads assigned";
      return [null, $error_messages];
    }

    // $assigned_leads = $this->check_column_value($updated_leads, ['lead_assigned' => true], ['unique_by' => 'vortex_id', 'returning' => ['vortex_id']]);

    return [$assigned_leads, $error_messages];
  }

  public function get_export(string $category, string $area): array
  {
    $exported_file = [];
    $error_messages = [];

    // only fetch leads to import
    $fetch_conditions = [
      'import_lead' => true,
    ];

    // define header rename map
    $default_columns = [
      'vortex_id' => 'Vortex ID',
      'listing_status' => 'Listing Status',
      'name' => 'Full Name',
      'phone' => 'Cell Phone',
      'phone_2' => 'Home Phone',
      'phone_3' => 'Work Phone',
      'phone_4' => 'Phone 4',
      'phone_5' => 'Phone 5',
      'phone_6' => 'Phone 6',
      'phone_7' => 'Phone 7',
      'email' => 'Email',
      'email_2' => 'Email 2',
      'email_3' => 'Email 3',
      'email_4' => 'Email 4',
      'email_5' => 'Email 5',
      'email_6' => 'Email 6',
      'email_7' => 'Email 7',
      'mailing_street' => 'Street Address',
      'mailing_city' => 'City',
      'mailing_state' => 'State',
      'mailing_zip' => 'Zip/Postal Code',
      'list_price' => 'List Price', // process price format
      'status_date' => 'Register Date', // process date format
      'mls_fsbo_id' => 'MLS/FSBO ID',
      'absentee_owner' => 'Absentee Owner', // process bool yes or no
      'property_address' => 'Property Address',
      'property_city' => 'Property City',
      'property_state' => 'Property State',
      'property_zip' => 'Property Zip',
      'property_county' => 'Property County', // process value
      'source' => 'Source',
      'pipeline' => 'Pipeline',
      'buyer_seller' => 'Buyer/Seller',
      'agent_assigned' => 'Agent Assigned',
    ];

    // define leads and columns to be fetched per lead category
    switch ($area) {
      case "montgomery":
      case "auburn":
        $fetch_conditions['assigned_area'] = $area;
        if (!empty($category)) {
          switch ($category) {
            case "absentee_owner":
              $fetch_conditions['listing_status'] = ['Expired', 'Withdrawn', 'Off Market', 'Cancelled'];
              $fetch_conditions['absentee_owner'] = true;
              break;
            case "expired":
              $fetch_conditions['listing_status'] = ['Expired', 'Withdrawn', 'Off Market', 'Cancelled'];
              $fetch_conditions['absentee_owner'] = false;
              break;
            case "frbo":
              $fetch_conditions['listing_status'] = "FRBO";
              unset($default_columns['listing_status']);
              break;
            case "fsbo":
              $fetch_conditions['listing_status'] = "FSBO";
              unset($default_columns['listing_status']);
              break;
          }
        }
        break;
    }

    // prepare columns to be fetched based on array definition
    $returned_columns = array_keys($default_columns);

    // fetch leads
    $leads_to_be_processed = $this->fetch_by($fetch_conditions, $returned_columns);

    if (empty($leads_to_be_processed)) {
      $error_messages[] = "No leads to export.";
      return [[], $error_messages];
    };

    // define removeable columns if empty
    $columns_to_check_if_empty = [
      'phone_4',
      'phone_5',
      'phone_6',
      'phone_7',
      'email_2',
      'email_3',
      'email_4',
      'email_5',
      'email_6',
      'email_7',
    ];
    // scan empty columns and define in an array
    $columns_to_remove = [];
    foreach ($columns_to_check_if_empty as $column) {
      $is_empty = true;

      foreach ($leads_to_be_processed as $lead) {
        if ($lead[$column] !== null) {
          $is_empty = false;
          break;
        }
      }

      if ($is_empty) {
        $columns_to_remove[] = $column;
      }
    }

    // remove empty columns from each lead
    foreach ($leads_to_be_processed as &$lead) {
      foreach ($columns_to_remove as $column) {
        unset($lead[$column]);
      }
    }
    unset($lead);

    // fetch counties
    $counties = (new County)->all();
    // create county id name map
    $county_map = array_column($counties, 'name', 'id');

    // format values
    foreach ($leads_to_be_processed as &$lead) {
      // format list_price
      if (isset($lead['list_price'])) {
        $lead['list_price'] = (int)$lead['list_price'];
      }

      // format status_date
      if (isset($lead['status_date'])) {
        $formatted_date = DateTime::createFromFormat('Y-m-d', $lead['status_date']);
        if ($formatted_date) {
          $lead['status_date'] = $formatted_date->format('m-d-Y');
        }
      }

      // format absentee_owner
      if (isset($lead['absentee_owner'])) {
        $lead['absentee_owner'] = $lead['absentee_owner'] ? "Yes" : "No";
      }

      // format property_county
      if (isset($lead['property_county']) && isset($county_map[$lead['property_county']])) {
        $lead['property_county'] = $county_map[$lead['property_county']];
      }

      // format other column values here
    }
    unset($lead);

    // prepare csv content in memory
    $file_output = fopen('php://temp', 'w+');
    if ($file_output === false) {
      $error_messages[] = "Failed to open temporary file.";
      return [[], $error_messages];
    }

    // get headers from leads keys of the first lead
    $headers = array_keys($leads_to_be_processed[0]);
    // rename headers
    foreach ($headers as &$header) {
      if (isset($default_columns[$header])) {
        $header = $default_columns[$header];
      }
    }
    unset($header);

    // write headers to the csv file
    fputcsv($file_output, $headers);
    // write each leads to the csv file
    foreach ($leads_to_be_processed as $lead) {
      fputcsv($file_output, $lead);
    }
    unset($lead);

    // move to the beginning of the stream
    rewind($file_output);


    // define file path and name
    $export_directory = self::$ROOT_DIR . self::STORAGE_DIR . "\\leads\\" . $category . "\\";
    $export_date = new DateTime();
    $formatted_export_date = $export_date->format("Ymd");
    $leads_category = "";
    switch ($category) {
      case "absentee_owner":
        $leads_category = "expireds-ao-{$area}";
        break;
      case "expired":
        $leads_category = "expireds-{$area}";
        break;
      case "frbo":
        $leads_category = "frbo-{$area}";
        break;
      case "fsbo":
        $leads_category = "fsbo-{$area}";
        break;
    }
    $file_name = "{$formatted_export_date}-{$leads_category}.csv";
    $file_path = $export_directory . $file_name;

    // check if the file already exists
    if (file_exists($file_path)) {
      $exported_file['path'] = $file_path;
      $exported_file['name'] = $file_name;
      $exported_file['type'] = 'text/csv';
      $exported_file['size'] = filesize($file_path);
      return [$exported_file, $error_messages];
    }

    // check if directory exists
    if (!is_dir($export_directory)) {
      mkdir($export_directory, 0755, true);
    }

    // open file for writing
    $file_saved = fopen($file_path, "w");

    // check if file opened successfully
    if ($file_saved === false) {
      $error_messages[] = "Failed to save file: $file_name";
      fclose($file_output);
      return [[], $error_messages];
    }

    // write filie content to the file
    rewind($file_output);
    stream_copy_to_stream($file_output, $file_saved);

    // close storage file
    fclose($file_saved);
    fclose($file_output);

    // add filepath to return object
    $exported_file['path'] = $file_path;
    $exported_file['name'] = $file_name;
    $exported_file['type'] = 'text/csv';
    $exported_file['size'] = filesize($file_path);

    return [$exported_file, $error_messages];
  }

  public function toggle_property(string $property)
  {
    switch ($property) {
      case 'absentee_owner':
        $absentee_owner = $this->absentee_owner ?? false;
        $listing_status = $this->listing_status ?? '';

        if ($listing_status === "FRBO" || $listing_status === "FSBO") {
          $source = "REDX";
        } else {
          $source = $this->absentee_owner ? "REDX" : "Absentee Owner";
        }

        $this->update_column('absentee_owner', !$absentee_owner);
        $this->update_column('source', $source);
        break;

      case 'import_lead':
        $import_lead = $this->import_lead ?? true;
        $this->update_column('import_lead', !$import_lead);
        break;
    }
  }

  private function standardize_street_address(string $address, array $suffix_lookup): null|string
  {
    if (empty($address)) return null;

    $address = trim(str_replace("#", "", $address));
    if (empty($address)) return null;

    $address = strtoupper($address);

    $address_parts = explode(" ", $address);

    $standardized_address = "";
    foreach ($address_parts as $part) {
      if (!array_key_exists($part, $suffix_lookup)) {
        $standardized_address .= $part . " ";
      } else {
        $standardized_address .= $suffix_lookup[$part] . " ";
      }
    }

    // replace multiple spaces with a single space
    $standardized_address = preg_replace('/\s+/', ' ', $standardized_address);

    return trim($standardized_address);
  }
}
