<?php

namespace App\Models;

use App\Models\Application_Record;

class Lead extends Application_Record
{
  // vortex_id VARCHAR(255) NOT NULL,
  // lead_imported BOOLEAN DEFAULT FALSE,
  // listing_status VARCHAR(255) NULL,
  // name VARCHAR(255) NULL,
  // name_2 VARCHAR(255) NULL,
  // name_3 VARCHAR(255) NULL,
  // name_4 VARCHAR(255) NULL,
  // name_5 VARCHAR(255) NULL,
  // name_6 VARCHAR(255) NULL,
  // name_7 VARCHAR(255) NULL,
  // mls_name VARCHAR(255) NULL,
  // mls_name_2 VARCHAR(255) NULL,
  // mls_name_3 VARCHAR(255) NULL,
  // mls_name_4 VARCHAR(255) NULL,
  // mls_name_5 VARCHAR(255) NULL,
  // mls_name_6 VARCHAR(255) NULL,
  // mls_name_7 VARCHAR(255) NULL,
  // phone VARCHAR(20) NULL,
  // phone_status VARCHAR(255) NULL,
  // phone_2 VARCHAR(20) NULL,
  // phone_2_status VARCHAR(255) NULL,
  // phone_3 VARCHAR(20) NULL,
  // phone_3_status VARCHAR(255) NULL,
  // phone_4 VARCHAR(20) NULL,
  // phone_4_status VARCHAR(255) NULL,
  // phone_5 VARCHAR(20) NULL,
  // phone_5_status VARCHAR(255) NULL,
  // phone_6 VARCHAR(20) NULL,
  // phone_6_status VARCHAR(255) NULL,
  // phone_7 VARCHAR(20) NULL,
  // phone_7_status VARCHAR(255) NULL,
  // email VARCHAR(255) NULL,
  // email_2 VARCHAR(255) NULL,
  // email_3 VARCHAR(255) NULL,
  // email_4 VARCHAR(255) NULL,
  // email_5 VARCHAR(255) NULL,
  // email_6 VARCHAR(255) NULL,
  // email_7 VARCHAR(255) NULL,
  // address TEXT NULL,
  // address_2 TEXT NULL,
  // address_3 TEXT NULL,
  // address_4 TEXT NULL,
  // address_5 TEXT NULL,
  // address_6 TEXT NULL,
  // address_7 TEXT NULL,
  // first_name VARCHAR(255) NULL,
  // last_name VARCHAR(255) NULL,
  // mailing_street VARCHAR(255) NULL,
  // mailing_city VARCHAR(255) NULL,
  // mailing_state VARCHAR(255) NULL,
  // mailing_zip VARCHAR(20) NULL,
  // list_date DATE NULL,
  // list_price DECIMAL(20, 2) NULL,
  // days_on_market INT NULL,
  // lead_date DATE NULL,
  // expired_date DATE NULL,
  // withdrawn_date DATE NULL,
  // status_date DATE NULL,
  // listing_agent VARCHAR(255) NULL,
  // listing_broker VARCHAR(255) NULL,
  // mls_fsbo_id VARCHAR(255) NULL,
  // absentee_owner BOOLEAN DEFAULT FALSE,
  // property_address VARCHAR(255) NULL,
  // property_city VARCHAR(255) NULL,
  // property_state VARCHAR(255) NULL,
  // property_zip VARCHAR(20) NULL,
  // property_county VARCHAR(255) NULL,
  // assigned_area VARCHAR(255) NULL,
  // source VARCHAR(255) NULL,
  // pipeline VARCHAR(255) NULL,
  // buyer_seller VARCHAR(255) NULL,
  // agent_assigned VARCHAR(255) NULL,
  public string $vortex_id;
  public bool $lead_imported;
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
  public ?string $listing_date;
  public ?string $listing_price;
  public ?string $days_on_market;
  public ?string $lead_date;
  public ?string $expired_date;
  public ?string $withdrawn_date;
  public ?string $status_date;
  public ?string $listing_agent;
  public ?string $listing_broker;
  public ?string $mls_fsbo_id;
  public bool $absentee_owner;
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
}
