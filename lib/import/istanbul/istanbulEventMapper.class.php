<?php
/**
 * Istanbul event mapper
 *
 * @package projectn
 * @subpackage istanbul.import.lib.unit
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class istanbulEventMapper extends istanbulBaseMapper
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
          {
            $event = new Event();
          }

          // Column Mapping
          $event[ 'vendor_event_id' ]         = $vendorEventId;
          $event[ 'name' ]                    = $this->clean( (string) $eventElement->name );
          $event[ 'short_description' ]       = $this->fixHtmlEntities( $this->clean( (string) $eventElement->short_description  ) );
          $event[ 'description' ]             = $this->fixHtmlEntities( $this->clean( (string) $eventElement->description  ) );
          $event[ 'booking_url' ]             = $this->clean( (string) $eventElement->booking_url );
          $event[ 'url' ]                     = $this->clean( (string) $eventElement->url );
          $event[ 'price' ]                   = $this->clean( (string) $eventElement->price );
          $event[ 'rating' ]                  = (int) $eventElement->rating;
          $event[ 'Vendor' ]                  = clone $this->vendor;

          // Timeout Link
          if( $this->clean( (string) $eventElement->timeout_url ) != "" )
          {
              $event->setTimeoutLinkProperty( $this->clean( (string) $eventElement->timeout_url ) );
          }

          // Categories
          $cats = $this->extractCategories( $eventElement );
          foreach( $cats as $cat )
          {
            $event->addVendorCategory( $cat );
          }

          foreach( $eventElement->medias->media as $media )
          {
              $this->addImageHelper( $event, (string) $media ); //#753 addImageHelper capture Exception and notify, this don't break the Import process
          }

          // Delete Occurences, but before deleting save the ids in an array to reuse
          $oldIds = array();

          foreach ($event['EventOccurrence'] as $oldOccurrence)
          {
            $oldIds[] = $oldOccurrence[ 'id' ];
          }

          $event['EventOccurrence']->delete();

          // Create Occurences
          foreach ( $eventElement->occurrences  as $xmlOccurrences )
          {
            $xmlOccurrence = $xmlOccurrences->occurrence;
            try
             {
                  // Some Events Don't Have Occcurrences
                  if( !$xmlOccurrence )
                  {
                    $this->notifyImporterOfFailure( new Exception( 'No Occurences in Feed for Vendor Event ID: ' . $vendorEventId . ' in Istanbul.' ) );
                    break;
                  }

                  // Find POI
                  $poi = Doctrine::getTable( 'Poi' )->findOneByVendorPoiIdAndVendorId( $this->clean( (string) $xmlOccurrence->venue ),  $this->vendor[ 'id' ] );
                  if( !$poi )
                  {
                    $this->notifyImporterOfFailure( new Exception( 'Could not find a Poi with id: ' . $this->clean( (string) $xmlOccurrence->venue ) . ' for Vendor Event ID ' . $vendorEventId . ' in Istanbul.' ) );
                    continue;
                  }

                  $occurrence = new EventOccurrence();
                 //reusing the old id
                  $occurrence['id']                               = array_pop( $oldIds );
                  $occurrence[ 'vendor_event_occurrence_id' ]     = $vendorEventId . '_' .  $xmlOccurrence[ 'id' ];
                  $occurrence[ 'booking_url' ]                    = $this->clean( (string) $xmlOccurrence->booking_url );

                  // Get Start Date and end date

                  $startDate = $this->clean( (string) $xmlOccurrence->start_date );
                  if( !empty( $startDate ) )
                  {
                    //the date in the feed is in 03.09.2010 format , convert it to "Y-m-d"
                    $startDate = date( "Y-m-d", strtotime(  $startDate ) );
                  }

                  $endDate = $this->clean( (string) $xmlOccurrence->end_date );
                  if( !empty( $endDate ) )
                  {
                    //the date in the feed is in 03.09.2010 format , convert it to "Y-m-d"
                    $endDate = date( "Y-m-d", strtotime(  $endDate ) );
                  }

                  $occurrence[ 'start_date' ]                     = $startDate;
                  $occurrence[ 'end_date' ]                       = $endDate;
                  $occurrence[ 'utc_offset' ]                     = $this->vendor->getUtcOffset();
                  $occurrence[ 'Poi' ] = $poi;

                  $event['EventOccurrence'][] = $occurrence;

             }
             catch( Exception $exception )
             {
                 $this->notifyImporterOfFailure( $exception, $occurrence );
             }
          }

        if( count( $event['EventOccurrence'] ) == 0 )
        {
            $this->notifyImporterOfFailure( new Exception( 'Could not find any reliable occurrences for Vendor Event ID: ' . $vendorEventId . ' in  Istanbul' ) );
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
