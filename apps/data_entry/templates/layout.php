<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <?php include_title() ?>
    <link rel="shortcut icon" href="/favicon.ico" />

    <!-- temporary manual includes for login... -->
    <link rel="stylesheet" type="text/css" media="screen" href="/sfDoctrinePlugin/css/global.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="/sfDoctrinePlugin/css/default.css" />
    <!-- end temporary manual includes for login... -->

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
          Project N Data Entry
        </h1>
      </div>

      <?php if ($sf_user->isAuthenticated()): ?>

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
            <?php echo link_to('Vendor Poi Category', '@vendor_poi_category') ?> /
          </li>
          <li>
            <?php echo link_to('Vendor Event Category', '@vendor_event_category') ?> /
          </li>
          <?php /* <li>
            <?php echo link_to('Users', 'sf_guard_user') ?>
          </li>*/ ?>
          <li>
            <?php echo link_to('Logout', 'sf_guard_signout') ?>
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
