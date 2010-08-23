<?php
/**
 * Barcelona event mapper
 *
 * @package projectn
 * @subpackage barcelona.import.lib.unit
 *
 * @author Peter Johnson <peterjohnson@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class barcelonaEventsMapper extends barcelonaBaseMapper
{
  public function mapEvents()
  {
    for( $i=0, $eventElement = $this->xml->event[ 0 ]; $i<$this->xml->event->count(); $i++, $eventElement = $this->xml->event[ $i ] )
    {
      try{
          // Get Venue Id
          $vendorEventId = $this->clean( (string) $eventElement['id'] );

          $event = Doctrine::getTable( 'Event' )->findOneByVendorIdAndVendorEventId( $this->vendor['id'], $vendorEventId );
          if( $event === false )
            $event = new Event();

          // Column Mapping
          $event['vendor_event_id']         = $vendorEventId;
          $event['review_date']             = $this->clean( (string) $eventElement->review_date );
          $event['name']                    = $this->clean( (string) $eventElement->name );
          $event['short_description']       = $this->fixHtmlEntities( $this->clean( (string) $eventElement->short_description ) );
          $event['description']             = $this->fixHtmlEntities( $this->clean( (string) $eventElement->description ) );
          $event['booking_url']             = $this->clean( (string) $eventElement->booking_url );
          $event['url']                     = $this->clean( (string) $eventElement->url );
          $event['price']                   = $this->clean( str_replace(PHP_EOL, '', (string) $eventElement->price));
          $event['Vendor']                  = clone $this->vendor;

          // Timeout Link
          if( $this->clean( (string) $eventElement->timeout_url ) != "" )
              $event->setTimeoutLinkProperty( $this->clean( (string) $eventElement->timeout_url ) );

          //Critics Choice
          $event->setCriticsChoiceProperty( strtolower( $this->clean( (string) $eventElement->critics_choice ) ) == 'y' );

          // Categories
          $cats = $this->extractCategories( $eventElement );
          foreach( $cats as $cat ) $event->addVendorCategory( $cat );

          // Add First Image Only
          //$medias = array();
          //foreach( $eventElement->medias->media as $media ) $medias[] = (string) $media;
          //if( !empty( $medias ) ) $this->addImageHelper( $event, $medias[0] );

          // Delete Occurences
          // occurrences of this event will be deleted and will be added again
          // this causes auto_increment id's to increment each time, we may run out of ids at some point
          // so we will keep the old ids
          $oldIds = array();

          foreach ($event['EventOccurrence'] as $oldOccurrence)
          {
            $oldIds[] = $oldOccurrence[ 'id' ];
          }

          $event['EventOccurrence']->delete();

          // Create Occurences
          for( $ii=0, $xmlOccurrence = $eventElement->occurrences->occurrence[ 0 ]; $ii<$eventElement->occurrences->occurrence->count(); $ii++, $xmlOccurrence = $eventElement->occurrences->occurrence[ $ii ] )
          {
             try
             {
                  // Some Events Don't Have Occcurrences
                  if( !$xmlOccurrence )
                  {
                    $this->notifyImporterOfFailure( new Exception( 'No Occurences in Feed for Vendor Event ID: ' . $vendorEventId . ' in Barcelona.' ) );
                    break;
                  }

                  // Only Get End Date At The Moment (saves CPU,RAM?).
                  $end_date   = $this->clean( (string) $xmlOccurrence->end_date );

                  // Ignore Ongoing Events
                  if( $end_date == 'ongoing' )
                  {
                    $this->notifyImporterOfFailure( new Exception( 'Rejected Ongoing Event for Vendor Event ID: ' . $vendorEventId . ' in Barcelona.' ) );
                    break; // @todo, change this to continue, just in case they come out of order, currently takes too long to get through 8000+ occurrences on one event.
                  }

                  // Some Events Have Thousands of Occcurrences
                  if( strtotime( $end_date ) > strtotime( "+3 month") )
                  {
                    $this->notifyImporterOfFailure( new Exception( 'Rejected Occurence Over 3 Months old for Vendor Event ID: ' . $vendorEventId . ' in Barcelona.' ) );
                    break; // @todo, change this to continue, just in case they come out of order, currently takes too long to get through 8000+ occurrences on one event.
                  }

                  // Get Start Date
                  $start_date = $this->clean( (string) $xmlOccurrence->start_date );

                  // Only Import Occurences from a Single Day at the Moment.
                  if( $start_date != $end_date )
                  {
                    $this->notifyImporterOfFailure( new Exception( 'Could not determine occurrence frequency for Vendor Event ID: ' . $vendorEventId . ' in Barcelona.' ) );
                    break;
                  }

                  // Find POI
                  $poi = Doctrine::getTable( 'Poi' )->findByVendorPoiIdAndVendorLanguage( $this->clean( (string) $xmlOccurrence->venue ), 'ca' );
                  if( !$poi )
                  {
                    $this->notifyImporterOfFailure( new Exception( 'Could not find a Poi with id: ' . $this->clean( (string) $xmlOccurrence->venue ) . ' for Vendor Event ID ' . $vendorEventId . ' in Barcelona.' ) );
                    continue;
                  }

                  $occurrence = new EventOccurrence();

                  //reusing the old id
                  $occurrence['id'] = array_pop( $oldIds );

                  $occurrence[ 'vendor_event_occurrence_id' ]     = Doctrine::getTable('EventOccurrence')->generateVendorEventOccurrenceId( $vendorEventId, $poi['id'], $this->clean( (string) $xmlOccurrence->start_date ) );
                  $occurrence[ 'booking_url' ]                    = $this->clean( (string) $xmlOccurrence->booking_url );
                  $occurrence[ 'start_date' ]                     = $start_date;
                  $occurrence[ 'end_date' ]                       = $end_date;
                  $occurrence[ 'utc_offset' ]                     = $poi['Vendor']->getUtcOffset();
                  $occurrence[ 'Poi' ] = $poi;

                  $event['Vendor'] = $poi['Vendor'];

                  $event['EventOccurrence'][] = $occurrence;
             }
             catch( Exception $exception )
             {
                 $this->notifyImporterOfFailure( $exception, $occurrence );
             }
          }

          // If Event has No Occurrences, don't import it.
          if( count( $event['EventOccurrence'] ) == 0 )
          {
              $this->notifyImporterOfFailure( new Exception( 'Could not find any reliable occurrences for Vendor Event ID: ' . $vendorEventId . ' in Barcelona.' ) );
              continue;
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
