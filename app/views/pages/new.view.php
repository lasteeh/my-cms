<?php
$pages = $this->get_object('pages');
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

<h1>new page</h1>
<form action="<?php $this->url('/dashboard/pages') ?>" method="post">
  <input type="text" placeholder="title" name="title">
  <input type="text" placeholder="slug" name="slug">
  <input type="text" placeholder="sub title" name="sub_title">
  <input type="text" placeholder="description" name="description">
  <input type="text" placeholder="content" name="content">
  <select name="parent_id">
    <option value="" selected disabled>Parent Page</option>
    <option value="">No Parent</option>
    <?php
    foreach ($pages as $page) {
      echo "<option value=\"{$page["id"]}\" >{$page["title"]}</option>";
    }
    ?>
  </select>
  <button type="submit">publish</button>
</form>