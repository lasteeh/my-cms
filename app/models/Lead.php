<?php

namespace App\Models;

use App\Models\Application_Record;

class Lead extends Application_Record
{
  public string $vortex_id;
  public bool $lead_imported;
  public bool $lead_assigned;
  public bool $lead_processed;
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

  public function paginate_leads(int $current_page, int $leads_per_index_page, string $sort_order, string $sort_by): array
  {
    list($leads, $total_pages) = (new Lead)->paginate($current_page, $leads_per_index_page, $sort_order, $sort_by);
    $all_counties = (new County)->fetch_by([], ['id', 'name']);

    // create a county id-name map
    $county_map = [];
    foreach ($all_counties as $county) {
      $county_map[$county['id']] = $county['name'];
    }
    $county_map['N/A'] = "MISSING COUNTY INFO";

    $processed_leads = [];
    // process leads additional column here
    foreach ($leads as $lead) {
      $is_county_info_available = !empty($lead['property_county']) ? true : false;

      // lead_imported
      $lead['lead_imported'] = $lead['lead_imported'] ? "Do Not Import" : "Import";

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

      // add to processed leads
      $processed_leads[] = $lead;
    }

    return [$processed_leads, $total_pages];
  }

  public function get_leads_from_files(array $saved_files, array $permitted_fields = []): array
  {
    $directory = self::$ROOT_DIR . self::STORAGE_DIR . "\\leads\\";
    $lead_ids = []; // list of leads that will be returned later
    $error_messages = []; // list of errors that will be returned later

    if (empty($saved_files)) {
      $error_messages[] = "No leads found.";
      return [null, $error_messages];
    }

    foreach ($saved_files as $file) {
      $file_name = str_replace($directory, "", $file);

      if (!file_exists($file)) {
        $error_messages[] = "File not found in storage: $file_name";
        continue;
      }

      $handle = fopen($file, "r");
      if (!$handle) {
        $error_messages[] = "Failed to open file: $file_name";
        continue;
      }

      $header = fgetcsv($handle, 0, ",", "\"", "\\");
      if (!$header) {
        $error_messages[] = "Failed to read header: $file_name";
        fclose($handle);
        continue;
      }

      $file_leads = []; // list of leads in one file
      while (($data = fgetcsv($handle, 0, ",", "\"", "\\")) !== false) {
        $lead_data = array_combine($header, $data);

        $lead = []; // a single lead
        foreach ($permitted_fields as $field) {
          $field_name = strtolower($field);
          $field_name = str_replace(" ", "_", $field_name);
          $field_name = str_replace("/", "_", $field_name);

          $lead[$field_name] = empty($lead_data[$field]) ? null : $lead_data[$field];
        }

        $file_leads[] = $lead;
      }

      fclose($handle);

      // skip db save if empty
      if (empty($file_leads)) {
        $error_messages[] = "No new leads found in file: $file_name";
        continue;
      }

      // perform duplicates filtration and batch insert
      $inserted_lead_ids = $this->insert_all($file_leads, ['unique_by' => 'vortex_id', 'batch_size' => 300]);
      if ($inserted_lead_ids === false) {
        $error_messages[] = "Failed to save leads from file: $file_name";
      }

      $lead_ids = array_merge($lead_ids, $inserted_lead_ids);
    }

    return [$lead_ids, $error_messages];
  }

  public function assign_leads(): array
  {
    $unassigned_leads = $this->fetch_by(['lead_assigned' => false], ['id', 'vortex_id', 'lead_assigned', 'property_city', 'property_county', 'assigned_area', 'mailing_street', 'absentee_owner', 'property_address', 'standardized_mailing_street', 'standardized_property_street', 'source', 'pipeline', 'buyer_seller', 'agent_assigned']);
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
      $absentee_owner = isset($lead['absentee_owner']) ? (bool) $lead['absentee_owner'] : false;
      $lead['source'] = $absentee_owner ? 'Absentee Owner' : 'REDX';

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
      }

      // inlcude lead to be assigned
      $leads_to_be_updated[] = $lead;
    }

    $updated_leads = $this->update_all($leads_to_be_updated, ['unique_by' => 'vortex_id', 'batch_size' => 300]);

    if ($updated_leads === false) {
      $error_messages[] = "Failed to assign leads";
      return [null, $error_messages];
    }

    $assigned_leads = $this->check_column_value($updated_leads, ['lead_assigned' => true], ['unique_by' => 'vortex_id', 'returning' => ['vortex_id']]);

    return [$assigned_leads, $error_messages];
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

    return trim($standardized_address);
  }
}
