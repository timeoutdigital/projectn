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
class eventsShouldHaveAtleastOneVendorCategory extends baseVerifyTask
{
  protected function verify()
  {
    $events = $this->getEventXml()->xpath( '//event' );

    $errorEvents = array();

    foreach( $events as $event )
    {
      $vendorCategories = $event->xpath( 'version/vendor-category' );
      if( count( $vendorCategories ) < 1 )
      {
        $errorEvents[] = $event->name;
      }
    }

    if( empty( $errorEvents ) )
    {
      $this->setMessage( 'Events should have atleast one vendor category: ok.' . PHP_EOL );
      return true;
    }
    else
    {
      $this->setMessage( 'Not all events have categories: ' . PHP_EOL . implode( PHP_EOL, $errorEvents ) );
      return false;
    }
  }
}
