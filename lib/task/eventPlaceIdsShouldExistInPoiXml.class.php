<?php 
/**
 * Checks place-ids in an event xml exist in the poi xml
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class eventPlaceIdsShouldExistInPoiXml
{
  private $task;

  public function __construct( $task )
  {
    $this->task = $task;
  }

  public function run()
  {
    $poiIds   = $this->extractPoiIds();
    $placeIds = $this->extractPlaceIds();
    $strays   = $this->valuesInSet1AreInSet2( $placeIds, $poiIds );

    if( empty( $strays ) )
    {
      echo 'It\'s allllll good!' . PHP_EOL;
      return true;
    }
    else
    {
      echo $this->createErrorMessage( $strays );
      return false;
    }
  }

  private function getPoiXml()
  {
    return $this->task->getPoiXml();
  }

  private function getEventXml()
  {
    return $this->task->getEventXml();
  }

  private function getOption( $option )
  {
    return $this->task->getOption( $option );
  }

  private function createErrorMessage( $errorValues )
  {
    $message = count( $errorValues ) . ' place-ids from ' . $this->getOption('event-xml') . 
               ' could not be found in ' . $this->getOption('poi-xml');

    return $message;
  }

  private function valuesInSet1AreInSet2( $set1, $set2 )
  {
    $strays = array();

    foreach( $set1 as $set1Value )
    {
      if( !in_array( $set1Value, $set2 ) )
        $strays[] = $set1Value;
    }
    return $strays;
  }

  private function extractPoiIds()
  {
    $poiIds = array();
    $xmlPoiIds = $this->getPoiXml()->xpath( '//@vpid' );

    foreach( $xmlPoiIds as $id )
    {
      $poiIds[] = (string) $id;
    }

    return array_unique($poiIds);
  }

  private function extractPlaceIds()
  {
    $placeIds = array();
    $occurrences = $this->getEventXml()->xpath( '//@place-id' );

    foreach( $occurrences as $occurrence )
    {
      $placeIds[] = $occurrence;
    }

    return array_unique($placeIds);
  }
}
