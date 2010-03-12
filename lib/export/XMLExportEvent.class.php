<?php
/**
 *
 * @package projectn
 * @subpackage export.lib
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @author Tim Bowler <timbowler@timeout.com>
 * 
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
    $xsd =  sfConfig::get( 'sf_data_dir') . DIRECTORY_SEPARATOR . 'xml_schemas'. DIRECTORY_SEPARATOR . 'latest' . DIRECTORY_SEPARATOR . 'vendor-events-1.4.xsd';
    parent::__construct(  $vendor, $destination, 'Event', $xsd );
  }

  protected function getData()
  {
    return Doctrine::getTable( 'Event' )->findByVendorAndStartsFrom( $this->vendor );
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
      $eventElement->setAttribute( 'id', $this->generateUID( $event ) );
      //$eventElement->setAttribute( 'modified', $this->modifiedTimeStamp );

      //event/name
      $this->appendRequiredElement($eventElement, 'name', $event['name'], XMLExport::USE_CDATA );

      //event/category
      foreach( $event['EventCategory'] as $category )
      {
        $this->appendRequiredElement($eventElement, 'category', $category);
        //$category->free( );
      }

      if( $event[ 'EventCategory' ]->count() < 1 )
      {
        $this->appendRequiredElement($eventElement, 'category', 'other');
      }

      //event/version
      $versionElement = $this->appendRequiredElement( $eventElement, 'version' );

      //Set theh language
      $langArray = explode('-',$this->vendor['language']);
      $versionElement->setAttribute( 'lang', $langArray[0] );

      //event/version/name
      $this->appendRequiredElement($versionElement, 'name', $event['name'], XMLExport::USE_CDATA);

      //event/version/vendor-category
      foreach( $event['VendorEventCategory'] as $vendorEventCategory )
      {
          $this->appendRequiredElement($versionElement, 'vendor-category', $vendorEventCategory['name'], XMLExport::USE_CDATA);
      }

      //event/version/short-description
      $cleanShortDescription = $this->cleanHtml( $event['short_description'] );
      $this->appendNonRequiredElement($versionElement, 'short-description', $cleanShortDescription, XMLExport::USE_CDATA);

      //event/version/description
      $cleanDescription = $this->cleanHtml( $event['description'] );
      $this->appendRequiredElement($versionElement, 'description', $cleanDescription, XMLExport::USE_CDATA);

      //event/version/booking-url
      $this->appendNonRequiredElement($versionElement, 'booking_url', $event['booking_url'], XMLExport::USE_CDATA);

      //event/version/url
      $this->appendNonRequiredElement($versionElement, 'url', $event['url'], XMLExport::USE_CDATA);

      //event/version/price
      $this->appendNonRequiredElement($versionElement, 'price', $event['price'], XMLExport::USE_CDATA);
      
      //event/version/media
      /*foreach( $event[ 'EventMedia' ] as $medium )
      {
        $mediaElement = $this->appendNonRequiredElement($versionElement, 'media', $medium['url'], XMLExport::USE_CDATA);
        if ( $mediaElement instanceof DOMElement )
        {
          $mediaElement->setAttribute( 'mime-type', $medium[ 'mime_type' ] );
        }
        //$medium->free();
      }*/

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
        $placeElement->setAttribute( 'place-id', $this->generateUID($place) );

        foreach( $place['EventOccurrence'] as $eventOccurrence )
        {
          
          if ( $eventOccurrence[ 'Event' ] != $event ) continue;

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
          
          $this->appendRequiredElement($timeElement, 'start_date', $eventOccurrence['start_date']);

          if( $eventOccurrence['start_time'] != '00:00:00' )//@todo fix this properly?
          $this->appendRequiredElement($timeElement, 'event_time', $eventOccurrence['start_time']);

          //$this->appendNonRequiredElement($timeElement, 'end_date', $eventOccurrence['end_date']); //not in schema...

          if( $eventOccurrence['end_time'] != '00:00:00' )//@todo fix this properly?
          $this->appendNonRequiredElement($timeElement, 'end_time', $eventOccurrence['end_time']);

          $this->appendRequiredElement($timeElement, 'utc_offset', $eventOccurrence['utc_offset']);

          //$eventOccurrence->free();
        }

        //$place->free();
      }

      $this->logExport->addItem( $event[ 'id' ], $event[ 'vendor_event_id' ] );

      //$event->free();
    }

    return $domDocument;
  }

}
?>
