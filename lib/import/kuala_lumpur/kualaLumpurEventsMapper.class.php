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

  public function mapEvents()
  {
    foreach( $this->xml->eventDetails as $event )
    {
        try {
          if( $this->isFilm( $event ) )
            continue;

          $eventId = (string) $event->id;

          $record = $this->dataMapperHelper->getEventRecord( $eventId );

          $record['vendor_event_id']   = $eventId;
          $record['name']              = (string) $event->title;
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

          try {
            $record->addMediaByUrl( (string) $event->medias->big_image );
          }
          catch( Exception $exception )
          {
            $this->notifyImporterOfFailure($exception);
          }


          $occurrence = $this->dataMapperHelper->getEventOccurrenceRecord( $record, $eventId );

          // occurrences of this event will be deleted and will be added again
          // this causes auto_increment id's to increment each time, we may run out of ids at some point
          // so we will keep the old id

          if( $occurrence !== false )
          {
            $occurrenceId = $occurrence[ 'id' ];
            $occurrence = new EventOccurrence( );
            $occurrence[ 'id' ] = $occurrenceId;
          }

          $occurrence[ 'vendor_event_occurrence_id' ] = $eventId;
          $occurrence['start_date'] = (string) $event->occurrences->start_date;
          $occurrence['start_time'] = stringTransform::extractStartTime( (string) $event->occurrences->start_time );
          $occurrence['end_date'] = (string) $event->occurrences->end_date;
          $occurrence['utc_offset'] = $this->vendor->getUtcOffset( (string) $event->occurrences->start_date );

          $poi = $this->dataMapperHelper->getPoiRecord( (string)  $event->address_details->venue_id   );
          if( is_null( $poi['id'] ) )
          {
            $this->notifyImporterOfFailure( new Exception( 'Could not find Kuala Lumpur Poi with vendor_poi_id of '. (string)  $event->address_details->venue_id ), $occurrence );
            continue;
          }

          $occurrence['Poi'] = $poi;
          $record['EventOccurrence']->delete();
          $record['EventOccurrence'][] = $occurrence;

          $this->notifyImporter( $record );

        }
        catch( Exception $exception )
        {
            $this->notifyImporterOfFailure($exception, $record);
        }
    }
  }

  private function isFilm( $event )
  {
    return (string) $event->categories->category     == 'Film'
        && ( (string) $event->categories->subCategory  == 'Screenings' || (string) $event->categories->subCategory  == 'Movies'  )
        ;
  }

}
