<?php

class VendorPoiCategoryTable extends Doctrine_Table
{

  public function findMappingsByVendorId( $id )
  {
    $q = Doctrine_Query::create()
                  ->select('*')
                  ->from('PoiCategory pc')
                  ->leftJoin('pc.VendorPoiCategories vpc')
                  ->where('vpc.vendor_id=?', $id);

      return $q->execute();
  }
  
}
