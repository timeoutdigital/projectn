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
    $rootElement->setAttribute( 'name', $this->vendor['name'] );
    $rootElement->setAttribute( 'modified', $this->modifiedTimeStamp );
    
    foreach( $data as $event )
    {
      //event
      $eventElement = $rootElement->appendChild( new DOMElement('event') );
      $eventElement->setAttribute( 'veid', 'veid_' . '1234' );
      $eventElement->setAttribute( 'modified', $this->modifiedTimeStamp );

      //event/name
      $nameElement = new DOMElement('name');
      $eventElement->appendChild( $nameElement );
      $nameElement->appendChild( $this->domDocument->createCDATASection( $event->getName() ) );

      //event/category
      foreach( $event['EventCategories'] as $category )
      {
        $eventElement->appendChild( new DOMElement( 'category', $category ) );
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
      $bookingUrl = $versionElement->appendChild( new DOMElement( 'booking-url' ) );
      $bookingUrl->appendChild( $this->domDocument->createCDATASection( $event->getBookingUrl() ) );

      //event/version/url
      $url = $versionElement->appendChild( new DOMElement( 'url' ) );
      $url->appendChild( $this->domDocument->createCDATASection( $event->getUrl() ) );

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
      foreach( $event[ 'EventOccurence' ] as $occurrence )
      {
        $showtimeElement = $eventElement->appendChild( new DOMElement( 'showtimes' ) );

        //event/showtimes/place
        $placeElement = $showtimeElement->appendChild( new DOMElement('place') );
        $placeElement->setAttribute( 'place-id', $occurrence->getPoiId() );

        //event/showtimes/occurrence
        $occurrenceElement = $showtimeElement->appendChild( new DOMElement( 'occurrence' ) );

        //event/showtimes/occurrence/booking-url
        $occurrenceBookingUrl = $occurrenceElement->appendChild( new DOMElement( 'booking-url' ) );
        $occurrenceBookingUrl->appendChild( $this->domDocument->createCDATASection( $event->getBookingUrl() ) );

        //event/showtimes/occurrence/time
        $timeElement = $occurrenceElement->appendChild( new DOMElement('time') );
        
        //event/showtimes/occurrence/time/start-date
        $timeElement->appendChild( new DOMElement( 'start-date', $occurrence->getStart() ) );
        $timeElement->appendChild( new DOMElement( 'end-date', $occurrence->getEnd() ) );
        $timeElement->appendChild( new DOMElement( 'utc-offset', $occurrence->getUtcOffset() ) );
      }
    }

    return $domDocument;
    
    foreach( $data as $event )
    {

      //event/showtimes
      foreach( $event[ 'EventOccurence' ] as $occurrence )
      {
      }
    }

    return $xmlElement;
  }

}
?>
