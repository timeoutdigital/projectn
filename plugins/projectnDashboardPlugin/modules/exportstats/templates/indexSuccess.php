<script type="text/javascript" src="/js/dygraph-combined.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="/sfDoctrinePlugin/css/global.css" />
<link rel="stylesheet" type="text/css" media="screen" href="/sfDoctrinePlugin/css/default.css" />

<?php foreach( $graphData as $cityName => $cityData ){ ?>
    <?php
        $csv = 'Date,Poi,Event,Movie\n';
        foreach( $cityData as $dateStamp => $dateData ){
            $csv .= $dateStamp . ',' . $dateData['Poi'] . ',' . $dateData['Event'] . ',' . $dateData['Movie'] . '\n';
        }
    ?>
    <h1><?php echo ucfirst( $cityName ) . " Export Stats."; ?></h1>
    <div id="<?php echo $cityName; ?>" style="width:95%; height:300px;"></div>
    <script type="text/javascript">
      new Dygraph(
        document.getElementById("<?php echo $cityName; ?>"),
        "<?php echo $csv; ?>",
        {
          rollPeriod: 1,
          showRoller: false,
          includeZero: true,
          strokeWidth: 2,
          drawPoints: 1,
          pointSize: 4,
        }
      );
    </script>
<?php } ?>
<br /><br />