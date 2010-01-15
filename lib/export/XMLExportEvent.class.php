<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of XMLExportEventclass
 *
 * @author ralph
 */
class XMLExportEvent extends XMLExport
{

  public function __construct( $vendor, $destination )
  {
    parent::__construct(  $vendor, $destination, 'Event' );
  }
  
  public function generateXML( $data )
  {
    $xmlElement = new SimpleXMLElement( '<vendor-events />' );

   //vendor_event
    $xmlElement->addAttribute( 'vendor', $this->vendor->getName() );
    $xmlElement->addAttribute( 'modified', $this->modifiedTimeStamp );
    
    foreach( $data as $event )
    {
      $eventTag = $xmlElement->addChild( 'event' );
      $eventTag->addAttribute( 'veid', 'veid_' . '1234' );
      $eventTag->addAttribute( 'modified', $this->modifiedTimeStamp );

      $eventTag->addChild( 'name', htmlspecialchars( $event->getName() ) );

      $versionTag = $eventTag->addChild( 'version' );
      $versionTag->addAttribute( 'lang', 'en' );

      $versionTag->addChild( 'name', htmlspecialchars( $event->getName() ) );

      $showtimes = $eventTag->addChild( 'showtimes' );

      $place = $showtimes->addChild( 'place' );

      $place->addAttribute('place-id', 'placeid');

      $occurrenceTag = $place->addChild( 'occurrence' );

      $time = $occurrenceTag->addChild( 'time' );

      $time->addChild('start_date', '2010-01-31 21:00:20' );

      $time->addChild('utc_offset', '-05:00' );
    }

    return $xmlElement;
  }





}
?>
