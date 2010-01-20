<?php

class EventTable extends Doctrine_Table
{
  public function findByVendorId( $vendorId )
  {
    $query = $this->createQuery( 'event' )
      ->select( '*' )
      ->leftJoin( 'event.Poi poi')
      ->where( 'poi.vendor_id = ?', $vendorId )
    ;

    return $query->execute();
  }
}
