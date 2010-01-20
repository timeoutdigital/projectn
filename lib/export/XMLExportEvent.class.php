<?php
/**
 * Creates the Event XML for a specified vendor. The XML is written to a file.
 *
 * @author ralph
 */
class XMLExportEvent extends XMLExport
{
  /**
   *
   * @param Vendor $vendor The Vendor to get Events for
   * @param String $destination Path to the file the export writes to
   */
  public function __construct( $vendor, $destination )
  {
    parent::__construct(  $vendor, $destination, 'Event' );
  }

  /**
   * Returns an XML representation of the all Events in the database
   *
   * @param Doctrine_Collection $data Collection of Events
   * @return SimpleXMLElement XML representation of the Events
   */
  protected function generateXML( $data )
  {
    $xmlElement = new SimpleXMLElement( '<vendor-events />' );

   //vendor_event
    $xmlElement->addAttribute( 'vendor', $this->vendor->getName() );
    $xmlElement->addAttribute( 'modified', $this->modifiedTimeStamp );
    
    foreach( $data as $event )
    {
      //event
      $eventTag = $xmlElement->addChild( 'event' );
      $eventTag->addAttribute( 'veid', 'veid_' . '1234' );
      $eventTag->addAttribute( 'modified', $this->modifiedTimeStamp );

      //event/name
      $eventTag->addChild( 'name', htmlspecialchars( $event->getName() ) );

      //event/category
      foreach( $event->getEventCategory() as $category )
      {
        $eventTag->addChild( 'category', $category );
      }

      //event/version
      $versionTag = $eventTag->addChild( 'version' );
      $versionTag->addAttribute( 'lang', 'en' );

      //event/version/name
      $versionTag->addChild( 'name', htmlspecialchars( $event->getName() ) );

      //event/version/vendor-category
      $versionTag->addChild( 'vendor-category', htmlspecialchars( $event->getVendorCategory() ) );

      //event/version/short-description
      $versionTag->addChild('short-description', htmlspecialchars( $event->getShortDescription() ));

      //event/version/short-description
      $versionTag->addChild('description', htmlspecialchars( $event->getDescription() ));

      //event/version/booking-url
      $versionTag->addChild('booking-url', htmlspecialchars( $event->getBookingUrl() ));
      
      //event/version/short-description
      $versionTag->addChild('url', htmlspecialchars( $event->getUrl() ));

      //event/version/booking-url
      $versionTag->addChild('price', htmlspecialchars( $event->getPrice() ));

      //event/showtimes
      foreach( $event['EventOccurence'] as $occurrence )
      {
        $showtimeTag = $eventTag->addChild( 'showtimes' );

        //event/showtimes/place
        $placeTag = $showtimeTag->addChild('place');

        $placeTag->addAttribute( 'place-id', $occurrence->getPoiId() );

        //event/showtimes/occurrence
        $occurrenceTag = $showtimeTag->addChild('occurrence');

        //event/showtimes/occurrence/booking-url
        $occurrenceTag->addChild( 'booking-url', $event->getBookingUrl() );

        //event/showtimes/occurrence/time
        $timeTag = $occurrenceTag->addChild( 'time' );

        //event/showtimes/occurrence/time/start-date
        $timeTag->addChild( 'start-date', $occurrence->getStart() );
        $timeTag->addChild( 'end-date', $occurrence->getEnd() );
        $timeTag->addChild( 'utc-offset', $occurrence->getUtcOffset() );
      }
    }

    return $xmlElement;
  }

}
?>
