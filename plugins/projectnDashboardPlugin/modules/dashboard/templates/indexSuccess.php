<?php use_helper('dashboard');?>
<?php use_stylesheet('/projectnDashboardPlugin/css/dashboard.css'); ?>
<?php use_javascript('jquery-1.4.3.min.js'); ?>
<?php use_javascript('/projectnDashboardPlugin/js/dashboard.js'); ?>
    <table id="over-view">
      <thead>
        <tr>
          <th rowspan="3" id="data-type" class="empty row-section-end big"></th>
          <th colspan="6" class="row-section-end big">Today vs yesterday</th>
          <th colspan="6" class="row-section-end big">This week vs last week</th>
          <th colspan="6">This month vs last month</th>
        </tr>
        <tr>
          <th colspan="2" class="row-section-end">Poi</th>
          <th colspan="2" class="row-section-end">Event</th>
          <th class="row-section-end big" colspan="2" class="">Movie</th>
          
          <th colspan="2" class="row-section-end">Poi</th>
          <th colspan="2" class="row-section-end">Event</th>
          <th class="row-section-end big" colspan="2" class="">Movie</th>
          
          <th colspan="2" class="row-section-end">Poi</th>
          <th colspan="2" class="row-section-end">Event</th>
          <th colspan="2" class="">Movie</th>


        </tr>
        <tr>
          <th>Import</th>
          <th class="row-section-end">Export</th>
          <th >Import</th>
          <th class="row-section-end">Export</th>
          <th>Import</th>
          <th class="row-section-end big">Export</th>

          <th>Import</th>
          <th class="row-section-end">Export</th>
          <th >Import</th>
          <th class="row-section-end">Export</th>
          <th>Import</th>
          <th class="row-section-end big">Export</th>

          <th>Import</th>
          <th class="row-section-end">Export</th>
          <th >Import</th>
          <th class="row-section-end">Export</th>
          <th>Import</th>
          <th>Export</th>
        </tr>
      </thead>
      <tbody>
      <?php 
      $vendors_sorted = $importYesterday->getIncludedCities();
      asort( $vendors_sorted ); // Sort A-Z
      foreach( $vendors_sorted as $cityName ):?>
        <tr>
          <th class="row-section-end big"><?php echo ucwords( str_replace('_', ' ', $cityName) );?></th>

          <?php 
          foreach( array('yesterday', 'week', 'month') as $logDay ){

              $importLog = 'import'.ucwords($logDay);
              $exportLog = 'export'.ucwords($logDay);
              // echo TD for Import LOG
              echo dashboardRow( $cityName, $$importLog, 'poi' );
              echo dashboardRow( $cityName, $$exportLog, 'poi', 'row-section-end' );

              echo dashboardRow( $cityName, $$importLog, 'event' );
              echo dashboardRow( $cityName, $$exportLog, 'event', 'row-section-end' );

              echo dashboardRow( $cityName, $$importLog, 'movie' );
              echo dashboardRow( $cityName, $$exportLog, 'movie', 'row-section-end big' );
          }
          ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

<table style="width:90%;" cellspacing="1" cellpadding="5">
    <thead>
        <tr>
            <th align="left">Yesterday Error Log:</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach( $importYesterday->getErrors() as $error ): ?>
        <tr>
            <th style="background-color: #fff;"><?php echo $error; ?></th>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<br />
