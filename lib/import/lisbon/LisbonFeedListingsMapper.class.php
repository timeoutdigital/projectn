<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class LisbonFeedListingsMapper extends LisbonFeedBaseMapper
{

  public function mapListings()
  {
    foreach( $this->xml->listings as $listingElement )
    {
      if( (int) $listingElement['RecurringListingID'] == 0 )
      {
         continue;
      }
  
      // Do we have the event occurrence already?
      $musicId = (int) $listingElement['musicid'];
      $occurrence = Doctrine::getTable( 'EventOccurrence' )->createQuery( 'o' )
                                                           ->innerJoin( 'o.Event e' )
                                                           ->where( 'e.vendor_id = ?', $this->vendor[ 'id' ] )
                                                           ->andWhere( 'o.vendor_event_occurrence_id = ?', $musicId )
                                                           ->fetchOne();

      if ( $occurrence )
      {
        $event = Doctrine::getTable( 'Event' )->find( $occurrence[ 'event_id' ] );
      }
      else
      {
        // Do we have an event with this RecurringListingId ?
        $rlid = (int) $listingElement[ 'RecurringListingID' ];
        $name = html_entity_decode( (string) $listingElement[ 'gigKey' ], ENT_QUOTES, 'UTF-8' );
        $event = Doctrine::getTable( 'Event' )->createQuery( 'e' )
                                              ->where( 'e.vendor_event_id = ?', $rlid )
                                              ->andWhere( 'e.name = ?', $name )
                                              ->fetchOne();

        if ( !$event )
        {
          $event = new Event();
        }

        $occurrence = new EventOccurrence();
      }

      $this->mapAvailableData( $event, $listingElement, 'EventProperty' );
      $this->appendBandInfoToDescription( $event, $listingElement );
      $event['description']                                 = preg_replace( "/{(\/?\w+)}/", "<$1>", $event['description'] );
      $event['price']                                       = str_replace( "?", "€", $event['price'] ); // Refs: #258b
      $event['vendor_id']                                   = $this->vendor['id'];
      $event['vendor_event_id']                             = (int) $listingElement['RecurringListingID'];
      $event['review_date']                                 = str_replace( 'T', ' ', (string) $listingElement['ModifiedDate'] );
      $event['name']                                        = html_entity_decode( (string) $listingElement[ 'gigKey' ], ENT_QUOTES, 'UTF-8' );

      $occurrence['vendor_event_occurrence_id']             = (int) $listingElement['musicid'];
      $start = $this->extractStartTimes( $listingElement );
      $occurrence['start_date']                             = $start['date'];
      $occurrence['start_time']                             = $start['time'];
      $occurrence['utc_offset']                             = $this->vendor->getUtcOffset( $start[ 'datetime' ] );
      $occurrence['event_id']                               = $event['id'];
     
      $placeid = (int) $listingElement['placeid'];
      $poi = $this->dataMapperHelper->getPoiRecord( $placeid, $this->vendor['id'] );

      if( !$poi )
      {
        $this->notifyImporterOfFailure( new Exception( 'Could not find Lisbon Poi with vendor_poi_id of '. $placeid ), $occurrence );
        continue;
      }

      $category = array( (string) $listingElement['category'], (string) $listingElement['SubCategory'] );
      $event->addVendorCategory( $category, $this->vendor['id'] );
      $poi->addVendorCategory(   $category, $this->vendor['id'] );
      $this->notifyImporter( $poi );
      $occurrence['Poi']                                    = $poi;
      $event['EventOccurrence'][]                           = $occurrence;
      
      $this->notifyImporter( $event );
    }
  }

  private function getEventRecordFrom( $listingElement )
  {
    $id = (int) $listingElement['RecurringListingID'];
    return $this->dataMapperHelper->getEventRecord( $id );
  }

  private function extractStartTimes( $listingElement )
  {
      $startParts = explode('T', (string) $listingElement['ListingDate'] );

      $start[ 'date' ] = $startParts[ 0 ];                        //@todo get start times for Lisbon
      $start[ 'time' ] = null;                                    //$startParts[ 1 ]; we don't seem to have times for Lisbon at the moment
      $start[ 'datetime' ] = $startParts[ 0 ] . ' ' . '00:00:00'; //$startParts[ 1 ]; so we need to hard code a work around for now

/*    print_r( $start );
      print "\n";
      exit;
*/
      return $start;
  }

  /**
   * Append band info to description as per #259
   * @param <type> $event
   * @param <type> $listingElement
   */
  private function appendBandInfoToDescription( $event, $listingElement )
  {
      $band_info = explode( ",", (string) $listingElement['band'] );

      foreach( $band_info as $k => $info )
          $band_info[$k] = trim( $info, "  " ); // One of those is a weird portugese space

      $band_info = (string) implode( "<br />", $band_info );

      if( (string) trim( $event['description'] ) != "" )
         $event['description'] .= "<br /><br />";
      
      $event['description'] .= $band_info;
  }

  /**
   * Return an array of mappings from xml attributes to event fields
   *
   * @return array
   */
  protected function getMap()
  {
    return array(
      'musicid' => 'vendor_event_id',
      'gigKey' => 'name',
      'Notesline1' => 'short_description',
      'AnnotationForWeb' => 'description',
      'priceinfo' => 'price',
    );
  }

  protected function getIgnoreMap()
  {
    return array(
      'placeid',
      'place',
      'category',
      'listing_',
      'residency',
      'highlight',
      'sortvalue',
      'SavePreview',
      'CatSort',
      'Complete',
      'ResTerm',
      'PlacePrefix',
      'Discounted',
      'freeevent',
      'fatbob',
      'MagicSlim',
      'see',
      'archiveid',
      'CreatedBy',
      'CreatedDate',
      'CreatedTime',
      'Modifiedby',
      'ModifiedDate',
      'ModifiedTime',
      'CategoryId',
      'Section',
      'ListingDate',
      'todate',
      'DoNotPublishOnWeb',
      'SubCategory',
      'LateNight',
      'VenuePrefix',
      'AnnotationSuffix',
      'EventPrefix',
      'FullPlaceInfo',
      'ListingURL',
      'Discount',
      'FoodServed',
      'New',
      'Under5s',
      'RecurringListingID',
      'TelNoInfo',
      'ExportArchiveInformation',
      'ExcludeVenueInVenuesList',
      'novenue',
      'ListingTubeInfo',
      'ListingBusInfo',
      'ListingRailInfo',
      'ListingsTubeExport',
      'ProposedFromDate',
      'Ongoing',
      'OngoingText',
      'ProposedToDate',
      'ProductionID',
      'BookingAhead',
      'DoNotExportListing',
      'LastChance',
      'Extra',
      'Cancelled',
      'SectionID',
      'SubCategoryID',
      'EventInfoSuffix',
      'ExportEventNameandSuffix',
      'DupParentID',
      'UniqueName',
      'UniqueNameID',
      'BookingTil',
      'UnusedEventAlpha',
      'EventLookup',
      'OutputSortField',
      'listingstatus',
      'image',
      'band'
    );
  }

  public function getTargetDate( $currentTarget )
  {

    foreach( $this->xml->listings as $listingElement )
    {
      if( (int) $listingElement['RecurringListingID'] == 0 )
      {
         continue;
      }

      $startParts = explode('T', (string) $listingElement['ProposedToDate'] );

      $date = strtotime( $startParts[0] );

      if ( $date > $currentTarget )
        $currentTarget = $date;
    }

    return $currentTarget;
  }

}
?>
