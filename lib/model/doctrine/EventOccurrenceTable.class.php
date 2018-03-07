<?php

class EventOccurrenceTable extends Doctrine_Table
{

  /**
   * generates a vendor occurrence id
   *
   * @param integer $eventId
   * @param integer $poiId
   * @param string $startDate
   * @return string
   */
  public function generateVendorEventOccurrenceId( $eventId, $poiId, $startDate )
  {
    if ( !empty( $eventId ) && !empty( $poiId ) && !empty( $startDate ) )
    {
      return $eventId . '_' . $poiId . '_' . date( 'YmdHis', strtotime( $startDate ) );
    }
    else
    {
      if( empty( $eventId ) )
      {
        throw new Exception( 'EventOccurrenceTable::generateVendorEventOccurrenceId expects non-empty $eventId' );
      }
      elseif( empty( $poiId ) )
      {
        throw new Exception( 'EventOccurrenceTable::generateVendorEventOccurrenceId expects non-empty $poiId' );
      }
      elseif (  empty( $startDate ) )
      {
        throw new Exception( 'EventOccurrenceTable::generateVendorEventOccurrenceId expects non-empty $startDate' );
      }
    }
  }


  /**
   *
   * @return string
   */
  public function getVendorUidFieldName()
  {
    return 'vendor_event_occurrence_id';
  }
}
