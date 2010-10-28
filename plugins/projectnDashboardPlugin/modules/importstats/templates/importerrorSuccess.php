<link rel="stylesheet" type="text/css" media="screen" href="/sfDoctrinePlugin/css/global.css" />
<link rel="stylesheet" type="text/css" media="screen" href="/sfDoctrinePlugin/css/default.css" />

<style type="text/css">
</style>

<?php
    $errors = $sf_data->getRaw( 'importError' );
    $ignore = array( 'id', 'log_import_id', 'updated_at' );
?>

<?php if( !empty( $errors ) ){ ?>
    <?php foreach( $errors as $error ){ ?>
        <?php foreach( $error as $k => $v ){ ?>
            <?php if( !is_array( $v ) && !in_array( $k, $ignore ) ){ ?>
                <?php if( $k == 'serialized_object'){ ?>
                <pre style="background-color:white; padding:10px; border:solid 1px #ddd; overflow: auto;">
<?php
    $u = unserialize( $v );
    if( method_exists( $u, 'toArray' ) )
    {
        echo '<h3>Object of type '. get_class( $u ) .'</h3>';
        $u = $u->toArray();
    }
    print_r( $u );
?>
                </pre>
                <?php } else { ?>
                    <pre style="background-color:white; padding:10px; border:solid 1px #ddd; overflow: auto;">
<?php echo "$k : " . ( is_bool( $v ) ? ( $v ? 'true' : 'false' ) : $v ); ?>
                    </pre>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    <?php } ?>
<?php } ?>