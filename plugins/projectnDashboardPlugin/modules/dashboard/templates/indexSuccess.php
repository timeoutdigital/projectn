<?php use_helper('dashboard');?>
<?php use_stylesheet('/projectnDashboardPlugin/css/dashboard.css'); ?>
    <table id="over-view">
      <thead>
        <tr>
          <th class="empty row-section-end big"></th>
          <th colspan="6" class="row-section-end big">Yesterday</th>
          <th colspan="6" class="row-section-end big">Last week</th>
          <th colspan="6">Last month</th>
        </tr>
        <tr>
          <th class="empty row-section-end big"></th>

          <th colspan="3" class="">Import</th>
          <th colspan="3" class="row-section-end big">Export</th>

          <th colspan="3" class="">Import</th>
          <th colspan="3" class="row-section-end big">Export</th>

          <th colspan="3" class="">Import</th>
          <th colspan="3">Export</th>
        </tr>
        <tr>
          <th class="row-section-end big"></th>

          <th>POI</th>
          <th>Event</th>
          <th class="row-section-end">Movie</th>
          <th>POI</th>
          <th>Event</th>
          <th class="row-section-end big">Movie</th>

          <th>POI</th>
          <th>Event</th>
          <th class="row-section-end">Movie</th>
          <th>POI</th>
          <th>Event</th>
          <th class="row-section-end big">Movie</th>

          <th>POI</th>
          <th>Event</th>
          <th class="row-section-end">Movie</th>
          <th>POI</th>
          <th>Event</th>
          <th>Movie</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach( $vendors as $vendor ): ?>
        <tr>
          <th class="row-section-end big"><?php echo ucwords( $vendor['city'] );?></th>

          <?php 
          foreach( array('yesterday', 'week', 'month') as $logDay ){

              // echo TD for Import LOG
              echo buildAndReturnTD( $vendor['city'], $thresholdImport, 'poi' );
              echo buildAndReturnTD( $vendor['city'], $thresholdImport, 'event' );
              echo buildAndReturnTD( $vendor['city'], $thresholdImport, 'movie' );

              // echo TD for Export LOG
              echo buildAndReturnTD( $vendor['city'], $thresholdExport, 'poi' );
              echo buildAndReturnTD( $vendor['city'], $thresholdExport, 'event' );
              echo buildAndReturnTD( $vendor['city'], $thresholdExport, 'movie' );
          }
          ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
