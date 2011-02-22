<?php

class LinkingVendorEventCategoryTable extends Doctrine_Table
{
    public function mapCategoriesTo( $mapToCategoryID, array $mapCategoriesList )
    {
        if( !is_numeric($mapToCategoryID) || intval($mapToCategoryID) <= 0 )
            throw new LinkingVendorEventCategoryTableException( 'Invalid $mapToCategoryID in parameter' );

        return $this->createQuery()
            ->update( 'LinkingVendorEventCategory' )
            ->set( 'vendor_event_category_id', '?', $mapToCategoryID )
            ->whereIn( 'vendor_event_category_id ', $mapCategoriesList )
            ->execute();
    }
}

class LinkingVendorEventCategoryTableException extends Exception{}