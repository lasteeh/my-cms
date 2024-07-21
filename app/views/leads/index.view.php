<?php
$errors = $this->get_flash('errors');
$alerts = $this->get_flash('alerts');
$lead_category = $this->get_object('lead_category');
$lead_area = $this->get_object('lead_area');
$search_params = $this->get_object('search_params');

$total_pages = $search_params['total_pages'];
$current_page = $search_params['page'];


$search_url_params = http_build_query($search_params);

$protocol = $_SERVER['REQUEST_SCHEME'] . '://';
$domain_name = $_SERVER['HTTP_HOST'];
$request_uri = $_SERVER['REQUEST_URI'];
$current_url = str_replace(self::$ROOT_URL, "", $protocol . $domain_name . $request_uri);

$action_html = "";
switch ($lead_category) {
  case '':
    $form_action_link = $this->get_url('/dashboard/leads/batch/add');

    $action_html = <<<HTML
      <form action="$form_action_link" method="post" enctype="multipart/form-data" style="max-width: max-content; margin-inline-start: auto;">
        <input type="file" name="leads[]" accept=".csv" autocomplete="off" required multiple>
        <button type="submit">Upload</button>
      </form>
    HTML;
    break;
  case 'unassigned':
    $assign_link = $this->get_url("/dashboard/leads/assign?category=unassigned");
    $add_city_link = $this->get_url('/dashboard/cities');

    $counties = $this->get_object("counties");
    $county_options = "";
    foreach ($counties as $county) {
      $county_name = $county['name'];
      $county_id = $county['id'];
      $county_options .= <<<HTML
          <option value="$county_id">$county_name</option>
      HTML;
    }

    $action_html = <<<HTML
        <div style="display: flex; flex-flow: column nowrap;">
        <a style="margin-inline-end: auto;" href="$assign_link">Assign Leads</a>
        <a target="_blank" href="https://developers.google.com/maps/documentation/geocoding/overview">Google Geocoding API</a>
        <a target="_blank" href="https://tools.usps.com/zip-code-lookup.htm?bycitystate">ZIP Code Lookup | USPS</a>
        </div>
        <form id="add_city" action="$add_city_link" method="post" style="display: flex; width: min(600px, 100%); max-width: max-content; margin-inline-start: auto;">
          <div style="display: flex; flex-flow: row wrap; ">
          <input type="hidden" name="origin_url" value="$current_url">
          <input type="text" name="name" placeholder="City" required>
          <select name="state" required>
            <option value="" disabled>Select State</option>
            <option value="Alabama" selected>AL</option>
            <option value="Alaska">AK</option>
            <option value="Arizona">AZ</option>
            <option value="Arkansas">AR</option>
            <option value="California">CA</option>
            <option value="Colorado">CO</option>
            <option value="Connecticut">CT</option>
            <option value="Delaware">DE</option>
            <option value="Florida">FL</option>
            <option value="Georgia">GA</option>
            <option value="Hawaii">HI</option>
            <option value="Idaho">ID</option>
            <option value="Illinois">IL</option>
            <option value="Indiana">IN</option>
            <option value="Iowa">IA</option>
            <option value="Kansas">KS</option>
            <option value="Kentucky">KY</option>
            <option value="Louisiana">LA</option>
            <option value="Maine">ME</option>
            <option value="Maryland">MD</option>
            <option value="Massachusetts">MA</option>
            <option value="Michigan">MI</option>
            <option value="Minnesota">MN</option>
            <option value="Mississippi">MS</option>
            <option value="Missouri">MO</option>
            <option value="Montana">MT</option>
            <option value="Nebraska">NE</option>
            <option value="Nevada">NV</option>
            <option value="New Hampshire">NH</option>
            <option value="New Jersey">NJ</option>
            <option value="New Mexico">NM</option>
            <option value="New York">NY</option>
            <option value="North Carolina">NC</option>
            <option value="North Dakota">ND</option>
            <option value="Ohio">OH</option>
            <option value="Oklahoma">OK</option>
            <option value="Oregon">OR</option>
            <option value="Pennsylvania">PA</option>
            <option value="Rhode Island">RI</option>
            <option value="South Carolina">SC</option>
            <option value="South Dakota">SD</option>
            <option value="Tennessee">TN</option>
            <option value="Texas">TX</option>
            <option value="Utah">UT</option>
            <option value="Vermont">VT</option>
            <option value="Virginia">VA</option>
            <option value="Washington">WA</option>
            <option value="West Virginia">WV</option>
            <option value="Wisconsin">WI</option>
            <option value="Wyoming">WY</option>
          </select>

          <input type="text" name="zip_codes" value="" placeholder="Zip Codes" required>
          <select name="county_id" required>
            <option value="" disabled selected>Select County</option>
            $county_options
          </select>

          <input type="text" name="latitude" placeholder="Latitude">
          <input type="text" name="longitude" placeholder="Longitude">

          <input type="text" name="bound_nw" placeholder="Bound NW">
          <input type="text" name="bound_se" placeholder="Bound SW">

          <input type="text" name="viewport_nw" placeholder="Viewport NW">
          <input type="text" name="viewport_se" placeholder="Viewport SE">
          </div>
          <button style="display: inline-block;" type="submit">Add City</button>
        </form>
    HTML;
    break;
  case "absentee_owner":
  case "expired":
  case "frbo":
  case "fsbo":
    if (!empty($lead_area)) {
      $export_link = $this->get_url("/dashboard/leads/export/{$lead_area}/{$lead_category}");
      $action_html = <<<HTML
        <form action="$export_link" method="post">
          <input type="hidden" name="origin_url" value="$current_url">
          <button type="submit">Export</button>
        </form>
      HTML;
    }
    break;
}

