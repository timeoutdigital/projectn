<div id="graphholder_<?php echo $vendor['id'];?>" style="clear:both; width:95%; height:300px;"></div>
<?php
    $csv = 'Date,Poi,Event,Movie\n';
    foreach( $graphData as $dateStamp => $dateData ){
        $csv .= $dateStamp . ',' . $dateData['Poi'] . ',' . $dateData['Event'] . ',' . $dateData['Movie'] . '\n';
    }
?>
    <script type="text/javascript">
      new Dygraph(
        document.getElementById("graphholder_<?php echo $vendor['id']; ?>"),
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