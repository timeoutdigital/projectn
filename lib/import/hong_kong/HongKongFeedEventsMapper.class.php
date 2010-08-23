<?php
/**
 * hong kong events mapper
 *
 * @package projectn
 * @subpackage hongkong.import.lib.unit
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */
class HongKongFeedEventsMapper extends HongKongFeedBaseMapper
{

  public function mapEvents()
  {
      
      foreach ($this->fixIteration($this->xml->channel->events->event)  as $eventElement)
      {
          try{
              // Get Venue Id
              $vendorEventId = (int) $eventElement['id'];

              $event = Doctrine::getTable( 'Event' )->findByVendorEventIdAndVendorLanguage( $vendorEventId, 'en-HK' );
              if( !$event ) $event = new Event();

              // Column Mapping
              $event['vendor_event_id']         = (string) $vendorEventId;
              $event['name']                    = (string) $eventElement->name;
              $event['short_description']       = $this->fixHtmlEntities( (string) $eventElement->short_description ); // Requires Double Entity Decoding
              $event['description']             = $this->fixHtmlEntities( (string) $eventElement->description ); // Requires Double Entity Decoding
              $event['url']                     = (string) $eventElement->url;
              $event['price']                   = (string) $eventElement->price;
              $event['rating']                  = $this->roundNumberOrReturnNull( (string) $eventElement->rating );

              $event['Vendor'] = $this->vendor;

              // Timeout Link
              if( (string) $eventElement->timeout_url != "" )
                  $event->addProperty( "Timeout_link", (string)$eventElement->timeout_url );
          
              // Delete Occurences
              $event['EventOccurrence']->delete();

              // Create Occurences
              foreach( $eventElement->occurrences->occurrence as $xmlOccurrence )
              {
                  try{

                      // Events are related to POI's, GET EXISTING POI's OR Exit without adding!
                      $poi = Doctrine::getTable( 'Poi' )->findOneByVendorPoiIdAndVendorId( (string) $xmlOccurrence->venue_id, $this->vendor['id'] );
                      if( !$poi )
                      {
                          $this->notifyImporterOfFailure( new Exception( 'Could not find a Poi with id: ' . (string) $xmlOccurrence->venue_id . ' for Event ' . $vendorEventId . ' in ' . $this->vendor['city'] . "." ) );
                          continue;
                      }

                      // Get Occurrence Id, Because Hong Kong Occurrent ID == POI vendor poi id, we are using generateVendorEventOccurrenceId
                      // generate unique ID.
                      $vendor_occurence_id = Doctrine::getTable('EventOccurrence')->generateVendorEventOccurrenceId($event['vendor_event_id'],$poi['vendor_poi_id'], (string) $xmlOccurrence->start_date); // (int) $xmlOccurrence[ 'id' ];

                      $occurrence = new EventOccurrence();
                      $occurrence[ 'vendor_event_occurrence_id' ]     = $vendor_occurence_id;
                      $occurrence[ 'start_date' ]                     = (string) $xmlOccurrence->start_date;
                      $occurrence[ 'start_time' ]                     = $this->extractTimeOrNull( (string) $xmlOccurrence->start_time );
                      $occurrence[ 'end_date' ]                       = (string) $xmlOccurrence->end_date;
                      $occurrence[ 'end_time' ]                       = $this->extractTimeOrNull( (string) $xmlOccurrence->end_time );
                      $occurrence[ 'utc_offset' ]                     = $this->vendor->getUtcOffset();
                      $occurrence[ 'Poi' ] = $poi;


                      // Add Categories (use Tags as Category!) // Require Vendor
                      $categories = array();
                      foreach( $eventElement->tags->tag as $category ) $categories[] = stringTransform::mb_trim((string) $category); // mb_trim to trim any Whitespaces in Cat names
                      $event->addVendorCategory( $categories, $event['Vendor']['id'] );

                      // Add to Events Occurrences
                      $event['EventOccurrence'][] = $occurrence;

                  }catch(Exception $exception){
                      //$this->notifyImporterOfFailure( $exception, $occurrence );
                      print_r($exception->getMessage() . ' -Failed to add Occurrence for vendor ID@' . $vendorEventId . ' - vendor event id@' . $event['vendor_event_id'] . ' on venue id@' . (string) $xmlOccurrence->venue_id .PHP_EOL);
                  }

              } // Foreach Events occurrences

              // Save Event
              $this->notifyImporter( $event );
              unset($event);

          }catch(Exception $exception){
              $this->notifyImporterOfFailure($exception, $event);
              print_r($exception->getMessage() . ' - failed to add Event for vendor event id@'. $event['vendor_event_id'].PHP_EOL);
          }
      }// END foreach
  }

}

?>
