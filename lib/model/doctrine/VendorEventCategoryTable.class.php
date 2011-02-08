<?php

class VendorEventCategoryTable extends Doctrine_Table
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
    
}
