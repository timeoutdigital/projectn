<?php use_helper('jQuery'); ?>
<script type="text/javascript" src="/js/dygraph-combined.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="/sfDoctrinePlugin/css/global.css" />
<link rel="stylesheet" type="text/css" media="screen" href="/sfDoctrinePlugin/css/default.css" />

<ul>
    <?php echo $form; ?>
    <li><input type="button" value="Update Graph" onclick="refreshGraph();" /></li>
    <li class="wait-gif"><img src="/images/loading.gif" alt="Loading..."/></li>
</ul>

<div id="graphContainer">
    
    <?php
    $vendorIDS = array(); // used bellow in JS
    foreach( $a2zVendors as $vendor ): $vendorIDS[] = $vendor['id']; ?>
    <div id="vendor_<?php echo $vendor['id']?>">
        <h1><?php echo ucfirst( $vendor['city'] ) . " Export Stats."; ?></h1>
        <div id="graph_container_<?php echo $vendor['id']; ?>">loading...</div>
    </div>
    <?php endforeach; ?>

</div>

<script type="text/javascript">

    var isBusy = false;
    var loadspinner = '<img src="/images/loading-small.gif" alt="Loading..."/>';
    
    function refreshGraph()
    {
        <?php
        echo 'var vendorIDs = ["'.  implode( '","', $vendorIDS ).'"];';
        ?>

        if( isBusy )
        {
            alert( 'Please wait while loading last request...' );
            return;
        }
        // Clear existing
        for( var i in vendorIDs )
            jQuery( '#graph_container_' + vendorIDs[i] ).html( 'waiting...' );

        jQuery('.wait-gif').show();
        isBusy = true;
        _loadGraphRecursively( vendorIDs, 0 );

    }

    function _loadGraphRecursively( vendorIDs, index )
    {
        var vendor_id = vendorIDs[index];
        var container = '#graph_container_' + vendor_id;
        
        jQuery( container ).html( loadspinner );

        jQuery(container).load( 'exportstats/graph', {
                date_from_month   : $("#date_from_month").val(),
                date_from_day     : $("#date_from_day").val(),
                date_from_year    : $("#date_from_year").val(),
                date_to_month     : $("#date_to_month").val(),
                date_to_day       : $("#date_to_day").val(),
                date_to_year      : $("#date_to_year").val(),
                vendor_id         : vendor_id
            }, function(){
                
                index = index + 1; // Get next vendor id
                if( index >= vendorIDs.length )
                {
                    isBusy = false;
                    jQuery('.wait-gif').hide();
                    return; // End of call back
                }
                
                _loadGraphRecursively( vendorIDs, index ); // Recursively request
                
            });
    }

    // load defaults
    jQuery(document).ready(function() {
        refreshGraph();
    });

</script>