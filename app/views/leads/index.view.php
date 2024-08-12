<?php
$protocol = $_SERVER['REQUEST_SCHEME'] . '://';
$domain = $_SERVER['HTTP_HOST'];
$request_uri = $_SERVER['REQUEST_URI'];
$origin_url = str_replace(self::$ROOT_URL, "", $protocol . $domain . $request_uri);

$errors = $this->get_flash('errors');
$alerts = $this->get_flash('alerts');
$leads = $this->get_object('leads');
$search_params = $this->get_object('search_params');

$lead_area = $leads['area'] ?? '';
$lead_category = $leads['category'] ?? '';

$paginations = $leads['config']['pagination'] ?? [];
$pagination_links = '<ul class="pagination">';
foreach ($paginations as $link) {
  $label = $link['label'] ?? '';
  $href = $link['href'] ?? '';
  $classes = " pagination-link " . ((isset($link['current']) && $link['current'] === true) ? " current " : " ") . ($href === '#' ? "inactive" : " ");
  $anchor = <<<HTML
    <li><a href="$href" class="$classes">$label</a></li>
  HTML;

  $pagination_links .= $anchor;
}
$pagination_links .= '<ul>';

$is_checked = function (string $key, mixed $value, array $search_params) {
  if (empty($search_params['filter_by'])) return '';
  if (empty($search_params['filter_by'][$key])) return '';

  if (is_array($search_params['filter_by'][$key])) {
    return (in_array($value, $search_params['filter_by'][$key]) ? 'checked' : '');
  }

  return ($value === $search_params['filter_by'][$key] ? 'checked' : '');
};
?>

<style>
  #leads-table {
    isolation: isolate;
  }

  #upload {
    & input[type="file"] {
      border: 1px solid lightgrey;
      border-radius: 0.25em;

      &::file-selector-button {
        padding: 0.25em 0.5em;
      }
    }
  }

  #filter {
    display: flex;
    flex-flow: row wrap;
    gap: 0.5em;
    justify-content: space-between;
    align-items: stretch;

    position: relative;
    isolation: isolate;
    z-index: 2;

    >* {
      flex: 1 1 auto;
    }

    & button {
      padding: 0.25em 0.5em;
    }

    & .filter-group {
      position: relative;

      &:has(button[data-show="false"]) ul {
        display: none;
      }

      & button {
        width: 100%;
        text-align: left;

        display: flex;
        flex-flow: row nowrap;
        justify-content: space-between;
      }

      & ul {
        list-style: none;

        position: absolute;
        top: 100%;
        left: 0;
        z-index: 2;

        min-width: 100%;
        width: max-content;
        border-radius: 0.25em;

        background-color: white;
        box-shadow: 1px 1px 4px hsl(0, 0%, 0%, .25);

        overflow: hidden;

        & label {
          display: block;
          padding: 0.25em 0.5em;
        }
      }

      & input[type="checkbox"] {
        margin-inline-end: 0.5em;
      }
    }

    #filter-date {
      display: flex;
      flex-flow: row wrap;
      justify-content: space-between;
      gap: 0.5em;

      /* text-align: right; */

      >* {
        flex: 1 1 auto;
      }

      input[type="date"] {
        padding: 0.25em 0.5em;
        margin-inline-start: 0.25em;
      }
    }
  }

  .pagination {
    list-style: none;
    display: flex;
    flex-flow: row wrap;
    gap: 1ch;
    margin-block: 0.5em;

    max-width: max-content;

    & .pagination-link {
      font-size: 0.75rem;
      border: 1px solid lightgrey;

      padding: 0.025em 0.35em;
      text-decoration: none;
    }

    & .pagination-link.inactive {
      color: lightgrey;
      cursor: default;
    }
  }

  #table {
    max-height: 50dvh;
    overflow: auto;

    position: relative;
    z-index: -1;
    scroll-padding-top: 4em;

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

<?php
if ($errors) {  ?>
  <ul>
    <?php
    foreach ($errors as $error) {
      echo "<li>{$error}</li>";
    }
    ?>
  </ul>
<?php
}

