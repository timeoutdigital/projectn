<?php

class EventOccurrenceTable extends Doctrine_Table
{

  /*
   * generates a vendor occurrence id
   *
   */
  public function generateVendorEventOccurrenceId( $eventId, $poiId, $startDate )
  {
    if ( !empty( $eventId ) && !empty( $poiId ) && !empty( $startDate ) )
    {
      return $eventId . '_' . $poiId . '_' . date( 'YmdHis', strtotime( $startDate ) );
    }
    else
    {
      throw new Exception( 'one or more of the passed parameters($eventId, $poiId, $startDate) is empty' );
    }
  }

}
