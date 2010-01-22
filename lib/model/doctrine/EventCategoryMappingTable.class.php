<?php

class EventCategoryMappingTable extends Doctrine_Table
{



  public function findByVendorId( $vendorId )
  {

    return $this->createQuery('ecm')
                ->leftJoin( 'ecm.VendorEventCategory vec' )
                ->leftJoin( 'ecm.EventCategory ec' )
                ->where('vec.vendor_id = ?', $vendorId)
                ->execute();

  }


}
