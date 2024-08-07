<?php
$errors = $this->get_flash('errors');
$alerts = $this->get_flash('alerts');
$leads = $this->get_object('leads');
$search_params = $this->get_object('search_params');

var_dump($search_params);
echo "<br>";
echo "<br>";
var_dump(http_build_query($search_params['filter_by']));

var_dump($search_params['range']);
?>

<style>
  #table {
    max-height: 70dvh;
    overflow: auto;

    & table {
      position: relative;
      text-align: left;
      border-spacing: 0;

      isolation: isolate;
    }

    & table :where(th,
      td) {
      padding: 0.1em 0.75em;
      border: 1px solid darkgray;
    }

    & tr:first-child {
      position: sticky;
      top: 0;
      background-color: lightgrey;
      z-index: 1;
    }

    & :where(td, th):first-child {
      position: sticky;
      left: 0;
    }

    & th:first-child {
      background-color: lightgrey;
    }

    & td:first-child {
      padding: 0.5em;
      background-color: lightgrey;
      color: black;
    }

    & td:first-child p {
      margin-inline-start: auto;
      font-size: 0.75rem;
    }


    & tr:where([data-lead-assigned="false"]) {
      --bg-color: var(--bg-red);
      background-color: hsl(var(--bg-color), 0.2);
    }

    & tr:where([data-lead-assigned="true"][data-area-assigned="false"], [data-ignore="true"]) {
      --bg-color: var(--bg-gray);
      background-color: hsl(var(--bg-color), 0.2);
      color: hsl(var(--bg-gray));

      & span {
        color: hsl(var(--bg-gray));
      }
    }

    & tr:where([data-absentee-owner="true"]) td.absentee_owner {
      background-color: hsl(var(--bg-orange), 0.2);
    }

    & tr:not(:first-child):hover {
      background-color: hsl(var(--bg-color, var(--bg-blue)), 0.7);
    }

    & tr:focus {
      background-color: hsl(var(--bg-color, var(--bg-blue)), 0.5);
    }

    & td:not(:has(a))>p {
      width: max-content;
    }

    & td:has(a)>p,
    & td:has(a)>p>a {
      display: block;
      width: 100%;
    }
  }
</style>

<div>
  <form action="<?= $this->url("/dashboard/leads") ?>">

    <ul>
      <li><input type="checkbox" name="filter_by[listing_status][]" id="expired" value="Expired"><label for="expired">Expireds</label></li>
      <li><input type="checkbox" name="filter_by[listing_status][]" id="withdrawn" value="Withdrawn"><label for="withdrawn">Withdrawn</label></li>
      <li><input type="checkbox" name="filter_by[listing_status][]" id="off_market" value="Off Market"><label for="off_market">Off Market</label></li>
      <li><input type="checkbox" name="filter_by[listing_status][]" id="cancelled" value="Cancelled"><label for="cancelled">Cancelled</label></li>
      <li><input type="checkbox" name="filter_by[listing_status][]" id="frbo" value="FRBO"><label for="frbo">FRBO</label></li>
      <li><input type="checkbox" name="filter_by[listing_status][]" id="fsbo" value="FSBO"><label for="fsbo">FSBO</label></li>
    </ul>

    <ul>
      <li><input type="checkbox" name="filter_by[import_lead][]" id="import" value="1"><label for="import">Import</label></li>
      <li><input type="checkbox" name="filter_by[import_lead][]" id="do_not_import" value="0"><label for="do_not_import">Do Not Import</label></li>
    </ul>

    <ul>
      <li><input type="checkbox" name="filter_by[assigned_area][]" id="montgomery" value="montgomery"><label for="montgomery">Montgomery</label></li>
      <li><input type="checkbox" name="filter_by[assigned_area][]" id="auburn" value="auburn"><label for="auburn">Auburn</label></li>
    </ul>

    <ul>
      <li><input type="checkbox" name="filter_by[absentee_owner][]" id="absentee_owner" value="1"><label for="absentee_owner">Absentee Owner</label></li>
    </ul>

    <ul>
      <li><input type="checkbox" name="filter_by[lead_assigned][]" id="assigned" value="1"><label for="assigned">Assigned</label></li>
      <li><input type="checkbox" name="filter_by[lead_assigned][]" id="unassigned" value="0"><label for="unassigned">Unassigned</label></li>
    </ul>

    <ul>
      <li><input type="date" name="range[created_at][]" id="start_date" max="<?= (new DateTime)->format('Y-m-d') ?>" value="<?= $search_params['range']['created_at'][0] ?? '' ?>"><label for="start_date">Start Date</label></li>
      <li><input type="date" name="range[created_at][]" id="end_date" max="<?= (new DateTime)->format('Y-m-d') ?>" value="<?= $search_params['range']['created_at'][1] ?? '' ?>"><label for="end_date">End Date</label></li>
    </ul>
    <button type="submit">Filter</button>
  </form>