function paginate(int $current_page = 1, int $total_pages = 1, array $search_params = [], int $maximum_pagination_links = 10)
{
  $html = "";

  // calculate the midpoint around the current page
  $midpoint = (int) floor($maximum_pagination_links / 2);

  // calculate the start and end points for pagination
  $start = max(1, $current_page - $midpoint);
  $end = min($total_pages, $start + $maximum_pagination_links - 1);

  // adjust the start again to ensure we show exactly $maximum_pagination_links links if possible
  $start = max(1, $end - $maximum_pagination_links + 1);

  if ($current_page > 1) {
    $search_params['page'] = $current_page - 1;
    $pagination_params = http_build_query($search_params);

    $html .= <<<HTML
      <a href="?$pagination_params">&laquo; Previous</a>
    HTML;
  }

  if ($start > 1) {
    $html .= <<<HTML
      <a href="?page=1">1</a>
      <span>...</span>
    HTML;
  }

  for ($i = $start; $i <= $end; $i++) {
    $search_params['page'] = $i;
    $pagination_params = http_build_query($search_params);

    if ($i == $current_page) {
      $html .= <<<HTML
        <span>$i</span>
      HTML;
    } else {
      $html .= <<<HTML
        <a href="?$pagination_params">$i</a>
      HTML;
    }
  }

  if ($end < $total_pages) {
    $html .= <<<HTML
      <span>...</span>
      <a href="?page=$total_pages">$total_pages</a>
    HTML;
  }

  if ($current_page < $total_pages) {
    $search_params['page'] = $current_page + 1;
    $pagination_params = http_build_query($search_params);
    $html .= <<<HTML
      <a href="?$pagination_params">Next &raquo;</a>
    HTML;
  }

  return $html;
}

