<?php
$page = $this->get_object('page');
$edit_url = "/dashboard/pages/{$page->id}";
$edit_link = $this->get_url($edit_url);
?>
<ul>
  <?php
  foreach ($this->ERRORS as $error) {
    echo "<li>{$error}</li>";
  }
  ?>
</ul>
<h1>edit page</h1>
<form action="<?php echo $edit_link ?>" method="post">
  <input type="text" value="<?php echo $page->title ?>" placeholder="title" name="title">
  <input type="text" value="<?php echo $page->slug ?>" placeholder="slug" name="slug">
  <input type="text" value="<?php echo $page->sub_title ?>" placeholder="sub title" name="sub_title">
  <input type="text" value="<?php echo $page->description ?>" placeholder="description" name="description">
  <input type="text" value="<?php echo $page->content ?>" placeholder="content" name="content">
  <button type="submit">update</button>
</form>