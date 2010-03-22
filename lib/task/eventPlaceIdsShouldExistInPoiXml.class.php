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
class eventPlaceIdsShouldExistInPoiXml extends baseVerifyTask
{
  protected function verify()
  {
    $poiIds   = $this->extractPoiIds();
    $placeIds = $this->extractPlaceIds();
    $strays   = $this->valuesInSet1AreInSet2( $placeIds, $poiIds );

    if( empty( $strays ) )
    {
      $this->setMessage( 'Event place-ids should exist in poi xml: ok' . PHP_EOL );
      return true;
    }
    else
    {
      $this->setMessage( $this->createErrorMessage( $strays ) );
      return false;
    }
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
