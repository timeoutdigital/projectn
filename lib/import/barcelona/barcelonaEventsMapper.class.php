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

          $event = Doctrine::getTable( 'Event' )->findOneByVendorIdAndVendorEventId( $this->vendor['id'], (string) $eventElement['id'] );
          if( $event === false )
            $event = new Event();

          // Column Mapping
          $event['vendor_event_id']         = (string) $eventElement['id'];
          $event['review_date']             = (string) $eventElement->review_date;
          $event['name']                    = (string) $eventElement->name;
          $event['short_description']       = $this->fixHtmlEntities( (string) $eventElement->short_description );
          $event['description']             = $this->fixHtmlEntities( (string) $eventElement->description );
          $event['booking_url']             = (string) $eventElement->booking_url;
          $event['url']                     = (string) $eventElement->url;
          $event['price']                   = (string) $eventElement->price;
          $event['rating']                  = (string) $event->rating;
          $event['Vendor']                  = $this->vendor;

          // Timeout Link
          if( (string) $eventElement->timeout_url != "" )
              $event->setTimeoutLinkProperty( trim( (string) $eventElement->timeout_url ) );

          //Critics Choice
          $event->setCriticsChoiceProperty( ( (string) $eventElement->critics_choice == 'y' ) ? true : false );

          // Categories
          $event->addVendorCategory( $this->extractCategories( $eventElement ), $this->vendor->id );
            
            print_r( array_unique( $categories ) );

          // Add First Image Only
          //$medias = array();
          //foreach( $eventElement->medias->media as $media ) $medias[] = (string) $media;
          //if( !empty( $medias ) ) $this->addImageHelper( $event, $medias[0] );

          // Delete Occurences
          //$event['EventOccurrence']->delete();
          
          // Create Occurences
          //foreach( $eventElement->occurrences->occurrence as $xmlOccurrence )
          //{
          //   try
          //   {
          //        // Get Occurrence Id
          //        $vendor_occurence_id = (int) $xmlOccurrence[ 'id' ];

          //        $occurrence = new EventOccurrence();
          //        $occurrence[ 'vendor_event_occurrence_id' ]     = $vendor_occurence_id;
          //        $occurrence[ 'booking_url' ]                    = (string) $xmlOccurrence->booking_url;
          //        $occurrence[ 'start_date' ]                     = (string) $xmlOccurrence->start_date;
          //        $occurrence[ 'start_time' ]                     = (string) $xmlOccurrence->start_time;
          //        $occurrence[ 'end_date' ]                       = (string) $xmlOccurrence->end_date;
          //        $occurrence[ 'end_time' ]                       = (string) $xmlOccurrence->end_time;
          //        $occurrence[ 'utc_offset' ]                     = $poi['Vendor']->getUtcOffset();
          //        $occurrence[ 'Poi' ] = $poi;

          //        $event['Vendor'] = $poi['Vendor'];

          //        $event['EventOccurrence'][] = $occurrence;
          //   }
          //   catch( Exception $exception )
          //   {
          //       $this->notifyImporterOfFailure( $exception, $occurrence );
          //   }
          //}
          
          //$this->notifyImporter( $event );
      }
      catch( Exception $exception )
      {
          echo $exception->getMessage();
          $this->notifyImporterOfFailure( $exception, $event );
      }
    }
  }
  
}