function check(string $name, ?string $lead_category)
{
  $fields = [
    "vortex_id" => true,
    "import_lead" => true,
    "listing_status" => true,
    "name" => true,
    "phone" => true,
    "phone_2" => true,
    "phone_3" => true,
    "phone_4" => false,
    "phone_5" => false,
    "phone_6" => false,
    "phone_7" => false,
    "email" => true,
    "email_2" => false,
    "email_3" => false,
    "email_4" => false,
    "email_5" => false,
    "email_6" => false,
    "email_7" => false,
    "mailing_street" => true,
    "mailing_city" => true,
    "mailing_state" => true,
    "mailing_zip" => true,
    "list_price" => true,
    "status_date" => true,
    "mls_fsbo_id" => true,
    "standardized_mailing_street" => true,
    "absentee_owner" => true,
    "standardized_property_street" => true,
    "property_address" => true,
    "property_city" => true,
    "property_state" => true,
    "property_zip" => true,
    "property_county" => true,
    "assigned_area" => true,
    "source" => true,
    "pipeline" => true,
    "buyer_seller" => true,
    "agent_assigned" => true,
  ];


  switch ($lead_category) {
    case 'unassigned':
      $fields['listing_status'] = false;
      $fields['name'] = false;
      $fields['phone'] = false;
      $fields['phone_2'] = false;
      $fields['phone_3'] = false;
      $fields['email'] = false;
      $fields['mailing_street'] = false;
      $fields['mailing_city'] = false;
      $fields['mailing_zip'] = false;
      $fields['mailing_state'] = false;
      $fields['list_price'] = false;
      $fields['status_date'] = false;
      $fields['mls_fsbo_id'] = false;
      $fields['standardized_mailing_street'] = false;
      $fields['absentee_owner'] = false;
      $fields['standardized_property_street'] = false;
      $fields['property_address'] = false;
      break;
    case 'absentee_owner':
      $fields['vortex_id'] = false;
      $fields['import_lead'] = false;
      $fields['listing_status'] = false;
      $fields['name'] = false;
      $fields['phone'] = false;
      $fields['phone_2'] = false;
      $fields['phone_3'] = false;
      $fields['email'] = false;
      $fields['mailing_street'] = false;
      $fields['mailing_city'] = false;
      $fields['mailing_zip'] = false;
      $fields['list_price'] = false;
      $fields['status_date'] = false;
      $fields['mls_fsbo_id'] = false;
      $fields['property_address'] = false;
      $fields['property_city'] = false;
      $fields['property_zip'] = false;
      break;
    case 'frbo':
    case 'fsbo':
      $fields['standardized_mailing_street'] = false;
      $fields['absentee_owner'] = false;
      $fields['standardized_property_street'] = false;
      break;
  }
  return $fields[$name] ? "checked" : "";
}
?>

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

<h1><?php $this->page_info('title'); ?></h1>

<div class="actions" style="display: flex; flex-flow: row wrap; align-items: end; justify-content: flex-end; gap: 1em;">
  <?= $action_html; ?>
</div>

