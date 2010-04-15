<?php 
/**
 * Checks that pois are not present multiple times
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Ralph Schwaninger <ralphschwaninger@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class noDuplicatePois extends baseVerifyTask
{

  protected function verify()
  {
    $pois = $this->getPoiXml()->xpath( '//entry' );

    $uniquePois = array();

    $uniqueNamePois = array();

    $errorPois = array();

    foreach( $pois as $poi )
    {
        $poiId = (string) $poi[ 'vpid' ];

        if ( in_array( $poiId, $uniquePois ) )
        {
            $errorPois[] = 'ID:' . $poiId;
        }
        else
        {
            $uniquePois[] = $poiId;
        }

        $poiName = (string) $poi->name;

        if ( in_array( $poiName, $uniqueNamePois ) )
        {
            $errorPois[] = 'NAME (warning only):' . $poiName;
        }
        else
        {
            $uniqueNamePois[] = $poiName;
        }
    }

    if( empty( $errorPois ) )
    {
      $this->setMessage( 'Pois check for duplicates: ok.' . PHP_EOL );
      return true;
    }
    else
    {
      $this->setMessage( 'The following duplicate pois where found: ' . PHP_EOL . implode( PHP_EOL, $errorPois ) );
      return false;
    }
  }

}
