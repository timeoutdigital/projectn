<link rel="stylesheet" type="text/css" media="screen" href="/sfDoctrinePlugin/css/global.css" />
<link rel="stylesheet" type="text/css" media="screen" href="/sfDoctrinePlugin/css/default.css" />

<style type="text/css">
    table#errors { width:100%; background-color:white; }
    table#errors th{ background-color:#C8D6FF; border:solid 1px #eee; }
    table#errors td{ border:solid 1px #eee; }
</style>

<?php
    $errors = $sf_data->getRaw( 'errorList' );
    $ignore = array( 'serialized_object', 'trace', 'log_import_id', 'updated_at' );
?>

<?php if( !empty( $errors ) ){ ?>
    <table id="errors">
        <tr>
            <?php foreach( $errors[0] as $k => $v ){ ?>
                <?php if( !is_array( $v ) && !in_array( $k, $ignore ) ){ ?>
                    <th><?php echo ucfirst( $k ); ?></th>
                <?php } ?>
            <?php } ?>
            <th>Details</th>
            <th>Resolve</th>
        </tr>
        <?php foreach( $errors as $error ){ ?>
            <tr>
                <?php foreach( $error as $k => $v ){ ?>
                    <?php if( !is_array( $v ) && !in_array( $k, $ignore ) ){ ?>
                        <td><?php echo $v; ?></td>
                    <?php } ?>
               <?php } ?>
               <td><a href="importstats/importerror/?id=<?php echo $error['id'] ; ?>" target="_blank">more info...</a></td>
               <?php if( in_array( $error['model'], array('Poi','Event','Movie') ) ){ ?>
                   <td><a href="<?php echo strtolower( $error['model'] ); ?>/resolve?import_error_id=<?php echo $error['id'] ; ?>" target="_blank">resolve</a></td>
               <?php } else { ?>
                   <td></td>
               <?php } ?>
            </tr>
        <?php } ?>
    </table>
<?php } ?>