<h1>pages</h1>
<a href="<?php $this->url('/dashboard/pages/new') ?>">add page</a>

<table>
  <tr>
    <th>title</th>
    <th>slug</th>
    <th>parent page</th>
    <th>date published</th>
  </tr>

  <?php
  $pages = $this->get_object('pages');
  $page_lookup = [];

  foreach ($pages as $page) {
    $page_lookup[$page['id']] = $page;
  }

  foreach ($pages as $page) {
    $edit_url = "/dashboard/pages/{$page['id']}/edit";
    $edit_link = $this->get_url($edit_url);

    $parent_title = '';
    if ($page['parent_id'] !== null && isset($page_lookup[$page['parent_id']])) {
      $parent_title = $page_lookup[$page['parent_id']]['title'];
    }

    echo
    "<tr>
        <td><a href='{$edit_link}'>{$page['title']}</a></td>
        <td>{$page['slug']}</td>
        <td>{$parent_title}</td>
        <td>{$page['created_at']}</td>
      </tr>";
  }
  ?>

</table>