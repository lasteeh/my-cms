<?php
$errors = $this->get_flash('errors');
$alerts = $this->get_flash('alerts');
$current_page = $this->get_object('current_page');
$total_pages = $this->get_object('total_pages');
$url_params = $this->get_object('url_params');
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

<h1>Leads</h1>

<form action="<?php $this->url('/dashboard/leads/batch_add'); ?>" method="post" enctype="multipart/form-data" style="max-width: max-content; margin-inline-start: auto;">
  <input type="file" name="leads[]" accept=".csv" autocomplete="off" required multiple>
  <button type="submit">Upload</button>
</form>


<div class="actions" style="display: flex; flex-flow: row wrap; justify-content: flex-start; gap: 1em; margin-block: 1em;">
  <a href="<?php $this->url("/dashboard/leads"); ?>">Show All</a>
  <a href="<?php $this->url("/dashboard/leads/unassigned"); ?>">Unassigned Leads</a>
  <a href="<?php $this->url("/dashboard/leads/expireds"); ?>">Expired Leads</a>
  <a href="<?php $this->url("/dashboard/leads/frbo"); ?>">FRBO Leads</a>
  <a href="<?php $this->url("/dashboard/leads/fsbo"); ?>">FSBO Leads</a>
  <a href="<?php $this->url("/dashboard/leads/assign?{$url_params}"); ?>" style="margin-inline-start: auto;">Assign Leads</a>
</div>

<div>
  <?php if ($current_page > 1 && $total_pages > 1) : ?>
    <a href="?<?= $url_params ?>&page=<?= $current_page - 1 ?>">&laquo; Previous</a>
  <?php endif; ?>

  <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
    <?php if ($i == $current_page || $total_pages === 1) : ?>
      <span><?= $i ?></span>
    <?php else : ?>
      <a href="?<?= $url_params ?>&page=<?= $i ?>"><?= $i ?></a>
    <?php endif; ?>
  <?php endfor; ?>

  <?php if ($current_page < $total_pages) : ?>
    <a href="?<?= $url_params ?>&page=<?= $current_page + 1 ?>">Next &raquo;</a>
  <?php endif; ?>
</div>

<style>
  table {
    position: relative;
    text-align: left;
    border-spacing: 0;

    isolation: isolate;
  }

  table :where(th,
    td) {
    padding: 0.5em 0.75em;
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

  tr:not(:first-child):hover {
    background-color: hsl(120, 73%, 75%, 0.1);
  }

  td>p {
    width: max-content;
  }
</style>
<div style="position: relative; width: 100%; min-height: min-content; max-height: calc(75dvh - 4em); overflow: auto; border: 1px solid gray;">
  <table>
    <tr>
      <th></th>
      <th>Vortex ID</th>
      <th>Import Status</th>
      <th>Listing Status</th>
      <th>Full Name</th>
      <th>Cell Phone</th>
      <th>Home Phone</th>
      <th>Work Phone</th>
      <th>Email</th>
      <th>Street Address</th>
      <th>City</th>
      <th>State</th>
      <th>Zip/Postal Code</th>
      <th>List Price</th>
      <th>Register Date</th>
      <th>MLS/FSBO ID</th>
      <th>Standardized Mailing Street</th>
      <th>Absentee Owner</th>
      <th>Standardized Property Street</th>
      <th>Property Address</th>
      <th>Property City</th>
      <th>Property State</th>
      <th>Property Zip</th>
      <th>Property County</th>
      <th>Assigned Area</th>
      <th>Source</th>
      <th>Pipeline</th>
      <th>Buyer/Seller</th>
      <th>Agent Assigned</th>
    </tr>

    <?php
    $leads = $this->get_object('leads');

    foreach ($leads as $index => $lead) {
      $row_number = $index + 1;
      $vortex_id = $lead['vortex_id'];
      $lead_imported = $lead['lead_imported'];
      $lead_assigned = $lead['lead_assigned'];
      $lead_processed = $lead['lead_processed'];
      $listing_status = $lead['listing_status'];
      $name = $lead['name'];
      $phone = $lead['phone'];
      $phone_2 = $lead['phone_2'];
      $phone_3 = $lead['phone_3'];
      $email = $lead['email'];
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

      $row = <<<HTML
        <tr>
          <td><p>$row_number</p></td>
          <td><p>$vortex_id</p></td>
          <td><p>$lead_imported</p></td>
          <td><p>$listing_status</p></td>
          <td><p>$name</p></td>
          <td><p>$phone</p></td>
          <td><p>$phone_2</p></td>
          <td><p>$phone_3</p></td>
          <td><p>$email</p></td>
          <td><p>$mailing_street</p></td>
          <td><p>$mailing_city</p></td>
          <td><p>$mailing_state</p></td>
          <td><p>$mailing_zip</p></td>
          <td><p>$list_price</p></td>
          <td><p>$status_date</p></td>
          <td><p>$mls_fsbo_id</p></td>
          <td><p>$standardized_mailing_street</p></td>
          <td><p>$absentee_owner</p></td>
          <td><p>$standardized_property_street</p></td>
          <td><p>$property_address</p></td>
          <td><p>$property_city</p></td>
          <td><p>$property_state</p></td>
          <td><p>$property_zip</p></td>
          <td><p>$property_county</p></td>
          <td><p>$assigned_area</p></td>
          <td><p>$source</p></td>
          <td><p>$pipeline</p></td>
          <td><p>$buyer_seller</p></td>
          <td><p>$agent_assigned</p></td>
        </tr>
      HTML;
      echo $row;
    }
    ?>

  </table>
</div>

<div>
  <?php if ($current_page > 1 && $total_pages > 1) : ?>
    <a href="?<?= $url_params ?>&page=<?= $current_page - 1 ?>">&laquo; Previous</a>
  <?php endif; ?>

  <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
    <?php if ($i == $current_page || $total_pages === 1) : ?>
      <span><?= $i ?></span>
    <?php else : ?>
      <a href="?<?= $url_params ?>&page=<?= $i ?>"><?= $i ?></a>
    <?php endif; ?>
  <?php endfor; ?>

  <?php if ($current_page < $total_pages) : ?>
    <a href="?<?= $url_params ?>&page=<?= $current_page + 1 ?>">Next &raquo;</a>
  <?php endif; ?>
</div>