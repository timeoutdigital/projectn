<?php

class EventTable extends Doctrine_Table
{

  /*
   * @todo debug this function and possibly remove the 
   * following line in XmlExportEvent
   * 
   * if ( $eventOccurrence[ 'Event' ] != $event ) continue;
   * 
   * and use this function instead
   */
  public function findWithOccurrencesOrderedByPois()
  {
    $query = $this->createQuery()
                  ->select( '*' )
                  ->leftJoin( 'event.EventOccurrence eo')
                  ->orderBy( 'eo.poi_id' );

    return $query->execute();
  }

  /**
   * Get the name of the vendor's uid fieldname, this is a temporary solution
   * @todo rename Poi, Events, Movies etc to have vendor_uid field instead
   * of vendor_<model name>_id to allow polymorphism
   *
   * @return string
   */
  public function getVendorUidFieldName()
  {
    return 'vendor_event_id';
  }

  /**
   * @param Vendor $vendor
   * @param DateTime $dateTime
   *
   * @returns array
   */
  public function findByVendorAndStartsFromAsArray( Vendor $vendor, DateTime $dateTime = null )
  {
    if( is_null( $dateTime ) )
    {
      $dateTime = new DateTime;
    }

    $dateString = $dateTime->format( 'Y-m-d' );

    $query = $this->createQuery( 'event' )
                  ->leftJoin( 'event.EventOccurrence occurrence' )
                  ->leftJoin( 'event.EventCategory' )
                  ->leftJoin( 'event.EventProperty eventProperties' )
                  ->leftJoin( 'event.EventMedia eventMedia' )
                  ->leftJoin( 'event.VendorEventCategory' )
                  ->addWhere( 'event.vendor_id = ? ',  $vendor['id'] )
                  ->addWhere( 'occurrence.start_date >= ?', $dateString )
                  ->addOrderBy( 'occurrence.poi_id' );

    return $query->execute( array(), Doctrine_Core::HYDRATE_ARRAY);
  }


  public function customFindByVendorId( $vendorID, $hydrate=true)
  {
      
      $query = $this->createQuery('event e')
                    ->leftJoin('e.EventCategory ec')
                    ->leftJoin('e.LinkingEventCategory')
                    ->addWhere('e.vendor_id=?', $vendorID);

      if($hydrate)
      {
         return  $query->execute( );
      }
      else
      {
         return  $query->execute( array(), Doctrine_Core::HYDRATE_ARRAY);
      }
     
  }

  public function findForExport( Vendor $vendor )
  {
    $dateTime = new DateTime;
    $dateString = $dateTime->format( 'Y-m-d' );

    $query = $this->createQuery( 'event' )
                  ->leftJoin( 'event.EventOccurrence occurrence' )
                  ->leftJoin( 'event.EventProperty eventProperties' )
                  ->leftJoin( 'event.EventMedia eventMedia' )
                  ->leftJoin( 'event.VendorEventCategory' )
                  ->addWhere( 'event.vendor_id = ? ',  $vendor['id'] )
                  ->addWhere( 'occurrence.start_date >= ?', $dateString )
                  ->addOrderBy( 'occurrence.poi_id' );

    return $query->execute( array(), Doctrine_Core::HYDRATE_ARRAY);
  }

  public function findByVendorEventIdAndVendorLanguage( $vendorEventId, $vendorLanguage )
  {
    $vendors = Doctrine::getTable( 'Vendor' )->findByLanguage( $vendorLanguage );

    $vendorIds = array();
    foreach( $vendors as $vendor )
      $vendorIds[] = $vendor[ 'id' ];

    $event = $this
      ->createQuery( 'e' )
      ->andWhere( 'e.vendor_event_id = ?', $vendorEventId )
      ->andWhereIn( 'e.vendor_id', $vendorIds )
      ->fetchOne()
      ;
    return $event;
  }
}