if ($alerts) {  ?>
  <ul>
    <?php
    foreach ($alerts as $alert) {
      echo "<li>{$alert}</li>";
    }
    ?>
  </ul>
<?php
}
?>

<h1 style="margin-block-end: 0.5em;"><?= $this->get_page_info('title') ?></h1>

<div style="display: flex; flex-flow: row wrap; justify-content: start; gap: 0.5em; margin-block: 0.5em;">
  <form id="upload" action="<?= $this->get_url('/dashboard/leads/batch/add'); ?>" method="post" enctype="multipart/form-data" style="max-width: max-content;">
    <input type="hidden" name="origin_url" value="<?= $origin_url ?>">
    <input type="file" name="leads[]" accept=".csv" autocomplete="off" required multiple>
    <button type="submit" style="padding: 0.25em 0.5em;">Upload</button>
  </form>

  <form id="assign" action="<?= $this->get_url('/dashboard/leads/assign'); ?>" method="post" style="max-width: max-content;">
    <input type="hidden" name="origin_url" value="<?= $origin_url ?>">
    <button type="submit" style="padding: 0.25em 0.5em;">Assign</button>
  </form>

  <?php
  $valid_categories = ['absentee_owner', 'expired', 'frbo', 'fsbo'];
  $valid_areas = ['montgomery', 'auburn'];

  if (!empty($lead_category) && in_array($lead_category, $valid_categories) && !empty($lead_area) && in_array($lead_area, $valid_areas)) {
    $export_link = $this->get_url("/dashboard/leads/export/{$lead_category}/{$lead_area}?" . http_build_query($search_params));
    $export_button = <<<HTML
        <a id="export" href="$export_link" style="max-width: max-content;">
          <button type="button" style="padding: 0.25em 0.5em;">Export</button>
        </a>
      HTML;

    echo $export_button;
  }
  ?>

  <form id="clear" action="<?= $this->get_url('/dashboard/leads/clear'); ?>" method="post" style="max-width: max-content; margin-inline-start: auto;">
    <input type="hidden" name="origin_url" value="<?= $origin_url ?>">
    <button onclick="return confirm('Are you sure?');" type="submit" style="padding: 0.25em 0.5em; background-color: hsl(var(--bg-red),0.9); border-radius: 0.25em; border: 1px solid black; font-weight: bold; color: white;">Clear</button>
  </form>
</div>

