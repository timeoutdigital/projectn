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
    foreach( $this->xml->event as $eventElement )
    {
      try{
          // Get Venue Id
          $vendorEventId = (int) $eventElement['id'];
          if( !isset( $vendorEventId ) || !is_numeric( $vendorEventId ) ) break;

          $venueId = (string) $eventElement->occurrences->occurrence[0]->venue;
          $poi     = Doctrine::getTable( 'Poi' )->findByVendorPoiIdAndVendorLanguage( $venueId, 'ru' );
          if( !$poi )
          {
            $this->notifyImporterOfFailure( new Exception( 'Could not find a Poi with id: ' . $venueId . ' in Russia.' ) );
            continue;
          }

          $event = Doctrine::getTable( 'Event' )->findByVendorEventIdAndVendorLanguage( $vendorEventId, 'ru' );
          if( !$event )
            $event = new Event();

          $vendor = $poi['Vendor'];
          $event['Vendor'] = $poi['Vendor'];

          // Column Mapping
          $event['review_date']             = (string) $eventElement->review_date;
          $event['vendor_event_id']         = (string) $vendorEventId;
          $event['name']                    = (string) $eventElement->name;
          $event['short_description']       = $this->fixHtmlEntities( (string) $eventElement->short_description );
          $event['description']             = $this->fixHtmlEntities( (string) $eventElement->description );
          $event['booking_url']             = (string) $eventElement->booking_url;
          $event['url']                     = (string) $eventElement->url;
          $event['price']                   = (string) $eventElement->price;
          $event['rating']                  = (string) $eventElement->rating;

          // Categories
          $categories = array();
          foreach( $eventElement->categories->category as $category ) $categories[] = (string) $category;
          $event->addVendorCategory( $categories, $this->vendor->id );

          // Add First Image Only
          $medias = array();
          foreach( $eventElement->medias->media as $media ) $medias[] = (string) $media;
          if( !empty( $medias ) ) $this->addImageHelper( $event, $medias[0] );

          // Delete Occurences
          $event['EventOccurrence']->delete();
          
          // Create Occurences
          foreach( $eventElement->occurrences->occurrence as $xmlOccurrence )
          {
              // Get Occurrence Id
              foreach( $xmlOccurrence->attributes() as $k => $v )
                  if( $k == "id" ) $vendor_occurence_id = (int) $v;

              if( !isset( $vendor_occurence_id ) || !is_numeric( $vendor_occurence_id ) ) break;

              $occurrence = new EventOccurrence();
              $occurrence[ 'vendor_event_occurrence_id' ]     = $vendor_occurence_id;
              $occurrence[ 'booking_url' ]                    = (string) $xmlOccurrence->booking_url;
              $occurrence[ 'start_date' ]                     = (string) $xmlOccurrence->start_date;
              $occurrence[ 'start_time' ]                     = (string) $xmlOccurrence->start_time;
              $occurrence[ 'end_date' ]                       = (string) $xmlOccurrence->end_date;
              $occurrence[ 'end_time' ]                       = (string) $xmlOccurrence->end_time;
              $occurrence[ 'utc_offset' ]                     = $poi['Vendor']->getUtcOffset();
              $occurrence[ 'Poi' ] = $poi;

              $event['Vendor'] = $poi['Vendor'];

              $event['EventOccurrence'][] = $occurrence;
          }
          
          $this->notifyImporter( $event );
      }
      catch( Exception $exception )
      {
        echo 'foo';
          $this->notifyImporterOfFailure( $exception );
      }
    }
  }


  /**
   * Returns either an Event from Russia with matching $vendorEventId
   * or a new Event
   *
   * @param $vendorEventId
   */
  private function getRussianEvent( $vendorEventId )
  {
    return Doctrine::getTable( 'Event' )
      ->createQuery( 'e' )
      ->andWhere( 'e.vendor_event_id = ?', $vendorEventId )
      ->andWhereIn( 'e.vendor_id', $this->getRussianVendorIds() )
      ->fetchOne()
      ;
  }

  private function getRussianVendorIds()
  {
    if( is_null( $this->russianVendorsIds ) )
    {
      $russianVendors = Doctrine::getTable( 'Vendor' )->findByLanguage( 'ru' );

      $russianVendorsIds = array();
      foreach( $russianVendors as $vendor )
        $russianVendorsIds[] = $vendor[ 'id' ];

      $this->russianVendorsIds = $russianVendorsIds;
    }
    return $this->russianVendorsIds;
  }
}
