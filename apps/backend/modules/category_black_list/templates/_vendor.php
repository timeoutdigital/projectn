<?php
    if( !isset( $vendors ) )
    {
        $vendors = Doctrine::getTable( 'Vendor' )->findAll( 'KeyValue' );
    }

    echo ucwords( $vendors[ $vendor_category_black_list->vendor_id ] );
?>