<div id="leads-table">

  <form id="filter" action="<?= $this->url("/dashboard/leads") ?>">

    <div class="filter-group">
      <button type="button" data-show="false" onclick="this.dataset.show = this.dataset.show === 'false' ? 'true' : 'false';"><span>Listing Status</span><span>&#9662;</span></button>
      <ul>
        <li><label><input type="checkbox" name="filter_by[listing_status][]" value="Expired" <?= $is_checked('listing_status', 'Expired', $search_params); ?>><span>Expireds</span></label></li>
        <li><label><input type="checkbox" name="filter_by[listing_status][]" value="Withdrawn" <?= $is_checked('listing_status', 'Withdrawn', $search_params); ?>><span>Withdrawn</span></label></li>
        <li><label><input type="checkbox" name="filter_by[listing_status][]" value="Off Market" <?= $is_checked('listing_status', 'Off Market', $search_params); ?>><span>Off Market</span></label></li>
        <li><label><input type="checkbox" name="filter_by[listing_status][]" value="Cancelled" <?= $is_checked('listing_status', 'Cancelled', $search_params); ?>><span>Cancelled</span></label></li>
        <li><label><input type="checkbox" name="filter_by[listing_status][]" value="FRBO" <?= $is_checked('listing_status', 'FRBO', $search_params); ?>><span>FRBO</span></label></li>
        <li><label><input type="checkbox" name="filter_by[listing_status][]" value="FSBO" <?= $is_checked('listing_status', 'FSBO', $search_params); ?>><span>FSBO</span></label></li>
      </ul>
    </div>

    <div class="filter-group">
      <button type="button" data-show="false" onclick="this.dataset.show = this.dataset.show === 'false' ? 'true' : 'false';"><span>Import Status</span><span>&#9662;</span></button>
      <ul>
        <li><label><input type="checkbox" name="filter_by[import_lead][]" value="1" <?= $is_checked('import_lead', '1', $search_params); ?>><span>Import</span></label></li>
        <li><label><input type="checkbox" name="filter_by[import_lead][]" value="0" <?= $is_checked('import_lead', '0', $search_params); ?>><span>Do Not Import</span></label></li>
      </ul>
    </div>

    <div class="filter-group">
      <button type="button" data-show="false" onclick="this.dataset.show = this.dataset.show === 'false' ? 'true' : 'false';"><span>Assigned Area</span><span>&#9662;</span></button>
      <ul>
        <li><label><input type="checkbox" name="filter_by[assigned_area][]" value="montgomery" <?= $is_checked('assigned_area', 'montgomery', $search_params); ?>><span>Montgomery</span></label></li>
        <li><label><input type="checkbox" name="filter_by[assigned_area][]" value="auburn" <?= $is_checked('assigned_area', 'auburn', $search_params); ?>><span>Auburn</span></label></li>
      </ul>
    </div>

    <div class="filter-group">
      <button type="button" data-show="false" onclick="this.dataset.show = this.dataset.show === 'false' ? 'true' : 'false';"><span>Absentee Owner</span><span>&#9662;</span></button>
      <ul>
        <li><label><input type="checkbox" name="filter_by[absentee_owner][]" value="1" <?= $is_checked('absentee_owner', '1', $search_params); ?>><span>Yes</span></label></li>
        <li><label><input type="checkbox" name="filter_by[absentee_owner][]" value="0" <?= $is_checked('absentee_owner', '0', $search_params); ?>><span>No</span></label></li>
      </ul>
    </div>

    <div class="filter-group">
      <button type="button" data-show="false" onclick="this.dataset.show = this.dataset.show === 'false' ? 'true' : 'false';"><span>Lead Assignment </span><span>&#9662;</span></button>
      <ul>
        <li><label><input type="checkbox" name="filter_by[lead_assigned][]" value="1" <?= $is_checked('lead_assigned', '1', $search_params); ?>><span>Assigned</span></label></li>
        <li><label><input type="checkbox" name="filter_by[lead_assigned][]" value="0" <?= $is_checked('lead_assigned', '0', $search_params); ?>><span>Unassigned</span></label></li>
      </ul>
    </div>

    <div class="filter-group" id="filter-date">
      <label>Start Date: <input type="date" name="range[created_at][]" max="<?= (new DateTime)->format('Y-m-d') ?>" value="<?= $search_params['range']['created_at'][0] ?? '' ?>"></label>
      <label>End Date: <input type="date" name="range[created_at][]" max="<?= (new DateTime)->modify('+1 day')->format('Y-m-d') ?>" value="<?= $search_params['range']['created_at'][1] ?? '' ?>"></label>
    </div>

    <button type="submit">Filter</button>
  </form>

  <div style="position: relative; isolation: isolate; z-index: 1;">
    <?= $pagination_links ?>
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
      foreach ($leads['items'] as $index => $lead) {
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

        $toggle_import_lead_link = $this->get_url("/dashboard/leads/toggle/import_lead/{$lead_id}");
        $toggle_ao_link = $this->get_url("/dashboard/leads/toggle/absentee_owner/{$lead_id}");

        $row = <<<HTML
        <tr id="$row_number" data-ignore="$ignore_lead" data-lead-assigned="$is_lead_assigned" data-area-assigned="$is_area_assigned" data-absentee-owner="$is_absentee_owner" tabindex="0">
          <td><p>$row_number</p></td>
          <td class="vortex_id"><p>$vortex_id</p></td>
          <td class="import_lead" style="padding: 0;">
            <form action="$toggle_import_lead_link" method="post">
              <input type="hidden" name="origin_url" value="$origin_url">
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
            <form action="$toggle_ao_link" method="post">
              <input type="hidden" name="origin_url" value="$origin_url">
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
  <div>
    <?= $pagination_links ?>
  </div>
</div>