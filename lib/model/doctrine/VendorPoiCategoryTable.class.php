<?php

class VendorPoiCategoryTable extends Doctrine_Table
{
    public function findConcatDuplicateCategoryIdBy( $vendor_id, $hydrationMode = Doctrine_Core::HYDRATE_RECORD  )
    {
        return $this->createQuery()
            ->select('GROUP_CONCAT( id ) as dupeIds')
            ->where('vendor_id = ? ', $vendor_id )
            ->groupBy('name, vendor_id')
            ->having('count(*) > 1')
            ->execute( array(), $hydrationMode );
    }

    public function findUnusedCategoriesBy( $vendor_id, $hydrationMode = Doctrine_Core::HYDRATE_RECORD )
    {
        return $this->createQuery('vpc')
            ->where('vpc.id NOT IN ( SELECT lvpc.vendor_poi_category_id FROM LinkingVendorPoiCategory lvpc )')
            ->andWhere('vpc.vendor_id = ? ', $vendor_id )
            ->execute( array(), $hydrationMode );
    }
}
