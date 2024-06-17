<h1>Standardized City Details</h1>

<table style="text-align: left; border-spacing: 1em 0.25em;">
  <tr>
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
  </tr>

  <?php
  $cities = $this->get_object('cities');

  foreach ($cities as $city) {
    echo
    "<tr>
        <td>{$city['name']}</td>
        <td>{$city['state']}</td>
        <td  style='max-width: 25ch; max-height: 1ch; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;'>{$city['zip_codes']}</td>
        <td>{$city['county_name']}</td>
        <td>{$city['latitude']}</td>
        <td>{$city['longitude']}</td>
        <td>{$city['bound_nw']}</td>
        <td>{$city['bound_se']}</td>
        <td>{$city['viewport_nw']}</td>
        <td>{$city['viewport_se']}</td>
      </tr>";
  }
  ?>

</table>