<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <link rel="shortcut icon" href="/favicon.ico" />
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <?php include_title() ?>
    <?php include_stylesheets() ?>
    <?php include_javascripts() ?>
  </head>
  <body class="data-entry">

    <?php if ($sf_user->isAuthenticated()): ?>
        <div id="user-info">
          <div style="font-family:georgia; font-style: italic; margin-bottom: 1em; border-bottom: 1px dotted #ccc; padding-bottom: 10px;">
            Hi there, we've tidied up the design of these forms in hopes of making entering data here a bit more pleasant.<br/>
            If you find any problems, please email <a href="mailto:clarencelee@timeout.com">clarencelee@timeout.com</a>
          </div>
          <p><?php echo $sf_user->getUsername() ?>: <?php echo $sf_user->getCurrentVendorCity() ?></p>
          <p class="logout"><?php echo link_to('Logout', 'sf_guard_signout') ?></p>
        </div>
    <?php endif; ?>
    
    <div id="container">
      <div id="header">
      <a href="<?php echo url_for('@homepage') ?>">
          <?php echo image_tag( '/images/logo.gif', array( 'id' => 'logo', 'alt' => 'Timeout Project N Admin' ) ) ?>
      </a>
      <span>Project N</span>
      </div>

      <?php if ($sf_user->isAuthenticated()): ?>

      <div id="menu">
        <ul>
          <li>
            <ul>
              <?php if ($sf_user->hasCredential( 'poi' )): ?>
                <li><?php echo link_to('Poi', '@poi') ?></li>
              <?php endif ?>
              <?php if ($sf_user->hasCredential( 'vendor_poi_category' )): ?>
                <li><?php echo link_to('Vendor Poi Category', '@vendor_poi_category') ?></li>
              <?php endif ?>
              <?php if ($sf_user->hasCredential( 'poi' )): ?>
                <li><?php echo link_to('Geocode UI', '@geocode_ui') ?></li>
              <?php endif ?>
            </ul>
          </li>

          <li>
            <ul>
              <?php if ($sf_user->hasCredential( 'event' )): ?>
                <li><?php echo link_to('Event', '@event') ?></li>
              <?php endif ?>
              <?php if ($sf_user->hasCredential( 'vendor_event_category' )): ?>
                <li><?php echo link_to('Vendor Event Category', '@vendor_event_category') ?></li>
              <?php endif ?>
            </ul>
          </li>

          <li>
            <ul>
          <?php if ($sf_user->hasCredential( 'movie' )): ?>
            <li><?php echo link_to('Movie', '@movie') ?></li>
          <?php endif ?>
            </ul>
          </li>

          <li>
            <ul>
            </ul>
          </li>

          <li>
            <ul>
            </ul>
          </li>

          <li>
            <ul>
              <?php if ($sf_user->hasCredential( 'admin' )): ?>
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
          </li>

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
