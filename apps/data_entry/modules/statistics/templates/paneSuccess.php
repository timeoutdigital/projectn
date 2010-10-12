<script type="text/javascript" src="/js/dygraph-combined.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="/sfDoctrinePlugin/css/global.css" />
<link rel="stylesheet" type="text/css" media="screen" href="/sfDoctrinePlugin/css/default.css" />

<style type="text/css">
    table#panel td { border:none; background-color:#fff; }
    table#panel tr { border-bottom: solid 3px #C8D6FF; }
    table#panel span.panel-title { font-weight:bold; }
    table#panel p.diff { display:block; width:40px; height:32px; margin:-3px 0; padding:0px; text-align:right; font-style: italic; }
    p.up{ background-image: url( "/images/up_alt.png" ); background-repeat: no-repeat; }
    p.down{ background-image: url( "/images/down_alt.png" ); background-repeat: no-repeat; }
    p.num { background-color:#C8D6FF; border-radius: 5px; -moz-border-radius: 5px; padding:5px; margin-bottom: 0px; text-align:center; }
    p.noyesterday { background-image: url( "/images/alert.png" ); background-repeat: no-repeat; height:22px; padding-top:10px; padding-left:50px; margin-left:10px; }
</style>

<table id="xwdfwd" style="background-color:#fff;margin-right:10px;">
    <tr id="summary">
        <td style="background-color:#fff; border:none;" valign="top">
            <div style="background-color:#C8D6FF; width:300px; margin:10px; border-radius: 5px; -moz-border-radius: 5px;">
                <?php
                    $today      = $statsPanel->current(); $statsPanel->next();
                    $yesterday  = $statsPanel->current();

                    $exportStats = $exportStats->current();
                    $exportTotal = false;

                    if( isset( $exportStats[ 'LogExportCount' ] ) && !empty( $exportStats[ 'LogExportCount' ] ) )
                        $exportTotal = $exportStats[ 'LogExportCount' ][0]['count'];

                    if( $today !== false )
                    {
                    
                        $todayTotal = $yesterdayTotal = 0;
                        foreach( $today[ $model ] as $metric ) $todayTotal += $metric;
                        if( $yesterday !== false ) foreach( $yesterday[ $model ] as $metric ) $yesterdayTotal += $metric;
                ?>
                <table id="panel" width="290px" style="margin:5px; border:none;">
                    <caption style="font-size:26px;">Todays Summary</caption>
                    <tr>
                        <td><span class="panel-title" style="color:green">New Records</span></td>
                        <td>
                            <?php if( $yesterday !== false ){ ?>
                                <?php if( $today[ $model ]['insert'] > $yesterday[ $model ]['insert'] ){ ?>
                                    <p class="diff up"><?= $today[ $model ]['insert'] - $yesterday[ $model ]['insert']; ?></p>
                                <?php } elseif( $today[ $model ]['insert'] < $yesterday[ $model ]['insert'] ){ ?>
                                    <p class="diff down"><?= $yesterday[ $model ]['insert'] - $today[ $model ]['insert']; ?></p>
                                <?php } ?>
                            <?php } ?>
                        </td>
                        <td><p class="num"><?= $today[ $model ]['insert']; ?></p></td>
                    </tr>
                    <tr>
                        <td><span class="panel-title" style="color:red">Failed to Import</span></td>
                        <td>
                            <?php if( $yesterday !== false ){ ?>
                                <?php if( $today[ $model ]['failed'] > $yesterday[ $model ]['failed'] ){ ?>
                                    <p class="diff up"><?= $today[ $model ]['failed'] - $yesterday[ $model ]['failed']; ?></p>
                                <?php } elseif( $today[ $model ]['failed'] < $yesterday[ $model ]['failed'] ){ ?>
                                    <p class="diff down"><?= $yesterday[ $model ]['failed'] - $today[ $model ]['failed']; ?></p>
                                <?php } ?>
                            <?php } ?>
                        </td>
                        <td><p class="num"><?= $today[ $model ]['failed']; ?></p></td>
                    </tr>
                    <tr>
                        <td><span class="panel-title" style="color:blue">Updated Records</span></td>
                        <td width="10px">
                            <?php if( $yesterday !== false ){ ?>
                                <?php if( $today[ $model ]['updated'] > $yesterday[ $model ]['updated'] ){ ?>
                                    <p class="diff up"><?= $today[ $model ]['updated'] - $yesterday[ $model ]['updated']; ?></p>
                                <?php } elseif( $today[ $model ]['updated'] < $yesterday[ $model ]['updated'] ){ ?>
                                    <p class="diff down"><?= $yesterday[ $model ]['updated'] - $today[ $model ]['updated']; ?></p>
                                <?php } ?>
                            <?php } ?>
                        </td>
                        <td width="50px"><p class="num"><?= $today[ $model ]['updated']; ?></p></td>
                    </tr>
                    <tr>
                        <td><span class="panel-title" style="color:#555;">Un-modified Records</span></td>
                        <td width="10px">
                            <?php if( $yesterday !== false ){ ?>
                                <?php if( $today[ $model ]['existing'] > $yesterday[ $model ]['existing'] ){ ?>
                                    <p class="diff up"><?= $today[ $model ]['existing'] - $yesterday[ $model ]['existing']; ?></p>
                                <?php } elseif( $today[ $model ]['existing'] < $yesterday[ $model ]['existing'] ){ ?>
                                    <p class="diff down"><?= $yesterday[ $model ]['existing'] - $today[ $model ]['existing']; ?></p>
                                <?php } ?>
                            <?php } ?>
                        </td>
                        <td width="50px"><p class="num"><?= $today[ $model ]['existing']; ?></p></td>
                    </tr>
                    <tr>
                        <td><span class="panel-title">Total in Vendor Feed</span></td>
                        <td>
                            <?php if( $yesterday !== false ){ ?>
                                <?php if( $todayTotal > $yesterdayTotal ){ ?>
                                    <p class="diff up"><?= $todayTotal - $yesterdayTotal; ?></p>
                                <?php } elseif( $todayTotal < $yesterdayTotal ){ ?>
                                    <p class="diff down"><?= $yesterdayTotal - $todayTotal; ?></p>
                                <?php } ?>
                            <?php } ?>
                        </td>
                        <td><p class="num"><?= $todayTotal; ?></p></td>
                    </tr>
                    <tr><td colspan="3" style="background-color:#C8D6FF; height:5px; padding:0px;"></td></tr>
                    <tr>
                        <td><span class="panel-title">Total in Database</span></td>
                        <td></td>
                        <td><p class="num"><?= is_numeric( $dbtotal ) ? $dbtotal : '?'; ?></p></td>
                    </tr>
                    <tr>
                        <td><span class="panel-title">Total Exported to Nokia</span></td>
                        <td></td>
                        <td><p class="num"><?= is_numeric( $exportTotal ) ? $exportTotal : '?'; ?></p></td>
                    </tr>
                </table>
            </div>
                <?php if( $yesterday === false ){ ?>
                    <p class="noyesterday">Failed to retrieve yesterdays import logs.</p>
                <?php } ?>
            <?php } else { ?>
                <p class="noyesterday">Failed to retrieve todays import logs.</p>
            <?php } ?>
        </td>
        <td id="summary2" style="border:none; padding:17px;" width="100%">
            <div style="background-color:#C8D6FF; border-radius: 5px; margin-bottom:20px; -moz-border-radius: 5px;">
                <p style="font-size:26px; padding:5px 10px; text-align:center;"><?= ucfirst( $vendor->city ); ?> <?= ucfirst( $model ); ?> imports for period: <?= date( 'jS F \'y', $date_from ); ?> - <?= date( 'jS F \'y', $date_to ); ?></p>
            </div>
            <div id="graph" style="width:100%; height:350px;"></div>
        </td>
    </tr>
</div>

<script type="text/javascript">
  new Dygraph(
    document.getElementById("graph"),
    "<?php
        echo 'Date,insert,failed,updated\n';
        foreach( $stats as $date => $metrics )
            echo $date . ',' . $metrics[ $model ]['insert'] . ',' . $metrics[ $model ]['failed'] . ',' . $metrics[ $model ]['updated'] . '\n';
    ?>",
    {
      rollPeriod: 1,
      showRoller: false,
      includeZero: true,
      strokeWidth: 2,
      drawPoints: 1,
      pointSize: 4,
      colors: ['green', 'red', 'blue']
    }
  );
</script>

