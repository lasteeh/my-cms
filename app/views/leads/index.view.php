<?php
$errors = $this->get_flash('errors');
$alerts = $this->get_flash('alerts');
$current_page = $this->get_object('current_page');
$total_pages = $this->get_object('total_pages');
$sort_order = $this->get_object('sort_order');
$sort_by = $this->get_object('sort_by');
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
  <input type="file" name="leads[]" id="leads" accept=".csv" autocomplete="off" required multiple>
  <button type="submit">Upload</button>
</form>

<div style="display: flex; flex-flow: row wrap; justify-content: flex-end;">
  <a href="<?php $this->url('/dashboard/leads/assign'); ?>">Assign Leads</a>
</div>

<div>
  <?php if ($current_page > 1) : ?>
    <a href="?page=<?= $current_page - 1 ?>&sort_order=<?= $sort_order ?>&sort_by=<?= $sort_by ?>">&laquo; Previous</a>
  <?php endif; ?>

  <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
    <?php if ($i == $current_page) : ?>
      <span><?= $i ?></span>
    <?php else : ?>
      <a href="?page=<?= $i ?>&sort_order=<?= $sort_order ?>&sort_by=<?= $sort_by ?>"><?= $i ?></a>
    <?php endif; ?>
  <?php endfor; ?>

  <?php if ($current_page < $total_pages) : ?>
    <a href="?page=<?= $current_page + 1 ?>&sort_order=<?= $sort_order ?>&sort_by=<?= $sort_by ?>">Next &raquo;</a>
  <?php endif; ?>
</div>
<style>
  table :where(th,
    td) {
    padding: 0.5em 0.75em;
    border: 1px solid darkgray;
  }
</style>
<div style="width: 100%; min-height: 25dvh; max-height: calc(75dvh - 2em); overflow: auto; border: 1px solid gray;">
  <table style="position: relative; text-align: left; border-spacing: 0;">
    <tr style="position: sticky; top: 0; background-color: lightgray;">
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

    foreach ($leads as $lead) {
      $vortex_id = $lead['vortex_id'];
      $lead_imported = $lead['lead_imported'];
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

      $warning_color = "darkred";
      $ignore_color = "lightgray";

      $missing_county_info = $lead['property_county'] === "MISSING COUNTY INFO" ? true : false;
      $area_not_assigned = $lead['assigned_area'] === "IGNORE ROW" ? true : false;

      $background_color = "transparent";
      $color = "black";
      if ($area_not_assigned) {
        $background_color = $ignore_color;
        $color = "darkgray";
      }
      if ($missing_county_info) {
        $background_color = $warning_color;
        $color = "white";
      }

      $row = <<<HTML
        <tr style="background-color: $background_color; color: $color;">
          <td>$vortex_id</td>
          <td>$lead_imported</td>
          <td>$listing_status</td>
          <td>$name</td>
          <td>$phone</td>
          <td>$phone_2</td>
          <td>$phone_3</td>
          <td>$email</td>
          <td>$mailing_street</td>
          <td>$mailing_city</td>
          <td>$mailing_state</td>
          <td>$mailing_zip</td>
          <td>$list_price</td>
          <td>$status_date</td>
          <td>$mls_fsbo_id</td>
          <td>$standardized_mailing_street</td>
          <td>$absentee_owner</td>
          <td>$standardized_property_street</td>
          <td>$property_address</td>
          <td>$property_city</td>
          <td>$property_state</td>
          <td>$property_zip</td>
          <td>$property_county</td>
          <td>$assigned_area</td>
          <td>$source</td>
          <td>$pipeline</td>
          <td>$buyer_seller</td>
          <td>$agent_assigned</td>
        </tr>
      HTML;
      echo $row;
    }
    ?>

  </table>
</div>

<div>
  <?php if ($current_page > 1) : ?>
    <a href="?page=<?= $current_page - 1 ?>&sort_order=<?= $sort_order ?>&sort_by=<?= $sort_by ?>">&laquo; Previous</a>
  <?php endif; ?>

  <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
    <?php if ($i == $current_page) : ?>
      <span><?= $i ?></span>
    <?php else : ?>
      <a href="?page=<?= $i ?>&sort_order=<?= $sort_order ?>&sort_by=<?= $sort_by ?>"><?= $i ?></a>
    <?php endif; ?>
  <?php endfor; ?>

  <?php if ($current_page < $total_pages) : ?>
    <a href="?page=<?= $current_page + 1 ?>&sort_order=<?= $sort_order ?>&sort_by=<?= $sort_by ?>">Next &raquo;</a>
  <?php endif; ?>
</div>