<?php

// Get Meta
$string = '';
foreach ( $t_venue['PoiMeta'] as $meta )
{
    if ( strtolower( $meta['lookup']) == 'duplicate' )
        $string = '✔';
}
echo $string;