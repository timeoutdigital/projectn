<?php

class VendorEventCategoryTable extends Doctrine_Table
{

  public function findMappingsByVendorId( $id )
  {
    $q = Doctrine_Query::create()
                  ->select('*')
                  ->from('EventCategory ec')
                  ->leftJoin('ec.VendorEventCategories vec')
                  ->where('vec.vendor_id=?', $id);

      return $q->execute();
  }

}
