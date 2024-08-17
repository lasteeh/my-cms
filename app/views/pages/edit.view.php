<?php
$current_page = $this->get_object('current_page');
$pages = $this->get_object('pages');
$edit_uri = "/dashboard/pages/{$current_page->id}";
$edit_link = $this->get_url($edit_uri);
$delete_uri = "/dashboard/pages/{$current_page->id}/delete";
$delete_link = $this->get_url($delete_uri);
?>

<h1>edit page</h1>
<form action="<?php echo $edit_link ?>" method="post">
  <input type="text" value="<?php echo $current_page->title ?>" placeholder="title" name="title">
  <input type="text" value="<?php echo $current_page->slug ?>" placeholder="slug" name="slug">
  <input type="text" value="<?php echo $current_page->sub_title ?>" placeholder="sub title" name="sub_title">
  <input type="text" value="<?php echo $current_page->description ?>" placeholder="description" name="description">
  <input type="text" value="<?php echo $current_page->content ?>" placeholder="content" name="content">
  <input type="text" value="<?php echo $current_page->custom_css ?>" placeholder="custom css" name="custom_css">
  <input type="text" value="<?php echo $current_page->custom_js ?>" placeholder="custom js" name="custom_js">
  <select name="parent_id">
    <option value="">No Parent</option>
    <?php
    foreach ($pages as $page) {
      $selected = ((string)$current_page->parent_id === (string)$page["id"]) ? "selected" : "";
      echo "<option value=\"{$page["id"]}\" {$selected} >{$page["title"]}</option>";
    }
    ?>
  </select>
  <button type="submit">update</button>
</form>
<form action="<?php echo $delete_link ?>" method="post">
  <button type="submit">delete</button>
</form>