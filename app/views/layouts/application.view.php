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
    <h1>MRCLeads</h1>
  </header>
  <main>
    <?php include $view_file; ?>
  </main>
  <aside>
    <nav style="height: 100%;">
      <menu style="display: flex; flex-flow: column nowrap; height: 100%;">
        <li style="margin-block-end: 2em;"><a href="<?php $this->url('/dashboard'); ?>">Dashboard</a></li>
        <li style="margin-block-end: 1em;"><a href="<?php $this->url('/dashboard/cities'); ?>">Standardized City Details</a></li>

        <li style="margin-block-end: 1em;">
          <a href="<?php $this->url('/dashboard/leads'); ?>">All Leads</a>
          <ul style="list-style: none; padding-inline-start: 1em;">
            <li><a href="<?php $this->url('/dashboard/leads/unassigned'); ?>">Unassigned Leads</a></li>
            <li><a href="<?php $this->url('/dashboard/leads/absentee_owner'); ?>">Absentee Owners</a></li>
            <li><a href="<?php $this->url('/dashboard/leads/expired'); ?>">Expireds</a></li>
            <li><a href="<?php $this->url('/dashboard/leads/frbo'); ?>">FRBO</a></li>
            <li><a href="<?php $this->url('/dashboard/leads/fsbo'); ?>">FSBO</a></li>
          </ul>
        </li>

        <li style="margin-block-end: 1em;">
          <a href="<?php $this->url('/dashboard/leads/montgomery'); ?>">Montgomery</a>
          <ul style="list-style: none; padding-inline-start: 1em;">
            <li><a href="<?php $this->url('/dashboard/leads/montgomery/absentee_owner'); ?>">Absentee Owners</a></li>
            <li><a href="<?php $this->url('/dashboard/leads/montgomery/expired'); ?>">Expireds</a></li>
            <li><a href="<?php $this->url('/dashboard/leads/montgomery/frbo'); ?>">FRBO</a></li>
            <li><a href="<?php $this->url('/dashboard/leads/montgomery/fsbo'); ?>">FSBO</a></li>
          </ul>
        </li>
        <li>
          <a href="<?php $this->url('/dashboard/leads/auburn'); ?>">Auburn</a>
          <ul style="list-style: none; padding-inline-start: 1em;">
            <li><a href="<?php $this->url('/dashboard/leads/auburn/absentee_owner'); ?>">Absentee Owners</a></li>
            <li><a href="<?php $this->url('/dashboard/leads/auburn/expired'); ?>">Expireds</a></li>
            <li><a href="<?php $this->url('/dashboard/leads/auburn/frbo'); ?>">FRBO</a></li>
            <li><a href="<?php $this->url('/dashboard/leads/auburn/fsbo'); ?>">FSBO</a></li>
          </ul>
        </li>

        <li style="margin-block-start: auto;"><a style="display: inline-block; margin-block: 1em;" href="<?php $this->url('/logout') ?>">Logout</a></li>
      </menu>
    </nav>
  </aside>
  <footer></footer>
</body>

</html>