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
   * Returns a string representation of the all Events in the database as XML
   *
   * @param Doctrine_Collection $data Collection of Events
   * @return string
   */
  protected function mapDataToDOMDocument( $data, $domDocument )
  {
    //$xmlElement = new SimpleXMLElement( '<vendor-events />' );
    $rootElement = $domDocument->appendChild(new DOMElement('vendor-events'));

    //vendor_event
    $rootElement->setAttribute( 'vendor', $this->vendor['name'] );
    $rootElement->setAttribute( 'modified', $this->modifiedTimeStamp );
    
    foreach( $data as $event )
    {
      //event
      $eventElement = $rootElement->appendChild( new DOMElement('event') );
      $eventElement->setAttribute( 'id', $event['vendor_event_id'] );
      $eventElement->setAttribute( 'modified', $this->modifiedTimeStamp );

      //event/name
      $nameElement = new DOMElement('name');
      $eventElement->appendChild( $nameElement );
      $nameElement->appendChild( $this->domDocument->createCDATASection( $event->getName() ) );

      //event/category
      foreach( $event['EventCategories'] as $category )
      {
        $categoryElement = $eventElement->appendChild( new DOMElement( 'category' ) );
        $categoryElement->appendChild( $this->domDocument->createCDATASection( $category ) );
      }

      //event/version
      $versionElement = $eventElement->appendChild( new DOMElement( 'version' ) );
      $versionElement->setAttribute( 'lang', 'en' );

      //event/version/name
      $nameElement = $versionElement->appendChild( new DOMElement( 'name' ) );
      $nameElement->appendChild( $this->domDocument->createCDATASection( $event->getName() ) );

      //event/version/vendor-category
      foreach( $event['VendorEventCategories'] as $vendorEventCategory )
      {
        $vendorCategoryElement = $versionElement->appendChild( new DOMElement( 'vendor-category' ) );
        $vendorCategoryElement->appendChild( $this->domDocument->createCDATASection( $vendorEventCategory['name'] ) );
      }

      //event/version/short-description
      $shortDescription = $versionElement->appendChild( new DOMElement( 'short-description' ) );//'short-description', htmlspecialchars( $event->getShortDescription() ));
      $shortDescription->appendChild( $this->domDocument->createCDATASection( $event->getShortDescription() ) );

      //event/version/short-description
      $shortDescription = $versionElement->appendChild( new DOMElement( 'description' ) );
      $shortDescription->appendChild( $this->domDocument->createCDATASection( $event->getDescription() ) );

      //event/version/booking-url
      if( !empty( $event['booking_url'] ) )
      {
        $bookingUrl = $versionElement->appendChild( new DOMElement( 'booking_url' ) );
        $bookingUrl->appendChild( $this->domDocument->createCDATASection( $event['booking_url'] ) );
      }

      //event/version/url
      if( !empty( $event['url'] ) )
      {
        $url = $versionElement->appendChild( new DOMElement( 'url' ) );
        $url->appendChild( $this->domDocument->createCDATASection( $event->getUrl() ) );
      }

      //event/version/price
      $price = $versionElement->appendChild( new DOMElement( 'price' ) );
      $price->appendChild( $this->domDocument->createCDATASection( $event->getPrice() ) );

      //event/version/property
      foreach( $event[ 'EventProperty' ] as $property )
      {
        $propertyElement = $versionElement->appendChild( new DOMElement( 'property' ) );
        $propertyElement->appendChild( $this->domDocument->createCDATASection( $property['value'] ) );
        $propertyElement->setAttribute( 'key', $property[ 'lookup' ] );
      }

      //event/showtimes
      foreach( $event[ 'EventOccurrence' ] as $occurrence )
      {
        $showtimeElement = $eventElement->appendChild( new DOMElement( 'showtimes' ) );

        //event/showtimes/place
        $placeElement = $showtimeElement->appendChild( new DOMElement('place') );
        $placeElement->setAttribute( 'place-id', $occurrence->getPoiId() );

        //event/showtimes/place/occurrence
        $occurrenceElement = $placeElement->appendChild( new DOMElement( 'occurrence' ) );

        //event/showtimes/occurrence/booking-url
        if( !empty( $event['booking_url'] ) )
        {
          $occurrenceBookingUrl = $occurrenceElement->appendChild( new DOMElement( 'booking_url' ) );
          $occurrenceBookingUrl->appendChild( $this->domDocument->createCDATASection( $event['booking_url'] ) );
        }

        //event/showtimes/occurrence/time
        $timeElement = $occurrenceElement->appendChild( new DOMElement('time') );
        
        //event/showtimes/occurrence/time/start-date
        $timeElement->appendChild( new DOMElement( 'start_date', $occurrence->getStart() ) );
        $timeElement->appendChild( new DOMElement( 'end_date', $occurrence->getEnd() ) );
        $timeElement->appendChild( new DOMElement( 'utc_offset', $occurrence->getUtcOffset() ) );
      }
    }

    return $domDocument;
  }

}
?>
