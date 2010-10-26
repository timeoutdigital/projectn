<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <?php include_title() ?>
    <link rel="shortcut icon" href="/favicon.ico" />
    <?php include_stylesheets() ?>
    <?php include_javascripts() ?>
  </head>
  <body>
    <div id="container">
      <div id="header">
        <h1>
          <a href="<?php echo url_for('@homepage') ?>">
            <img id="logo" src="/images/logo.gif" alt="Timeout Project N Admin" />
          </a>
          Project N Admin
        </h1>
      </div>

      <div id="menu">
        <ul>
          <li>
            <?php echo link_to('Poi', '@poi') ?> /
          </li>
          <li>
            <?php echo link_to('Event', '@event') ?> /
          </li>
          <li>
            <?php echo link_to('Movie', '@movie') ?> /
          </li>
          <li>
            <?php echo link_to('Vendor', '@vendor') ?> /
          </li>
          <li>
            <?php echo link_to('Vendor Poi Categories', '@vendor_poi_category') ?> /
          </li>
          <li>
            <?php echo link_to('Vendor Event Categories', '@vendor_event_category') ?> /
          </li>
          <li>
            <?php echo link_to('Geocode UI', '@geocode_ui') ?> /
          </li>
          <li>
            <?php echo link_to('Export Stats', 'exportstats/index') ?> /
          </li>
        </ul>
      </div>
      <div class="clear"></div>
      <div id="content">
        <?php echo $sf_content ?>
      </div>
      <div id="footer"></div>
    </div>
  </body>
</html>
