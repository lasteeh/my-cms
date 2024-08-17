<?php
$current_post = $this->get_object('current_post');
$edit_uri = "/dashboard/posts/{$current_post->id}";
$edit_link = $this->get_url($edit_uri);
$delete_uri = "/dashboard/posts/{$current_post->id}/delete";
$delete_link = $this->get_url($delete_uri);
?>

<h1>edit post</h1>
<form action="<?php echo $edit_link ?>" method="post">
  <input type="text" value="<?php echo $current_post->title ?>" placeholder="title" name="title">
  <input type="text" value="<?php echo $current_post->slug ?>" placeholder="slug" name="slug">
  <input type="text" value="<?php echo $current_post->sub_title ?>" placeholder="sub title" name="sub_title">
  <input type="text" value="<?php echo $current_post->description ?>" placeholder="description" name="description">
  <input type="text" value="<?php echo $current_post->excerpt ?>" placeholder="excerpt" name="excerpt">
  <input type="text" value="<?php echo $current_post->content ?>" placeholder="content" name="content">
  <input type="text" value="<?php echo $current_post->custom_css ?>" placeholder="custom css" name="custom_css">
  <input type="text" value="<?php echo $current_post->custom_js ?>" placeholder="custom js" name="custom_js">
  <button type="submit">update</button>
</form>

<form action="<?php echo $delete_link ?>" method="post">
  <button type="submit">delete</button>
</form>