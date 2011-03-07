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
          <a href="<?php echo url_for('@homepage') ?>"><img id="logo" src="/images/logo.gif" alt="Timeout Project N Admin" /></a><span>Project N</span>
      </div>

      <div id="menu">
        <ul>
          <li>
              <ul>
                  <li>
                    <?php echo link_to('POI', '@poi') ?> 
                  </li>
                  <li>
                    <?php echo link_to('Vendor POI categories', '@vendor_poi_category') ?>
                  </li>
                  <li>
                    <?php echo link_to('Geocode white list', 'poi_geo_white_list') ?>
                  </li>
                  <li>
                    <?php echo link_to('Geocode UI', '@geocode_ui') ?>
                  </li>
                  <li>
                    <?php echo link_to('Duplicates', '@poi_duplicate_pois') ?>
                  </li>
              </ul>
          </li>
          <li>
              <ul>
                  <li>
                    <?php echo link_to('Event', '@event') ?>
                  </li>
                  <li>
                    <?php echo link_to('Vendor event categories', '@vendor_event_category') ?>
                  </li>
              </ul>
          </li>
          <li>
              <ul>
                  <li>
                    <?php echo link_to('Movie', '@movie') ?>
                  </li>
              </ul>
          </li>
          <li>
            <?php echo link_to('Vendor', '@vendor') ?>
          </li>
          <li>
            <ul>
                <li>
                   Errors 
                </li>
                <li>
                    <?php echo link_to('Import', '@log_import_error') ?>
                </li>
                <li>
                    <?php echo link_to('Export', '@log_export_error') ?>
                </li>
            </ul>
          </li>
          <li>
            <ul>
                <li>
                   Stats 
                </li>
                <li>
                    <?php echo link_to('Dashboard', 'dashboard/index') ?>
                </li>
                <li>
                    <?php echo link_to('Import Stats', 'importstats/index') ?>
                </li>
                <li>
                    <?php echo link_to('Export Stats', 'exportstats/index') ?>
                </li>
            </ul>
          </li>
        </ul>
      </div>
      <div class="clear"></div>
      <div id="content">
        <?php echo $sf_content ?>
      </div>
      <div id="footer"></div>
      <div class="clear"></div>
    </div>
  </body>
</html>
