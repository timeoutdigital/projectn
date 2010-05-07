<?php

class kualaLumpurEventsMapper extends DataMapper
{
  /**
   * @var Vendor
   */
  private $vendor;

  /**
   * @var SimpleXMLElement
   */
  private $xml;

  public function __construct( Vendor $vendor, SimpleXMLElement $xml)
  {
    $this->vendor = $vendor;
    $this->xml = $xml;
    $this->dataMapperHelper = new ProjectNDataMapperHelper( $vendor );
  }

  public function mapVenues()
  {
    foreach( $this->xml->eventDetails as $event )
    {
      if( $this->isFilm( $event ) )
        continue;

      $eventId = (string) $event->id;

      $record = $this->dataMapperHelper->getEventRecord( $eventId );

      $record['vendor_event_id']   = $eventId;
      $record['name']              = (string) $event->name;
      $record['url']               = (string) $event->url;
      $record['short_description'] = (string) $event->short_description;
      $record['description']       = (string) $event->descripton;
      $record['price']             = (string) $event->price;
      $record['Vendor']            = $this->vendor;

      $record->addVendorCategory( array(
        $event->categories->category,
        $event->categories->subCategory,
      ), 
      $this->vendor );

      //$event->addMediaByUrl( (string) $event->medias->big_image );

      $occurrence = $this->dataMapperHelper->getEventOccurrenceRecord( $record, $eventId );
      $occurrence[ 'vendor_event_occurrence_id' ] = $eventId;
      $occurrence['start_date'] = (string) $event->occurrences->start_date;
      $occurrence['utc_offset'] = $this->vendor->getUtcOffset( $start[ 'datetime' ] );

      $poi = $this->dataMapperHelper->getPoiRecord( (string) $event->occurrences->venue, $this->vendor['id'] );

      if( !$poi->exists() )
      {
        $this->notifyImporterOfFailure( new Exception( 'Could not find Lisbon Poi with vendor_poi_id of '. $placeid ), $occurrence );
        continue;
      }

      $occurrence['Poi'] = $poi;

      $record['EventOccurrence'][] = $occurrence;

      $this->notifyImporter( $record );
    }
  }

  private function isFilm( $event )
  {
    return (string) $event->categories->category     == 'Film'
        && (string) $event->categories->subCategory  == 'Screenings'
        && (string) $event->categories->genre        != ''
        ;
  }
}
