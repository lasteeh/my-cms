<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $this->page_title; ?></title>
</head>

<body>
  <?php
  if (isset($_SESSION['user_id'])) {
  ?>
    <p> <?php echo ($_SESSION['user_id']); ?></p>
  <?php
  }
  ?>
  <?php include $view_file; ?>
</body>

</html>