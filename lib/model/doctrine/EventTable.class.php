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
   */
  public function findByVendorAndStartsFrom( Vendor $vendor, DateTime $dateTime = null )
  {
    if( is_null( $dateTime ) )
    {
      $dateTime = new DateTime;
    }

    $dateString = $dateTime->format( 'Y-m-d' );

    $query = $this->createQuery( 'event' )
                  ->leftJoin( 'event.EventOccurrence occurrence' )
                  ->leftJoin( 'occurrence.Poi poi' )
                  ->addWhere( 'occurrence.start_date >= ?', $dateString )
                  ;

    return $query->execute();
  }

}
