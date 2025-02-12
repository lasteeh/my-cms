<?php
$error_messages = $this->get_flash('errors');
$alert_messages = $this->get_flash('alerts');
?>

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
  <div id="overlays">
    <?php if ((!empty($error_messages)) || (!empty($alert_messages))) : ?>
      <div class="flash-messages">
        <?php if (!empty($error_messages)) : ?>
          <ul class="errors">
            <?php
            foreach ($error_messages as $error) {
              $message = <<<HTML
              <li onclick="this.style.display=`none`;"><span>&#10006;</span> <span>&nbsp;&#9474;&nbsp;</span> <span>$error</span></li>
            HTML;

              echo $message;
            }
            ?>
          </ul>
        <?php endif; ?>
        <?php if (!empty($alert_messages)) : ?>
          <ul class="alerts">
            <?php
            foreach ($alert_messages as $alert) {
              $message = <<<HTML
              <li onclick="this.style.display=`none`;"><span>&#10004;</span> <span>&nbsp;&#9474;&nbsp;</span> <span>$alert</span></li>
            HTML;

              echo $message;
            }
            ?>
          </ul>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
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

        <li><a target="_blank" href="https://matrix.lcar.mlsmatrix.com/Matrix/Default.aspx?c=AAEAAAD*****AQAAAAAAAAARAQAAAEQAAAAGAgAAAAQzNTM4DUAGAwAAAAbDh8K4NHMNAgs)&f=">LCAR® Saved Searches</a></li>
        <li><a target="_blank" href="https://matrix.alamls.net/Matrix/Default.aspx?c=AAEAAAD*****AQAAAAAAAAARAQAAAEQAAAAGAgAAAAQzMTQ3DUAGAwAAAAfDhcO0wosqDQIL&f=">MAAR® Saved Searches</a></li>
        <li style="margin-block-end: 1em;"><a target="_blank" href="https://vortex.theredx.com/">Vortex</a></li>

        <li><a target="_blank" href="https://www.homesearchinauburn.com/signin?returnurl=%2Fdashboard%2Fleadsimport">Auburn CINC Site</a></li>
        <li style="margin-block-end: 1em;"><a target="_blank" href="https://www.searchhomesinmontgomery.com/signin?returnurl=%2Fdashboard%2Fleadsimport">Montgomery CINC Site</a></li>

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
            <li><a href="<?php $this->url('/dashboard/leads/absentee_owner/montgomery'); ?>">Absentee Owners</a></li>
            <li><a href="<?php $this->url('/dashboard/leads/expired/montgomery'); ?>">Expireds</a></li>
            <li><a href="<?php $this->url('/dashboard/leads/frbo/montgomery'); ?>">FRBO</a></li>
            <li><a href="<?php $this->url('/dashboard/leads/fsbo/montgomery'); ?>">FSBO</a></li>
          </ul>
        </li>
        <li style="margin-block-end: 1em;">
          <a href="<?php $this->url('/dashboard/leads/auburn'); ?>">Auburn</a>
          <ul style="list-style: none; padding-inline-start: 1em;">
            <li><a href="<?php $this->url('/dashboard/leads/absentee_owner/auburn'); ?>">Absentee Owners</a></li>
            <li><a href="<?php $this->url('/dashboard/leads/expired/auburn'); ?>">Expireds</a></li>
            <li><a href="<?php $this->url('/dashboard/leads/frbo/auburn'); ?>">FRBO</a></li>
            <li><a href="<?php $this->url('/dashboard/leads/fsbo/auburn'); ?>">FSBO</a></li>
          </ul>
        </li>

        <li style="margin-block-start: auto;"><a style="display: inline-block; margin-block: 1em;" href="<?php $this->url('/logout') ?>">Logout</a></li>
      </menu>
    </nav>
  </aside>
  <footer></footer>
</body>

</html>