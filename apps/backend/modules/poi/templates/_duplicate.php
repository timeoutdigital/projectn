<?php
    if( $poi && $poi->isDuplicate() )
    {
        printf( '<a href="#" title="This is a Duplicate POI" onclick="showMasterPoi( this, \'%s\'); return false;">Duplicate</a>', $poi['id'] );
    }
    else if( $poi && $poi->isMaster() )
    {
        printf( '<a href="#" onclick="showDuplicatePois( this, \'%s\' ); return false;" title="View duplicate pois">Master</a>',  $poi['id'] );
    }
    else
    {
        echo '-';
    }
?>