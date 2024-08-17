<h1 style="margin-block-end: 0.5em;">Standardized City Details</h1>


<div style="display: flex; flex-flow: row wrap; align-items: end; justify-content: flex-start; gap: 1em; margin-block-end: 1em;">
  <div style="display: flex; flex-flow: column nowrap;">
    <a target="_blank" href="https://developers.google.com/maps/documentation/geocoding/overview">Google Geocoding API</a>
    <a target="_blank" href="https://tools.usps.com/zip-code-lookup.htm?bycitystate">ZIP Code Lookup | USPS</a>
  </div>
  <form id="add_city" action="<?php $this->url('/dashboard/cities'); ?>" method="post" style="display: flex; width: min(600px, 100%); max-width: max-content; margin-inline-start: auto;">
    <div style="display: flex; flex-flow: row wrap; ">
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
        <?php
        $counties = $this->get_object("counties");

        foreach ($counties as $county) {
          $county_name = $county['name'];
          $county_id = $county['id'];
          $option = <<<HTML
          <option value="$county_id">$county_name</option>
        HTML;

          echo $option;
        }
        ?>
      </select>

      <input type="text" name="latitude" placeholder="Latitude">
      <input type="text" name="longitude" placeholder="Longitude">

      <input type="text" name="bound_nw" placeholder="Bound NW">
      <input type="text" name="bound_se" placeholder="Bound SW">

      <input type="text" name="viewport_nw" placeholder="Viewport NW">
      <input type="text" name="viewport_se" placeholder="Viewport SE">
    </div>
    <button type="submit">Add City</button>
  </form>
</div>

<style>
  form#add_city>div>* {
    flex: 1 1 auto;
    padding: 0.25em;
  }

  form#add_city>button {
    padding: 0.25em 0.5em;
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

  :where(td, th):nth-child(2) {
    position: sticky;
    left: 0.5em;
  }

  th:nth-child(2) {
    background-color: lightgray;
  }

  td:nth-child(2) {
    background-color: var(--bg-hover, white);
  }

  :where(td, th):last-child {
    position: sticky;
    right: 0;
  }

  :where(td, th):where(:first-child, :last-child) {
    background-color: lightgrey;
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

  tr:where([data-lead-assigned="true"][data-area-assigned="false"]) {
    --bg-color: var(--bg-gray);
    background-color: hsl(var(--bg-color), 0.2);
    color: hsl(var(--bg-gray));
  }

  tr:where([data-absentee-owner="true"]) td.absentee_owner {
    background-color: hsl(var(--bg-orange), 0.2);
  }

  tr:not(:first-child):hover {
    --bg-hover: hsl(var(--bg-color, var(--bg-blue)), 1);
    background-color: hsl(var(--bg-color, var(--bg-blue)), 1);
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
<div style="width: 100%; min-height: 25dvh; max-height: calc(75dvh - 6em); overflow: auto; border: 1px solid gray;">
  <table>
    <tr>
      <th></th>
      <th>City</th>
      <th>State</th>
      <th>Zip Code</th>
      <th>County</th>
      <th>Latitude</th>
      <th>Longitude</th>
      <th>Bound NW</th>
      <th>Bound SE</th>
      <th>Viewport NW</th>
      <th>Viewport SE</th>
      <th></th>
    </tr>

    <?php
    $cities = $this->get_object('cities');

    foreach ($cities as $index => $city) {

      $edit_link = $this->get_url("/dashboard/cities/{$city['id']}/edit");

      $row_number = $index;
      $city_name = $city['name'];
      $city_state = $city['state'];
      $city_zip = $city['zip_codes'];
      $city_county = $city['county_name'];
      $city_latitude = $city['latitude'];
      $city_longitude = $city['longitude'];
      $city_bound_nw = $city['bound_nw'];
      $city_bound_se = $city['bound_se'];
      $city_viewport_nw = $city['viewport_nw'];
      $city_viewport_se = $city['viewport_se'];

      $row = <<<HTML
        <tr>
          <td><p>$row_number</p></td>
          <td><p>$city_name</p></td>
          <td><p>$city_state</p></td>
          <td><p style='max-width: 25ch; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;'>$city_zip</p></td>
          <td><p>$city_county</p></td>
          <td><p>$city_latitude</p></td>
          <td><p>$city_longitude</p></td>
          <td><p>$city_bound_nw</p></td>
          <td><p>$city_bound_se</p></td>
          <td><p>$city_viewport_nw</p></td>
          <td><p>$city_viewport_se</p></td>
          <td><a href="$edit_link">&#9998;</a></td>
        </tr>
      HTML;

      echo $row;
    }
    ?>

  </table>
</div>