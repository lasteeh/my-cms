<ul>
  <?php
  foreach ($this->ERRORS as $error) {
    echo "<li>{$error}</li>";
  }
  ?>
</ul>
<h1>new page</h1>
<form action="<?php $this->url('/dashboard/pages') ?>" method="post">
  <input type="text" placeholder="title" name="title">
  <input type="text" placeholder="slug" name="slug">
  <input type="text" placeholder="sub title" name="sub_title">
  <input type="text" placeholder="description" name="description">
  <input type="text" placeholder="content" name="content">
  <button type="submit">publish</button>
</form>