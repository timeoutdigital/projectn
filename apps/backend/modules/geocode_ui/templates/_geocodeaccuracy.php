<?php
    // Get Accuracy
    $accuracy = '-';
    foreach( $t_venue['PoiMeta'] as $meta)
    {
        if( $meta['lookup'] == 'Geocode_accuracy' )
        {
            $accuracy = $meta['value'];
        }
    }
?>
<a href='javascript:getVenueDetails( "<?php echo $t_venue->getId(); ?> " )'> <?php echo $accuracy;?> </a>