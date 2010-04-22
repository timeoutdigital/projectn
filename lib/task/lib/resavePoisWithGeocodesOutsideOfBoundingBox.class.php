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
class resavePoisWithGeocodesOutsideOfBoundingBox
{
  public function run()
  {
    $specification = new geocodeIsWithinBoundary();

    foreach( $specification->getFailingPois() as $failingPoi )
    {
      $failingPoi[ 'latitude'  ] = null;
      $failingPoi[ 'longitude' ] = null;
      $failingPoi->save();
    }
  }
}
