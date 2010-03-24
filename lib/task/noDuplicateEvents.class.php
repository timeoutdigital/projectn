<?php 
/**
 * Checks that events are not present multiple times
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
class noDuplicateEvents extends baseVerifyTask
{

  protected function verify()
  {
    $events = $this->getEventXml()->xpath( '//event' );

    $uniqueEvents = array();

    $uniqueNameEvents = array();

    $errorEvents = array();

    foreach( $events as $event )
    {
        $eventId = (string) $event[ 'id' ];
        
        if ( in_array( $eventId, $uniqueEvents ) )
        {
            $errorEvents[] = 'ID:' . $eventId;
        }
        else
        {
            $uniqueEvents[] = $eventId;
        }
        
        $eventName = (string) $event->name;

        if ( in_array( $eventName, $uniqueNameEvents ) )
        {
            $errorEvents[] = 'NAME (warning only):' . $eventName;
        }
        else
        {
            $uniqueNameEvents[] = $eventName;
        }
    }

    if( empty( $errorEvents ) )
    {
      $this->setMessage( 'Events check for duplicates: ok.' . PHP_EOL );
      return true;
    }
    else
    {
      $this->setMessage( 'The following duplicate events where found: ' . PHP_EOL . implode( PHP_EOL, $errorEvents ) );
      return false;
    }
  }

}
