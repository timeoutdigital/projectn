<?php
/**
 *
 * @package projectn
 * @subpackage export.lib
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd 2009
 *
 *
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
    $rootElement = $this->appendRequiredElement( $domDocument, 'vendor-events');

    //vendor_event
    $rootElement->setAttribute( 'vendor', $this->vendor['name'] );
    $rootElement->setAttribute( 'modified', $this->modifiedTimeStamp );

    foreach( $data as $event )
    {
      //event
      $eventElement = $this->appendRequiredElement( $rootElement, 'event' );
      $eventElement->setAttribute( 'id', $event['vendor_event_id'] );
      $eventElement->setAttribute( 'modified', $this->modifiedTimeStamp );

      //event/name
      $this->appendRequiredElement($eventElement, 'name', $event['name'], XMLExport::USE_CDATA );

      //event/category
      foreach( $event['EventCategories'] as $category )
      {
        $this->appendRequiredElement($eventElement, 'category', $category);
        //$category->free( );
      }


      //event/version
      $versionElement = $this->appendRequiredElement( $eventElement, 'version' );
      $versionElement->setAttribute( 'lang', 'en' );

      //event/version/name
      $this->appendRequiredElement($versionElement, 'name', $event['name'], XMLExport::USE_CDATA);

      //event/version/vendor-category
      foreach( $event['VendorEventCategories'] as $vendorEventCategory )
      {
        var_dump( !$vendorEventCategory->hasAccessor( 'name' ) );
        $this->appendRequiredElement($versionElement, 'vendor-category', $vendorEventCategory['name'], XMLExport::USE_CDATA);
        //$vendorEventCategory->free();
      }

      //event/version/short-description
      $this->appendNonRequiredElement($versionElement, 'short-description', $event['short_description'], XMLExport::USE_CDATA);

      //event/version/description
      $this->appendRequiredElement($versionElement, 'description', $event['description'], XMLExport::USE_CDATA);

      //event/version/booking-url
      if( !empty( $event['booking_url'] ) )
      {
        $this->appendNonRequiredElement($versionElement, 'booking_url', $event['booking_url'], XMLExport::USE_CDATA);
      }

      //event/version/url
      if( !empty( $event['url'] ) )
      {
        $this->appendNonRequiredElement($versionElement, 'url', $event['url'], XMLExport::USE_CDATA);
      }

      //event/version/price
      $this->appendNonRequiredElement($versionElement, 'price', $event['price'], XMLExport::USE_CDATA);

      //event/version/property
      foreach( $event[ 'EventProperty' ] as $property )
      {
        $propertyElement = $this->appendNonRequiredElement($versionElement, 'property', $property['value'], XMLExport::USE_CDATA);
        if ( $propertyElement instanceof DOMElement )
        {
          $propertyElement->setAttribute( 'key', $property[ 'lookup' ] );
        }

        //$property->free();
      }

      //event/showtimes
      $showtimeElement = $this->appendRequiredElement($eventElement, 'showtimes');

      //event/showtimes/place
      foreach( $event['Pois'] as $place)
      {
        $placeElement = $this->appendRequiredElement($showtimeElement, 'place');
        $placeElement->setAttribute( 'place-id', $place['id'] );

        foreach( $place['EventOccurrence'] as $eventOccurrence )
        {
          //event/showtimes/place/occurrence
          $occurrenceElement = $this->appendRequiredElement($placeElement, 'occurrence');

          //event/showtimes/occurrence/booking-url
          if( !empty( $event['booking_url'] ) )
          {
            $this->appendNonRequiredElement($occurrenceElement, 'booking_url', $event['booking_url']);
          }

          //event/showtimes/occurrence/time
          $timeElement = $this->appendRequiredElement($occurrenceElement, 'time');

          //event/showtimes/occurrence/time/start-date
          $this->appendRequiredElement($timeElement, 'start_date', $eventOccurrence['start']);
          $this->appendNonRequiredElement($timeElement, 'end_date', $eventOccurrence['end']);
          $this->appendRequiredElement($timeElement, 'utc_offset', $eventOccurrence['utc_offset']);

          //$eventOccurrence->free();
        }

        //$place->free();
      }

      //$event->free();
    }

    return $domDocument;
  }

}
?>