<style>
  form#add_city>div>* {
    flex: 1 1 auto;
    padding: 0.25em;
  }

  form#add_city>button {
    padding: 0.25em 0.5em;
  }

  .pagination {
    max-width: max-content;
    margin-block: 0.5em;
  }

  .pagination :where(a, span) {
    display: inline-block;
    border: 1px solid lightgrey;
    padding: 0.025em 0.35em;
    text-decoration: none;

    font-size: 0.75rem;
  }

  #table-container:has(#vortex_id:not(:checked)) .vortex_id,
  #table-container:has(#import_lead:not(:checked)) .import_lead,
  #table-container:has(#listing_status:not(:checked)) .listing_status,
  #table-container:has(#name:not(:checked)) .name,
  #table-container:has(#phone:not(:checked)) .phone,
  #table-container:has(#phone_2:not(:checked)) .phone_2,
  #table-container:has(#phone_3:not(:checked)) .phone_3,
  #table-container:has(#phone_4:not(:checked)) .phone_4,
  #table-container:has(#phone_5:not(:checked)) .phone_5,
  #table-container:has(#phone_6:not(:checked)) .phone_6,
  #table-container:has(#phone_7:not(:checked)) .phone_7,
  #table-container:has(#email:not(:checked)) .email,
  #table-container:has(#email_2:not(:checked)) .email_2,
  #table-container:has(#email_3:not(:checked)) .email_3,
  #table-container:has(#email_4:not(:checked)) .email_4,
  #table-container:has(#email_5:not(:checked)) .email_5,
  #table-container:has(#email_6:not(:checked)) .email_6,
  #table-container:has(#email_7:not(:checked)) .email_7,
  #table-container:has(#mailing_street:not(:checked)) .mailing_street,
  #table-container:has(#mailing_city:not(:checked)) .mailing_city,
  #table-container:has(#mailing_state:not(:checked)) .mailing_state,
  #table-container:has(#mailing_zip:not(:checked)) .mailing_zip,
  #table-container:has(#list_price:not(:checked)) .list_price,
  #table-container:has(#status_date:not(:checked)) .status_date,
  #table-container:has(#mls_fsbo_id:not(:checked)) .mls_fsbo_id,
  #table-container:has(#standardized_mailing_street:not(:checked)) .standardized_mailing_street,
  #table-container:has(#absentee_owner:not(:checked)) .absentee_owner,
  #table-container:has(#standardized_property_street:not(:checked)) .standardized_property_street,
  #table-container:has(#property_address:not(:checked)) .property_address,
  #table-container:has(#property_city:not(:checked)) .property_city,
  #table-container:has(#property_state:not(:checked)) .property_state,
  #table-container:has(#property_zip:not(:checked)) .property_zip,
  #table-container:has(#property_county:not(:checked)) .property_county,
  #table-container:has(#assigned_area:not(:checked)) .assigned_area,
  #table-container:has(#source:not(:checked)) .source,
  #table-container:has(#pipeline:not(:checked)) .pipeline,
  #table-container:has(#buyer_seller:not(:checked)) .buyer_seller,
  #table-container:has(#agent_assigned:not(:checked)) .agent_assigned {
    display: none;
  }

  #column_filters+form {
    position: absolute;
    top: 100%;
    right: 0%;
    display: flex;
    flex-flow: row wrap;
    align-items: stretch;
    justify-content: space-between;

    width: min(90%, 500px);
    padding: 0.5em;
    max-height: 50vh;
    overflow-y: auto;

    background-color: white;

    box-shadow: 1px 1px 2px hsl(0, 0%, 0%, 0.5);
  }

  #column_filters:not(:checked)+form {
    display: none;
  }

  #column_filters {
    display: none;
  }

  label[for="column_filters"] {
    background-color: hsl(0, 0%, 0%, 0.05);
    padding-block: 0.125em;
    padding-inline: 0.5em;
    border: 1px solid hsl(0, 0%, 0%, 1);
    border-radius: 0.125rem;
  }

  label[for="column_filters"]:hover {
    background-color: hsl(0, 0%, 0%, 0.1);
  }

  #column_filters+form label,
  #column_filters+form button[type="reset"] {
    flex: 1 1 33%;

    display: flex;
    align-items: center;
    gap: 0.25em;
    padding: 0.5em;

    font: inherit;

    background-color: transparent;
  }

  #column_filters+form label:hover,
  #column_filters+form button[type="reset"]:hover {
    background-color: hsl(0, 0%, 0%, 0.1);
  }

  #column_filters+form button[type="reset"] {
    border: 1px solid hsl(0, 0%, 0%, 0.1);
  }

  table {
    position: relative;
    text-align: left;
    border-spacing: 0;

    isolation: isolate;
  }

  table :where(th,
    td) {
    padding: 0.1em 0.75em;
    border: 1px solid darkgray;
  }

  tr:first-child {
    position: sticky;
    top: 0;
    background-color: lightgrey;
    z-index: 1;
  }

  :where(td, th):first-child {
    position: sticky;
    left: 0;
  }

  th:first-child {
    background-color: lightgrey;
  }

  td:first-child {
    padding: 0.5em;
    background-color: lightgrey;
    color: black;
  }

  td:first-child p {
    margin-inline-start: auto;
    font-size: 0.75rem;
  }


  tr:where([data-lead-assigned="false"]) {
    --bg-color: var(--bg-red);
    background-color: hsl(var(--bg-color), 0.2);
  }

  tr:where([data-lead-assigned="true"][data-area-assigned="false"], [data-ignore="true"]) {
    --bg-color: var(--bg-gray);
    background-color: hsl(var(--bg-color), 0.2);
    color: hsl(var(--bg-gray));

    & span {
      color: hsl(var(--bg-gray));
    }
  }

  tr:where([data-absentee-owner="true"]) td.absentee_owner {
    background-color: hsl(var(--bg-orange), 0.2);
  }

  tr:not(:first-child):hover {
    background-color: hsl(var(--bg-color, var(--bg-blue)), 0.7);
  }

  tr:focus {
    background-color: hsl(var(--bg-color, var(--bg-blue)), 0.5);
  }

  td:not(:has(a))>p {
    width: max-content;
  }

  td:has(a)>p,
  td:has(a)>p>a {
    display: block;
    width: 100%;
  }
