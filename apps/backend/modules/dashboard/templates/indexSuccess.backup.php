<html>
<head>
<script type="text/javascript" src="/js/dygraph-combined.js"></script>
</head>
<body>
<?php foreach( $data as $city => $models ){ ?>
    <?php foreach( $models as $model => $csv ){ ?>
        <?php if( !empty( $csv ) ){ ?>
            <h1><?php echo ucfirst( $city ) . " " . $model . " Import Stats."; ?></h1>
            <div id="<?php echo $city . "_" . $model; ?>" style="width:95%; height:350px;"></div>
            <script type="text/javascript">
              new Dygraph(
                document.getElementById("<?php echo $city . "_" . $model; ?>"),
                <?php echo $csv; ?>,
                {
                  rollPeriod: 7,
                  showRoller: false
                }
              );
            </script>
        <?php } ?>
    <?php } ?>
<?php } ?>
<br /><br />
</body>
</html>