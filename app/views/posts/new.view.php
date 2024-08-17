<h1>new post</h1>
<form action="<?php $this->url('/dashboard/posts') ?>" method="post">
  <input type="text" placeholder="title" name="title">
  <input type="text" placeholder="slug" name="slug">
  <input type="text" placeholder="sub title" name="sub_title">
  <input type="text" placeholder="description" name="description">
  <input type="text" placeholder="excerpt" name="excerpt">
  <input type="text" placeholder="content" name="content">
  <input type="text" placeholder="custom css" name="custom_css">
  <input type="text" placeholder="custom js" name="custom_js">
  <button type="submit">publish</button>
</form>