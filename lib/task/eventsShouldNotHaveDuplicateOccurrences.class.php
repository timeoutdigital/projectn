<?php 
/**
 * Checks occurrences for duplicates in the event xml
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
class eventsShouldNotHaveDuplicateOccurrences extends baseVerifyTask
{

  protected function verify()
  {
    $events = $this->getEventXml()->xpath( '//event' );

    $errorEvents = array();

    foreach( $events as $event )
    {
      $occurrenceArray = array();

      $places = $event->xpath( 'showtimes/place' );

      foreach( $places as $place )
      {
        $occurrences = $place->xpath( 'occurrence' );

        foreach( $occurrences as $occurrence )
        {
            $occurrenceArray[] = $this->_buildOccurrenceIdentString( $place, $occurrence );
        }
      }

      if ( 1 < count( $occurrenceArray ) )
      {
          $uniqueOccurrenceArray = array_unique( $occurrenceArray );
          $duplicateOccurrenceArray = array_diff_key( $occurrenceArray, $uniqueOccurrenceArray );

          if ( 0 < count( $duplicateOccurrenceArray ) )
          {
              $errorEvents[] = $event->name;
          }
      }
    }

    if( empty( $errorEvents ) )
    {
      $this->setMessage( 'Events should not have duplicate Occurrences: ok.' . PHP_EOL );
      return true;
    }
    else
    {
      $this->setMessage( 'The following events have duplicate occurrences: ' . PHP_EOL . implode( PHP_EOL, $errorEvents ) );
      return false;
    }
  }

  private function _buildOccurrenceIdentString( $place, $occurrence )
  {
    $identString = $place[ 'place-id' ];
    $identString .= $occurrence->time->start_date;
    if ( isset( $occurrence->time->event_time ) ) $identString .= $occurrence->time->event_time;
    $identString .= $occurrence->time->utc_offset;

    return $identString;
  }

}
