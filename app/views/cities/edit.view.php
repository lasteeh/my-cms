<?php
$current_city = $this->get_object('current_city');
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

<h1>Edit <?= $current_city->name; ?></h1>

<style>
  form>* {
    vertical-align: top;
  }
</style>

<?php
$name = $current_city->name;
$zip_codes = $current_city->zip_codes;
$latitude = $current_city->latitude;
$longitude = $current_city->longitude;
$bound_nw = $current_city->bound_nw;
$bound_se = $current_city->bound_se;
$viewport_nw = $current_city->viewport_nw;
$viewport_se = $current_city->viewport_se;
?>
<form action="<?php $this->url("/dashboard/cities/{$current_city->id}"); ?>" method="post">
  <div style="display: inline-block">
    <input type="text" name="name" placeholder="City" value="<?= $name ?>" required>
    <select name="state" required>
      <option value="" disabled selected>Select State</option>
      <?php
      $states = config('mrcleads.states') ?? [];

      foreach ($states as $key => $state) {
        $is_selected = $current_city->state === $state ? 'selected' : '';
        $option = <<<HTML
            <option value="$state" $is_selected>$key</option>
          HTML;

        echo $option;
      }
      ?>
    </select>
    <br />
    <input type="text" name="zip_codes" placeholder="Zip Codes" value="<?= $zip_codes ?>" required>
    <select name="county_id" required>
      <option value="" disabled selected>Select County</option>
      <?php
      $counties = $this->get_object("counties");

      foreach ($counties as $county) {
        $county_name = $county['name'];
        $county_id = $county['id'];
        $is_selected = $current_city->county_id === $county_id ? 'selected' : '';
        $option = <<<HTML
          <option value="$county_id" $is_selected>$county_name</option>
        HTML;

        echo $option;
      }
      ?>
    </select>
    <br />
    <input type="text" name="latitude" placeholder="Latitude" value="<?= $latitude ?>">
    <input type="text" name="longitude" placeholder="Longitude" value="<?= $longitude ?>">
    <br />
    <input type="text" name="bound_nw" placeholder="Bound NW" value="<?= $bound_nw ?>">
    <input type="text" name="bound_se" placeholder="Bound SW" value="<?= $bound_se ?>">
    <br />
    <input type="text" name="viewport_nw" placeholder="Viewport NW" value="<?= $viewport_nw ?>">
    <input type="text" name="viewport_se" placeholder="Viewport SE" value="<?= $viewport_se ?>">
  </div>
  <button type="submit">Update City</button>
</form>