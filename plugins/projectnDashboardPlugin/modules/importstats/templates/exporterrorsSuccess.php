<link rel="stylesheet" type="text/css" media="screen" href="/sfDoctrinePlugin/css/global.css" />
<link rel="stylesheet" type="text/css" media="screen" href="/sfDoctrinePlugin/css/default.css" />

<style type="text/css">
    table#errors { width:100%; background-color:white; }
    table#errors th{ background-color:#C8D6FF; border:solid 1px #eee; }
    table#errors td{ border:solid 1px #eee; }
</style>

<?php
    $errors = $sf_data->getRaw( 'errorList' );
    $ignore = array( 'model', 'log_export_id', 'updated_at' );
?>

<?php if( !empty( $errors ) ){ ?>
    <table id="errors">
        <tr>
            <?php foreach( $errors[0] as $k => $v ){ ?>
                <?php if( !is_array( $v ) && !in_array( $k, $ignore ) ){ ?>
                    <th><?php echo ucfirst( $k ); ?></th>
                <?php } ?>
            <?php } ?>
        </tr>
        <?php foreach( $errors as $error ){ ?>
            <tr>
                <?php foreach( $error as $k => $v ){ ?>
                    <?php if( !is_array( $v ) && !in_array( $k, $ignore ) ){ ?>
                        <td><?php echo $v; ?></td>
                    <?php } ?>
               <?php } ?>
            </tr>
        <?php } ?>
    </table>
<?php } else { ?>
    <h4>No errors found.</h4>
<?php } ?>