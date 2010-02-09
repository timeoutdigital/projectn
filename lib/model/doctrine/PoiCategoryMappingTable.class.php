<?php

class PoiCategoryMappingTable extends Doctrine_Table
{
  public function findByVendorId( $vendorId )
  {
    return $this->createQuery('pcm')
                ->leftJoin( 'pcm.VendorPoiCategory vpc' )
                ->leftJoin( 'pcm.PoiCategory pc' )
                ->where('vpc.vendor_id = ?', $vendorId)
                ->execute();
  }
}
