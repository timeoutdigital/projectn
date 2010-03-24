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
      throw new Exception( 'one or more of the passed parameters($eventId, $poiId, $startDate) is empty' );
    }
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
    return 'vendor_event_occurrence_id';
  }

  /**
   * search table for an EventOccurence that's equivalent. i.e. the same:
   * - vendor_event_occurrence_id
   * - booking_url
   * - start_date
   * - start_time
   * - end_date
   * - end_time
   * - utc_offset
   * - event_id
   * - poi_id
   */
  public function findEquivalents( EventOccurrence $record )
  {
    $query = $this->createQuery( 'e' );

    $columns = $this->getColumnNames();

    foreach( $columns as $column )
    {
      if( in_array( $column, array( 'id' ) ) )
        continue;

      if( $record[$column] )
      {
        $query->addWhere( "e.$column = ? " , $record[$column] );
      }
      else
      {
        $query->addWhere( "e.$column IS NULL" );
      }
    }

    return $query->execute();
  }

  public function hasEquivalent( EventOccurrence $eventOccurrence )
  {
    return $this->findEquivalents( $eventOccurrence )->count() > 0;
  }
}
