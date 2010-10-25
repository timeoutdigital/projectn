<script type="text/javascript" src="/js/dygraph-combined.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="/sfDoctrinePlugin/css/global.css" />
<link rel="stylesheet" type="text/css" media="screen" href="/sfDoctrinePlugin/css/default.css" />

<div style="float:right;"><?php echo $form; ?></div>
<div id="graphholder" style="clear:both; width:100%; height:350px;"></div>

<script type="text/javascript">
  new Dygraph(
    document.getElementById("graphholder"),
    "<?php
        echo 'Date,insert,failed,updated\n';
        foreach( $stats as $date => $metrics )
            echo $date . ',' . $metrics[ $model ]['insert'] . ',' . $metrics[ $model ]['failed'] . ',' . $metrics[ $model ]['updated'] . '\n';
    ?>",
    {
      rollPeriod: 1,
      showRoller: false,
      includeZero: true,
      strokeWidth: 4,
      drawPoints: 0,
      pointSize: 4,
      colors: ['green', 'red', 'blue']
    }
  );
</script>