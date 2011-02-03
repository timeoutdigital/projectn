<link rel="stylesheet" type="text/css" media="screen" href="/sfDoctrinePlugin/css/global.css" />
<link rel="stylesheet" type="text/css" media="screen" href="/sfDoctrinePlugin/css/default.css" />

<?php 
    if ( $importError[ 'isFixable' ] )
        echo link_to( 'Resolve', strtolower( $importError[ 'model' ] ) . '/resolve', array( 'popup' => true, 'query_string' => 'import_error_id=' . $importError[ 'id' ] ))
?>

<pre style="background-color:white; padding:10px; border:solid 1px #ddd; overflow: auto;">
    Model: <?php echo $importError[ 'model' ]; ?>
</pre>
<pre style="background-color:white; padding:10px; border:solid 1px #ddd; overflow: auto;">
    Exception Class: <?php echo $importError[ 'exception_class' ]; ?>
</pre>
<pre style="background-color:white; padding:10px; border:solid 1px #ddd; overflow: auto;">
    Trace: <?php echo $importError[ 'trace' ]; ?>
</pre>
<pre style="background-color:white; padding:10px; border:solid 1px #ddd; overflow: auto;">
    Message: <?php echo $importError[ 'message' ]; ?>
</pre>
<pre style="background-color:white; padding:10px; border:solid 1px #ddd; overflow: auto;">
    Log: <?php echo $importError[ 'log' ]; ?>
</pre>
<pre style="background-color:white; padding:10px; border:solid 1px #ddd; overflow: auto;">
    Error Object: <?php print_r( $importError[ 'errorObject' ]->toArray() ); ?>
</pre>
<pre style="background-color:white; padding:10px; border:solid 1px #ddd; overflow: auto;">
    Created at: <?php echo $importError[ 'created_at' ]; ?>
</pre>
