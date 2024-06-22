<?php
$errors = $this->get_flash('errors');
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
?>

<h1>Leads</h1>

<form action="<?php $this->url('/dashboard/leads/batch_add'); ?>" method="post" enctype="multipart/form-data" style="max-width: max-content; margin-inline-start: auto;">
  <input type="file" name="files[]" id="files" accept=".csv" autocomplete="off" required multiple>
  <button type="submit">Upload</button>
</form>

<div style="width: 100%; min-height: 25dvh; overflow: auto; border: 1px solid gray;">
  <table style="text-align: left; border-spacing: 1em 0.25em;">
    <tr>
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
      <th>Absentee Owner</th>
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
      echo
      "<tr>
        <td>{$lead['vortex_id']}</td>
        <td>{$lead['import_status']}</td>
        <td>{$lead['listing_status']}</td>
        <td>{$lead['name']}</td>
        <td>{$lead['phone']}</td>
        <td>{$lead['phone_2']}</td>
        <td>{$lead['phone_3']}</td>
        <td>{$lead['email']}</td>
        <td>{$lead['mailing_street']}</td>
        <td>{$lead['mailing_city']}</td>
        <td>{$lead['mailing_state']}</td>
        <td>{$lead['mailing_zip']}</td>
        <td>{$lead['listing_price']}</td>
        <td>{$lead['status_date']}</td>
        <td>{$lead['mls_fsbo_id']}</td>
        <td>{$lead['absentee_owner']}</td>
        <td>{$lead['property_address']}</td>
        <td>{$lead['property_city']}</td>
        <td>{$lead['property_state']}</td>
        <td>{$lead['property_zip']}</td>
        <td>{$lead['property_county']}</td>
        <td>{$lead['assigned_area']}</td>
        <td>{$lead['source']}</td>
        <td>{$lead['pipeline']}</td>
        <td>{$lead['buyer_seller']}</td>
        <td>{$lead['agent_assigned']}</td>
      </tr>";
    }
    ?>

  </table>
</div>