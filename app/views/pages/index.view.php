<h1>pages</h1>
<a href="<?php $this->url('/dashboard/pages/new') ?>">add page</a>

<table>
  <tr>
    <th>title</th>
    <th>slug</th>
    <th>date published</th>
  </tr>

  <?php
  $pages = $this->get_object('pages');

  foreach ($pages as $page) {
    $edit_url = "/dashboard/pages/{$page['id']}/edit";
    $edit_link = $this->get_url($edit_url);

    echo
    "<tr>
        <td><a href='{$edit_link}'>{$page['title']}</a></td>
        <td>{$page['slug']}</td>
        <td>{$page['created_at']}</td>
      </tr>";
  }
  ?>

</table>