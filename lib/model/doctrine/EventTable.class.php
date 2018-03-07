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

  public function findForExport( Vendor $vendor, $includeExpired = false )
  {
    $dateTime = new DateTime;
    $dateString = $dateTime->format( 'Y-m-d' );

    $query = $this->createQuery( 'event' )
                  ->leftJoin( 'event.EventOccurrence occurrence' )
                    ->leftJoin( 'event.EventProperty eventProperties' )
                        ->leftJoin( 'event.EventMedia eventMedia' )
                            ->leftJoin( 'event.VendorEventCategory vendorCat' )
                                ->leftJoin( 'vendorCat.UiCategory' )
                  ->addWhere( 'event.vendor_id = ? ',  $vendor['id'] )
                  ->addOrderBy( 'occurrence.poi_id' );

    /**
     * #849 Require DataEntry to Export Expired Events,
     * This switch enable us to Export Expired events when calling findForExport()
     */
    if( $includeExpired !== true )
    {
        $query->addWhere( 'occurrence.start_date >= ?', $dateString );
    }

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

  public function getVendorEventCategoryByVendorId( $vendorID, $order = 'name ASC' )
    {
        $query = Doctrine_Query::create( )
            ->from( 'VendorEventCategory v' )
            ->andWhere( 'vendor_id = ?', $vendorID )
            ->orderBy( $order );

        return $query->execute();
    }
}
