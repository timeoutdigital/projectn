<?php

class LinkingEventCategoryTable extends Doctrine_Table
{
    public function mapCategoriesTo( $mapToCategoryID, array $mapCategoriesList )
    {
        if( !is_numeric($mapToCategoryID) || intval($mapToCategoryID) <= 0 )
            throw new LinkingVendorPoiCategoryTableException( 'Invalid $mapToCategoryID in parameter' );

        return $this->createQuery()
            ->update( 'LinkingVendorEventCategory' )
            ->set( 'vendor_event_category_id', '?', $mapToCategoryID )
            ->whereIn( 'vendor_event_category_id ', $mapCategoriesList )
            ->execute();
    }
}
