<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php $this->page_info('title'); ?></title>
  <link id="app-style" rel="stylesheet" href="<?php $this->stylesheet('application') ?>">
  <script id="app-script" src="<?php $this->script('application') ?>" defer></script>
</head>

<body class="application">
  <header>
    <h1>CMS App</h1>
  </header>
  <main>
    <?php include $view_file; ?>
  </main>
  <aside>
    <nav>
      <menu>
        <li><a href="<?php $this->url('/dashboard') ?>">Dashboard</a></li>
        <li><a href="<?php $this->url('/dashboard/posts') ?>">Posts</a></li>
        <li><a href="<?php $this->url('/dashboard/pages') ?>">Pages</a></li>
        <li><a href="<?php $this->url('/logout') ?>">Logout</a></li>
      </menu>
    </nav>
  </aside>
  <footer></footer>
</body>

</html>