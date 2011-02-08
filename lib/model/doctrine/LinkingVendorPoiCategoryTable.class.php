<?php

class LinkingVendorPoiCategoryTable extends Doctrine_Table
{
    public function mapCategoriesTo( $mapToCategoryID, array $mapCategoriesList )
    {
        if( !is_numeric($mapToCategoryID) || intval($mapToCategoryID) <= 0 )
            throw new LinkingVendorPoiCategoryTableException( 'Invalid $mapToCategoryID in parameter' );

        return $this->createQuery()
            ->update( 'LinkingVendorPoiCategory' )
            ->set( 'vendor_poi_category_id', '?', $mapToCategoryID )
            ->whereIn( 'vendor_poi_category_id ', $mapCategoriesList )
            ->execute();
    }

}


class LinkingVendorPoiCategoryTableException extends Exception{}