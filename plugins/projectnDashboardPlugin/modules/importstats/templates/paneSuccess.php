<script type="text/javascript" src="/js/dygraph-combined.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="/sfDoctrinePlugin/css/global.css" />
<link rel="stylesheet" type="text/css" media="screen" href="/sfDoctrinePlugin/css/default.css" />

            
                <?php
                    $statsPanel = $sf_data->getRaw( 'statsPanel' );

                    if( is_array( $statsPanel ) && count( $statsPanel ) > 1 )
                    {
                        $yesterday = array_shift( $statsPanel );
                    }
		    $today = array_shift( $statsPanel );

                    $exportStats = $sf_data->getRaw( 'exportStats' );
                    $exportTotal = false;

                    if( isset( $exportStats[0][ 'LogExportCount' ][0]['count'] ) )
                        $exportTotal = $exportStats[0][ 'LogExportCount' ][0]['count'];
                ?>
                <table id="panel" width="290px" style="margin:5px; border:none;">
                    <caption style="font-size:26px;"><?php echo $form; ?></caption>
                    <?php
                        if( is_array( $today ) )
                        {

                            $todayTotal = $yesterdayTotal = 0;
                            foreach( $today[ $model ] as $metric ) $todayTotal += $metric;
                            if( is_array( $yesterday ) && array_key_exists( $model, $yesterday ) ) foreach( $yesterday[ $model ] as $metric ) $yesterdayTotal += $metric;
                    ?>
                    <tr>
                        <td><span class="panel-title" style="color:green">New Records</span></td>
                        <td>
                            <?php if( is_array( $yesterday ) ){ ?>
                                <?php if( $today[ $model ]['insert'] > $yesterday[ $model ]['insert'] ){ ?>
                                    <p class="diff up"><?php echo $today[ $model ]['insert'] - $yesterday[ $model ]['insert']; ?></p>
                                <?php } elseif( $today[ $model ]['insert'] < $yesterday[ $model ]['insert'] ){ ?>
                                    <p class="diff down"><?php echo $yesterday[ $model ]['insert'] - $today[ $model ]['insert']; ?></p>
                                <?php } ?>
                            <?php } ?>
                        </td>
                        <td><p class="num"><?php echo $today[ $model ]['insert']; ?></p></td>
                    </tr>
                    <tr>
                        <td><span class="panel-title" style="color:red">Failed to Import</span></td>
                        <td>
                            <?php if( is_array( $yesterday ) ){ ?>
                                <?php if( $today[ $model ]['failed'] > $yesterday[ $model ]['failed'] ){ ?>
                                    <p class="diff up"><?php echo $today[ $model ]['failed'] - $yesterday[ $model ]['failed']; ?></p>
                                <?php } elseif( $today[ $model ]['failed'] < $yesterday[ $model ]['failed'] ){ ?>
                                    <p class="diff down"><?php echo $yesterday[ $model ]['failed'] - $today[ $model ]['failed']; ?></p>
                                <?php } ?>
                            <?php } ?>
                        </td>
                        <td><p class="num"><?php echo $today[ $model ]['failed']; ?></p></td>
                    </tr>
                    <tr>
                        <td><span class="panel-title" style="color:blue">Updated Records</span></td>
                        <td width="10px">
                            <?php if( is_array( $yesterday ) ){ ?>
                                <?php if( $today[ $model ]['updated'] > $yesterday[ $model ]['updated'] ){ ?>
                                    <p class="diff up"><?php echo $today[ $model ]['updated'] - $yesterday[ $model ]['updated']; ?></p>
                                <?php } elseif( $today[ $model ]['updated'] < $yesterday[ $model ]['updated'] ){ ?>
                                    <p class="diff down"><?php echo $yesterday[ $model ]['updated'] - $today[ $model ]['updated']; ?></p>
                                <?php } ?>
                            <?php } ?>
                        </td>
                        <td width="50px"><p class="num"><?php echo $today[ $model ]['updated']; ?></p></td>
                    </tr>
                    <tr>
                        <td><span class="panel-title" style="color:#555;">Un-modified Records</span></td>
                        <td width="10px">
                            <?php if( is_array( $yesterday ) ){ ?>
                                <?php if( $today[ $model ]['existing'] > $yesterday[ $model ]['existing'] ){ ?>
                                    <p class="diff up"><?php echo $today[ $model ]['existing'] - $yesterday[ $model ]['existing']; ?></p>
                                <?php } elseif( $today[ $model ]['existing'] < $yesterday[ $model ]['existing'] ){ ?>
                                    <p class="diff down"><?php echo $yesterday[ $model ]['existing'] - $today[ $model ]['existing']; ?></p>
                                <?php } ?>
                            <?php } ?>
                        </td>
                        <td width="50px"><p class="num"><?php echo $today[ $model ]['existing']; ?></p></td>
                    </tr>
                    <tr>
                        <td><span class="panel-title">Total in Vendor Feed</span></td>
                        <td>
                            <?php if( is_array( $yesterday ) ){ ?>
                                <?php if( $todayTotal > $yesterdayTotal ){ ?>
                                    <p class="diff up"><?php echo $todayTotal - $yesterdayTotal; ?></p>
                                <?php } elseif( $todayTotal < $yesterdayTotal ){ ?>
                                    <p class="diff down"><?php echo $yesterdayTotal - $todayTotal; ?></p>
                                <?php } ?>
                            <?php } ?>
                        </td>
                        <td><p class="num"><?php echo $todayTotal; ?></p></td>
                    </tr>
                    <tr><td colspan="3" style="background-color:#C8D6FF; height:5px; padding:0px;"></td></tr>
                    <tr>
                        <td><span class="panel-title">Total in Database</span></td>
                        <td></td>
                        <td><p class="num"><?php echo is_numeric( $dbtotal ) ? $dbtotal : '?'; ?></p></td>
                    </tr>
                    <tr>
                        <td><span class="panel-title">Total Exported to Nokia</span></td>
                        <td></td>
                        <td><p class="num"><?php echo is_numeric( $exportTotal ) ? $exportTotal : '?'; ?></p></td>
                    </tr>
                <?php if( is_null( $yesterday ) ){ ?>
                    <p class="noyesterday">Failed to retrieve import logs for previous day.</p>
                <?php } ?>
            <?php } else { ?>
                <p class="noyesterday">Failed to retrieve import logs for specified date.</p>
            <?php } ?>
                </table>