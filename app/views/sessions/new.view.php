<?php
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

<h1>login page</h1>
<form action="<?php $this->url('/login') ?>" method="post">
  <input type="email" placeholder="email@domain.com" name="email">
  <input type="password" placeholder="password" name="password">
  <button type="submit">login</button>
</form>