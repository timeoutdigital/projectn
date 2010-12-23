<?php
    if( $poi && $poi->isDuplicate() )
    {
        printf( '<a href="%s" title="Duplicate of: %s">of #%s</a>', url_for('poi_edit', $poi['MasterPoi'][0] ), $poi['MasterPoi'][0]['poi_name'] ,$poi['MasterPoi'][0]['id'] );
    }
    else if( $poi && $poi->isMaster() )
    {
        echo 'Master';
    }
    else
    {
        echo '-';
    }
?>