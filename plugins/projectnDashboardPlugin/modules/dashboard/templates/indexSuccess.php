
<html>
  <head>
    <link type="text/css" rel="stylesheet" href="style.css" />
    <script type="text/javascript" src="jquery-1.4.3.min.js"></script>
    <script type="text/javascript" src="views.js"></script>
  </head>
  <body>
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
      <?php
        foreach( $vendors as $vendor )
        {
      ?>
        <tr>
            <th class="row-section-end big"><?php echo ucwords( $vendor['city'] );?></th>

          <td class="ok"><?php echo isset( $yesterdayData[ $vendor['city'] ][ 'poi'] ) ? round( $yesterdayData[ $vendor['city'] ][ 'poi'], 1 ) : ' - '; ?></td>
          <td class="warning"><?php echo isset( $yesterdayData[ $vendor['city'] ][ 'event'] ) ? round( $yesterdayData[ $vendor['city'] ][ 'event'], 1 ) : ' - '; ?></td>
          <td class="error row-section-end"><?php echo isset( $yesterdayData[ $vendor['city'] ][ 'movie'] ) ? round( $yesterdayData[ $vendor['city'] ][ 'movie'], 1 ) : ' - '; ?></td>

          <td class="warning">34</td>
          <td class="warning">56</td>
          <td class="error row-section-end big">89</td>

          <td class="ok"><?php echo isset( $weekData[ $vendor['city'] ][ 'poi'] ) ? round( $weekData[ $vendor['city'] ][ 'poi'], 1 ) : ' - '; ?></td>
          <td class="warning"><?php echo isset( $weekData[ $vendor['city'] ][ 'event'] ) ? round( $weekData[ $vendor['city'] ][ 'event'], 1 ) : ' - '; ?></td>
          <td class="error row-section-end"><?php echo isset( $weekData[ $vendor['city'] ][ 'movie'] ) ? round( $weekData[ $vendor['city'] ][ 'movie'], 1 ) : ' - '; ?></td>

          <td class="warning">34</td>
          <td class="warning">56</td>
          <td class="error row-section-end big">89</td>

          <td class="ok"><?php echo isset( $monthData[ $vendor['city'] ][ 'poi'] ) ? round( $monthData[ $vendor['city'] ][ 'poi'], 1 ) : ' - '; ?></td>
          <td class="warning"><?php echo isset( $monthData[ $vendor['city'] ][ 'event'] ) ? round( $monthData[ $vendor['city'] ][ 'event'], 1 ) : ' - '; ?></td>
          <td class="error row-section-end"><?php echo isset( $monthData[ $vendor['city'] ][ 'movie'] ) ? round( $monthData[ $vendor['city'] ][ 'movie'], 1 ) : ' - '; ?></td>

          <td class="warning">34</td>
          <td class="warning">56</td>
          <td class="error row-section-end">89</td>
        </tr>
        <?php
        } // foreach $this->vendors
        ?>
       
      </tbody>
    </table>
  </body>
</html>
