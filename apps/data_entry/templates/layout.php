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
              <?php echo image_tag( '/images/logo.gif', array( 'id' => 'logo', 'alt' => 'Timeout Project N Admin' ) ) ?>
          </a>
          Project N Data Entry
        </h1>
      </div>

      <?php if ($sf_user->isAuthenticated()): ?>

      <div id="infoBox">
        You are logged in as: <?php echo $sf_user->getUsername() ?><br />
        working on: <?php echo $sf_user->getCurrentVendorCity() ?><br />
        <?php echo link_to('Logout', 'sf_guard_signout') ?>
      </div>
    
      <div id="menu">
        <ul>
          <?php if ($sf_user->hasCredential( 'poi' )): ?>
            <li><?php echo link_to('Poi', '@poi') ?></li>
          <?php endif ?>
          <?php if ($sf_user->hasCredential( 'event' )): ?>
            <li><?php echo link_to('Event', '@event') ?></li>
          <?php endif ?>
          <?php if ($sf_user->hasCredential( 'movie' )): ?>
            <li><?php echo link_to('Movie', '@movie') ?></li>
          <?php endif ?>
          <?php if ($sf_user->hasCredential( 'vendor_poi_category' )): ?>
            <li><?php echo link_to('Vendor Poi Category', '@vendor_poi_category') ?></li>
          <?php endif ?>
          <?php if ($sf_user->hasCredential( 'vendor_event_category' )): ?>
            <li><?php echo link_to('Vendor Event Category', '@vendor_event_category') ?></li>
          <?php endif ?>
	 <?php if ($sf_user->hasCredential( 'poi' )): ?>
            <li><?php echo link_to('Geocode UI', '@geocode_ui') ?></li>
          <?php endif ?>
          <?php if ($sf_user->hasCredential( 'admin' )): ?>
            <li>||</li>
            <li>
              <?php echo link_to('Users', 'sf_guard_user') ?>
            </li>
            <li>
              <?php echo link_to('Groups', 'sf_guard_group') ?>
            </li>
            <li>
              <?php echo link_to('Permissions', 'sf_guard_permission') ?>
            </li>
          <?php endif ?>
        </ul>
      </div>
      <?php endif ?>

      <div class="clear"></div>
      <div id="content">

        <?php echo $sf_content ?>
      </div>
      <div id="footer"></div>
    </div>
  </body>
</html>
