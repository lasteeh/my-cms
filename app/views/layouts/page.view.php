<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php $this->page_info('page_title'); ?></title>
  <link id="page-style" rel="stylesheet" href="<?php $this->stylesheet('page'); ?>">
  <?php $this->inline_style(); ?>
  <script id="page-script" src="<?php $this->script('page'); ?>" defer></script>
  <?php $this->inline_script(); ?>
</head>

<body class="page">
  <header>
  </header>
  <main>
    <?php include $view_file; ?>
  </main>
  <footer></footer>
</body>

</html>