<ul><?php
    foreach ($this->ERRORS as $error) {
      echo "<li>{$error}</li>";
    }
    ?></ul>

<h1>register page</h1>
<form action="<?php $this->url('/users'); ?>" method="post">
  <input type="email" placeholder="email@domain.com" name="email">
  <input type="password" placeholder="password" name="password">
  <input type="password" placeholder="password confirmation" name="password_confirmation">
  <button type="submit">register</button>
</form>