</div>
<div id="table">
  <table>
    <tr>
      <th></th>
      <th class="vortex_id">Vortex ID</th>
      <th class="import_lead">Import Status</th>
      <th class="listing_status">Listing Status</th>
      <th class="name">Full Name</th>
      <th class="phone">Cell Phone</th>
      <th class="phone_2">Home Phone</th>
      <th class="phone_3">Work Phone</th>
      <th class="phone_4">Phone 4</th>
      <th class="phone_5">Phone 5</th>
      <th class="phone_6">Phone 6</th>
      <th class="phone_7">Phone 7</th>
      <th class="email">Email</th>
      <th class="email_2">Email 2</th>
      <th class="email_3">Email 3</th>
      <th class="email_4">Email 4</th>
      <th class="email_5">Email 5</th>
      <th class="email_6">Email 6</th>
      <th class="email_7">Email 7</th>
      <th class="mailing_street">Street Address</th>
      <th class="mailing_city">City</th>
      <th class="mailing_state">State</th>
      <th class="mailing_zip">Zip/Postal Code</th>
      <th class="list_price">List Price</th>
      <th class="status_date">Register Date</th>
      <th class="mls_fsbo_id">MLS/FSBO ID</th>
      <th class="standardized_mailing_street">Standardized Mailing Street</th>
      <th class="absentee_owner">Absentee Owner</th>
      <th class="standardized_property_street">Standardized Property Street</th>
      <th class="property_address">Property Address</th>
      <th class="property_city">Property City</th>
      <th class="property_state">Property State</th>
      <th class="property_zip">Property Zip</th>
      <th class="property_county">Property County</th>
      <th class="assigned_area">Assigned Area</th>
      <th class="source">Source</th>
      <th class="pipeline">Pipeline</th>
      <th class="buyer_seller">Buyer/Seller</th>
      <th class="agent_assigned">Agent Assigned</th>
    </tr>

    <?php
    foreach ($leads as $index => $lead) {
      $row_number = $index + 1;
      $lead_id = $lead['id'];
      $vortex_id = $lead['vortex_id'];
      $import_lead = $lead['import_lead'];
      $lead_assigned = $lead['lead_assigned'];
      $listing_status = $lead['listing_status'];
      $name = $lead['name'];
      $phone = $lead['phone'];
      $phone_2 = $lead['phone_2'];
      $phone_3 = $lead['phone_3'];
      $phone_4 = $lead['phone_4'];
      $phone_5 = $lead['phone_5'];
      $phone_6 = $lead['phone_6'];
      $phone_7 = $lead['phone_7'];
      $email = $lead['email'];
      $email_2 = $lead['email_2'];
      $email_3 = $lead['email_3'];
      $email_4 = $lead['email_4'];
      $email_5 = $lead['email_5'];
      $email_6 = $lead['email_6'];
      $email_7 = $lead['email_7'];
      $mailing_street = $lead['mailing_street'];
      $mailing_city = $lead['mailing_city'];
      $mailing_state = $lead['mailing_state'];
      $mailing_zip = $lead['mailing_zip'];
      $list_price = $lead['list_price'];
      $status_date = $lead['status_date'];
      $mls_fsbo_id = $lead['mls_fsbo_id'];
      $standardized_mailing_street = $lead['standardized_mailing_street'];
      $absentee_owner = $lead['absentee_owner'];
      $standardized_property_street = $lead['standardized_property_street'];
      $property_address = $lead['property_address'];
      $property_city = $lead['property_city'];
      $property_state = $lead['property_state'];
      $property_zip = $lead['property_zip'];
      $property_county = $lead['property_county'];
      $assigned_area = $lead['assigned_area'];
      $source = $lead['source'];
      $pipeline = $lead['pipeline'];
      $buyer_seller = $lead['buyer_seller'];
      $agent_assigned = $lead['agent_assigned'];

      $is_lead_assigned = $lead['lead_assigned'] ? 'true' : 'false';
      $is_area_assigned = $lead['assigned_area'] === "IGNORE ROW" ? 'false' : 'true';
      $is_absentee_owner = $lead['absentee_owner'] === "Yes" ? 'true' : 'false';
      $ignore_lead = $lead['import_lead'] === "Do Not Import" ? "true" : "false";

      $row = <<<HTML
        <tr id="$row_number" data-ignore="$ignore_lead" data-lead-assigned="$is_lead_assigned" data-area-assigned="$is_area_assigned" data-absentee-owner="$is_absentee_owner" tabindex="0">
          <td><p>$row_number</p></td>
          <td class="vortex_id"><p>$vortex_id</p></td>
          <td class="import_lead" style="padding: 0;">
            <form action="" method="post">
              <input type="hidden" name="origin_url" value="">
              <input type="hidden" name="row" value="$row_number">
              <button type="submit" style="width: 100%; padding: 0.1em 0.75em; display: flex; flex-flow: row nowrap; gap: 1em; align-items: center; justify-content: space-between; text-decoration: none; background-color: transparent; border: none; cursor: pointer; font: inherit">
                <span style="width: max-content;">$import_lead</span>
                <span>&#128260;</span>
              </button>
            </form>
          </td>
          <td class="listing_status"><p>$listing_status</p></td>
          <td class="name"><p>$name</p></td>
          <td class="phone"><p>$phone</p></td>
          <td class="phone_2"><p>$phone_2</p></td>
          <td class="phone_3"><p>$phone_3</p></td>
          <td class="phone_4"><p>$phone_4</p></td>
          <td class="phone_5"><p>$phone_5</p></td>
          <td class="phone_6"><p>$phone_6</p></td>
          <td class="phone_7"><p>$phone_7</p></td>
          <td class="email"><p>$email</p></td>
          <td class="email_2"><p>$email_2</p></td>
          <td class="email_3"><p>$email_3</p></td>
          <td class="email_4"><p>$email_4</p></td>
          <td class="email_5"><p>$email_5</p></td>
          <td class="email_6"><p>$email_6</p></td>
          <td class="email_7"><p>$email_7</p></td>
          <td class="mailing_street"><p>$mailing_street</p></td>
          <td class="mailing_city"><p>$mailing_city</p></td>
          <td class="mailing_state"><p>$mailing_state</p></td>
          <td class="mailing_zip"><p>$mailing_zip</p></td>
          <td class="list_price"><p>$list_price</p></td>
          <td class="status_date"><p>$status_date</p></td>
          <td class="mls_fsbo_id"><p>$mls_fsbo_id</p></td>
          <td class="standardized_mailing_street"><p>$standardized_mailing_street</p></td>
          <td class="absentee_owner" style="padding: 0;">
            <form action="" method="post">
              <input type="hidden" name="origin_url" value="">
              <input type="hidden" name="row" value="$row_number">
              <button type="submit" style="width: 100%; padding: 0.1em 0.75em; display: flex; flex-flow: row wrap; align-items: center; justify-content: space-between; text-decoration: none; background-color: transparent; border: none; cursor: pointer; font: inherit">
                <span>$absentee_owner</span>
                <span>&#128260;</span>
              </button>
            </form>
          </td>
          <td class="standardized_property_street"><p>$standardized_property_street</p></td>
          <td class="property_address"><p>$property_address</p></td>
          <td class="property_city"><p>$property_city</p></td>
          <td class="property_state"><p>$property_state</p></td>
          <td class="property_zip"><p>$property_zip</p></td>
          <td class="property_county"><p>$property_county</p></td>
          <td class="assigned_area"><p>$assigned_area</p></td>
          <td class="source"><p>$source</p></td>
          <td class="pipeline"><p>$pipeline</p></td>
          <td class="buyer_seller"><p>$buyer_seller</p></td>
          <td class="agent_assigned"><p>$agent_assigned</p></td>
        </tr>
      HTML;
      echo $row;
    }
    ?>

  </table>
</div>