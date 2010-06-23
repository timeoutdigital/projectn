<?php
/**
 * Sydney venues mapper
 *
 * @package projectn
 * @subpackage russia.import.lib.unit
 *
 * @author Peter Johnson <peterjohnson@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class RussiaFeedEventsMapper extends RussiaFeedBaseMapper
{
  /**
   * @var array
   */
  private $russianVendorIds;

  public function mapEvents()
  {
    for( $i=0, $eventElement = $this->xml->event[ 0 ]; $i<$this->xml->event->count(); $i++, $eventElement = $this->xml->event[ $i ] )
    {
      try{
          // Get Venue Id
          $vendorEventId = (int) $eventElement['id'];

          $event = Doctrine::getTable( 'Event' )->findByVendorEventIdAndVendorLanguage( $vendorEventId, 'ru' );
          if( !$event ) $event = new Event();

          // Column Mapping
          $event['review_date']             = (string) $eventElement->review_date;
          $event['vendor_event_id']         = (string) $vendorEventId;
          $event['name']                    = (string) $eventElement->name;
          $event['short_description']       = $this->fixHtmlEntities( (string) $eventElement->short_description );
          $event['description']             = $this->fixHtmlEntities( (string) $eventElement->description );
          $event['booking_url']             = (string) $eventElement->booking_url;
          $event['url']                     = (string) $eventElement->url;
          $event['price']                   = (string) $eventElement->price;
          $event['rating']                  = $this->roundNumberOrReturnNull( (string) $eventElement->rating );


          // Delete Occurences
          $event['EventOccurrence']->delete();
          
          // Create Occurences
          foreach( $eventElement->occurrences->occurrence as $xmlOccurrence )
          {
             try
             {
                  // This needs to be done in the for loop, as an event may have occurences at different POIs
                  // DO NOT declare $poi before this loop!
                  $poi = Doctrine::getTable( 'Poi' )->findOneByVendorPoiIdAndVendorId( (string) $xmlOccurrence->venue, $this->vendor['id'] );
                  if( !$poi )
                  {
                    $this->notifyImporterOfFailure( new Exception( 'Could not find a Poi with id: ' . (string) $xmlOccurrence->venue . ' for Event ' . $vendorEventId . ' in ' . $this->vendor['city'] . "." ) );
                    continue;
                  }

                  // Get Occurrence Id
                  $vendor_occurence_id = (int) $xmlOccurrence[ 'id' ];

                  $occurrence = new EventOccurrence();
                  $occurrence[ 'vendor_event_occurrence_id' ]     = $vendor_occurence_id;
                  $occurrence[ 'booking_url' ]                    = (string) $xmlOccurrence->booking_url;
                  $occurrence[ 'start_date' ]                     = (string) $xmlOccurrence->start_date;
                  $occurrence[ 'start_time' ]                     = $this->extractTimeOrNull( (string) $xmlOccurrence->start_time );
                  $occurrence[ 'end_date' ]                       = (string) $xmlOccurrence->end_date;
                  $occurrence[ 'end_time' ]                       = $this->extractTimeOrNull( (string) $xmlOccurrence->end_time );
                  $occurrence[ 'utc_offset' ]                     = $poi['Vendor']->getUtcOffset();
                  $occurrence[ 'Poi' ] = $poi;

                  $event['Vendor'] = $this->vendor;

                  // Categories (Requires Vendor)
                  $categories = array();
                  foreach( $eventElement->categories->category as $category ) $categories[] = (string) $category;
                  $event->addVendorCategory( $categories, $event['Vendor']['id'] );

                  // Add Images (Requires Vendor)
                  $processed_medias = array();
                  foreach( $eventElement->medias->media as $media )
                  {
                      $media_url = (string) $media;
                      if( !in_array( $media_url, $processed_medias ) )
                          $this->addImageHelper( $event, $media_url );
                      $processed_medias[] = $media_url;
                  }

                  $event['EventOccurrence'][] = $occurrence;
             }
             catch( Exception $exception )
             {
                 $this->notifyImporterOfFailure( $exception, $occurrence );
             }
          }
          
          $this->notifyImporter( $event );
      }
      catch( Exception $exception )
      {
          $this->notifyImporterOfFailure( $exception, $event );
      }
    }
  }
  
}
