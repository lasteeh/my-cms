<h1>posts</h1>
<a href="<?php $this->url('/dashboard/posts/new') ?>">add post</a>

<table>
  <tr>
    <th>title</th>
    <th>slug</th>
    <th>date published</th>
  </tr>

  <?php
  $posts = $this->get_object('posts');

  foreach ($posts as $post) {
    $edit_url = "/dashboard/posts/{$post['id']}/edit";
    $edit_link = $this->get_url($edit_url);

    echo
    "<tr>
        <td><a href='{$edit_link}'>{$post['title']}</a></td>
        <td>{$post['slug']}</td>
        <td>{$post['created_at']}</td>
      </tr>";
  }
  ?>

</table>