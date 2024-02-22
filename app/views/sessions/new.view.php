<ul>
  <?php
  foreach ($this->ERRORS as $error) {
    echo "<li>{$error}</li>";
  }
  ?>
</ul>

<h1>login page</h1>
<form action="<?php $this->url('/login') ?>" method="post">
  <input type="email" placeholder="email@domain.com" name="email">
  <input type="password" placeholder="password" name="password">
  <button type="submit">login</button>
</form>