</style>

<div id="table-container" style="isolation: isolate;">

  <div style="position: relative; display: flex; flex-flow: row wrap; justify-content: space-between; align-items: center;">
    <div class="pagination">
      <?= paginate($current_page, $total_pages, $search_params); ?>
    </div>

    <div style="margin-inline-start: auto; margin-block: 0.5em; z-index: 1;">
      <label for="column_filters" style="display: block; width: max-content; margin-inline-start: auto;">View Columns</label>
      <input type="checkbox" id="column_filters">
      <form method="dialog">
        <label><input type="checkbox" id="vortex_id" <?= check("vortex_id", $lead_category); ?>><span>Vortex ID</span></label>
        <label><input type="checkbox" id="import_lead" <?= check("import_lead", $lead_category); ?>><span>Import Status</span></label>
        <label><input type="checkbox" id="listing_status" <?= check("listing_status", $lead_category); ?>><span>Listing Status</span></label>
        <label><input type="checkbox" id="name" <?= check("name", $lead_category); ?>><span>Full Name</span></label>
        <label><input type="checkbox" id="phone" <?= check("phone", $lead_category); ?>><span>Cell Phone</span></label>
        <label><input type="checkbox" id="phone_2" <?= check("phone_2", $lead_category); ?>><span>Home Phone</span></label>
        <label><input type="checkbox" id="phone_3" <?= check("phone_3", $lead_category); ?>><span>Work Phone</span></label>
        <label><input type="checkbox" id="phone_4" <?= check("phone_4", $lead_category); ?>><span>Phone 4</span></label>
        <label><input type="checkbox" id="phone_5" <?= check("phone_5", $lead_category); ?>><span>Phone 5</span></label>
        <label><input type="checkbox" id="phone_6" <?= check("phone_6", $lead_category); ?>><span>Phone 6</span></label>
        <label><input type="checkbox" id="phone_7" <?= check("phone_7", $lead_category); ?>><span>Phone 7</span></label>
        <label><input type="checkbox" id="email" <?= check("email", $lead_category); ?>><span>Email</span></label>
        <label><input type="checkbox" id="email_2" <?= check("email_2", $lead_category); ?>><span>Email 2</span></label>
        <label><input type="checkbox" id="email_3" <?= check("email_3", $lead_category); ?>><span>Email 3</span></label>
        <label><input type="checkbox" id="email_4" <?= check("email_4", $lead_category); ?>><span>Email 4</span></label>
        <label><input type="checkbox" id="email_5" <?= check("email_5", $lead_category); ?>><span>Email 5</span></label>
        <label><input type="checkbox" id="email_6" <?= check("email_6", $lead_category); ?>><span>Email 6</span></label>
        <label><input type="checkbox" id="email_7" <?= check("email_7", $lead_category); ?>><span>Email 7</span></label>
        <label><input type="checkbox" id="mailing_street" <?= check("mailing_street", $lead_category); ?>><span>Street Address</span></label>
        <label><input type="checkbox" id="mailing_city" <?= check("mailing_city", $lead_category); ?>><span>City</span></label>
        <label><input type="checkbox" id="mailing_state" <?= check("mailing_state", $lead_category); ?>><span>State</span></label>
        <label><input type="checkbox" id="mailing_zip" <?= check("mailing_zip", $lead_category); ?>><span>Zip/Postal Code</span></label>
        <label><input type="checkbox" id="list_price" <?= check("list_price", $lead_category); ?>><span>List Price</span></label>
        <label><input type="checkbox" id="status_date" <?= check("status_date", $lead_category); ?>><span>Register Date</span></label>
        <label><input type="checkbox" id="mls_fsbo_id" <?= check("mls_fsbo_id", $lead_category); ?>><span>MLS/FSBO ID</span></label>
        <label><input type="checkbox" id="standardized_mailing_street" <?= check("standardized_mailing_street", $lead_category); ?>><span>Standardized Mailing Street</span></label>
        <label><input type="checkbox" id="absentee_owner" <?= check("absentee_owner", $lead_category); ?>><span>Absentee Owner</span></label>
        <label><input type="checkbox" id="standardized_property_street" <?= check("standardized_property_street", $lead_category); ?>><span>Standardized Property Street</span></label>
        <label><input type="checkbox" id="property_address" <?= check("property_address", $lead_category); ?>><span>Property Address</span></label>
        <label><input type="checkbox" id="property_city" <?= check("property_city", $lead_category); ?>><span>Property City</span></label>
        <label><input type="checkbox" id="property_state" <?= check("property_state", $lead_category); ?>><span>Property State</span></label>
        <label><input type="checkbox" id="property_zip" <?= check("property_zip", $lead_category); ?>><span>Property Zip</span></label>
        <label><input type="checkbox" id="property_county" <?= check("property_county", $lead_category); ?>><span>Property County</span></label>
        <label><input type="checkbox" id="assigned_area" <?= check("assigned_area", $lead_category); ?>><span>Assigned Area</span></label>
        <label><input type="checkbox" id="source" <?= check("source", $lead_category); ?>><span>Source</span></label>
        <label><input type="checkbox" id="pipeline" <?= check("pipeline", $lead_category); ?>><span>Pipeline</span></label>
        <label><input type="checkbox" id="buyer_seller" <?= check("buyer_seller", $lead_category); ?>><span>Buyer/Seller</span></label>
        <label><input type="checkbox" id="agent_assigned" <?= check("agent_assigned", $lead_category); ?>><span>Agent Assigned</span></label>
        <button type="reset">Reset</button>
      </form>
    </div>
  </div>

  <div style="position: relative; width: 100%; min-height: min-content; height: calc(75dvh - 7em); overflow: auto; border: 1px solid gray; resize: vertical; scroll-padding-top: 4em;">
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
      $leads = $this->get_object('leads');

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

        $toggle_ao_link = $this->get_url("/dashboard/leads/toggle/absentee_owner/{$lead_id}");
        $toggle_import_lead_link = $this->get_url("/dashboard/leads/toggle/import_lead/{$lead_id}");

        $row = <<<HTML
        <tr id="$row_number" data-ignore="$ignore_lead" data-lead-assigned="$is_lead_assigned" data-area-assigned="$is_area_assigned" data-absentee-owner="$is_absentee_owner" tabindex="0">
          <td><p>$row_number</p></td>
          <td class="vortex_id"><p>$vortex_id</p></td>
          <td class="import_lead" style="padding: 0;">
            <form action="$toggle_import_lead_link" method="post">
              <input type="hidden" name="origin_url" value="$current_url">
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
              <input type="hidden" name="origin_url" value="$current_url">
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

  <div class="pagination">
    <?= paginate($current_page, $total_pages, $search_params); ?>
  </div>
</div>