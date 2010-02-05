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

}
