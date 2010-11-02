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

          <th colspan="2" class="row-section-end">Poi</th>
          <th colspan="2" class="row-section-end">Event</th>
          <th class="empty row-section-end big" colspan="2" class="">Movie</th>
          
          <th colspan="2" class="row-section-end">Poi</th>
          <th colspan="2" class="row-section-end">Event</th>
          <th class="empty row-section-end big" colspan="2" class="">Movie</th>
          
          <th colspan="2" class="row-section-end">Poi</th>
          <th colspan="2" class="row-section-end">Event</th>
          <th colspan="2" class="">Movie</th>


        </tr>
        <tr>
          <th class="row-section-end big"></th>

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
      <?php foreach( $importYesterday->getIncludedCities() as $cityName ):?>
        <tr>
          <th class="row-section-end big"><?php echo ucwords( str_replace('_', ' ', $cityName) );?></th>

          <?php 
          foreach( array('yesterday', 'week', 'month') as $logDay ){

              $importLog = 'import'.ucwords($logDay);
              $exportLog = 'export'.ucwords($logDay);
              // echo TD for Import LOG
              printf( '<td class="%s" title="%s">%s</td>', $$importLog->getStatusBy( $cityName , 'poi') , $$importLog->getVariantNumberBy( $cityName , 'poi' ), $$importLog->getVariantPercentageBy(  $cityName , 'poi', 1 ) );
              printf( '<td class="row-section-end %s" title="%s">%s</td>', $$exportLog->getStatusBy( $cityName , 'poi') , $$exportLog->getVariantNumberBy( $cityName , 'poi' ), $$exportLog->getVariantPercentageBy(  $cityName , 'poi', 1 ) );

              printf( '<td class="%s" title="%s">%s</td>', $$importLog->getStatusBy( $cityName , 'event') , $$importLog->getVariantNumberBy( $cityName , 'event' ), $$importLog->getVariantPercentageBy(  $cityName , 'event', 1 ) );
              printf( '<td class="row-section-end %s" title="%s">%s</td>', $$exportLog->getStatusBy( $cityName , 'event') , $$exportLog->getVariantNumberBy( $cityName , 'event' ), $$exportLog->getVariantPercentageBy(  $cityName , 'event', 1 ) );

              printf( '<td class="%s" title="%s">%s</td>', $$importLog->getStatusBy( $cityName , 'movie') , $$importLog->getVariantNumberBy( $cityName , 'movie' ), $$importLog->getVariantPercentageBy(  $cityName , 'movie', 1 ) );
              printf( '<td class="row-section-end big %s" title="%s">%s</td>', $$exportLog->getStatusBy( $cityName , 'movie') , $$exportLog->getVariantNumberBy( $cityName , 'movie' ), $$exportLog->getVariantPercentageBy(  $cityName , 'movie', 1 ) );
              
          }
          ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
<!--
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
-->
<br />