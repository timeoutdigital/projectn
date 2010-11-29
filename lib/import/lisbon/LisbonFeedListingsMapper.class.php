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

    /*
     * -- About the lisbon event feed --
     *
     * @musicid seems to be a unique occurrence id.
     * @RecurringListingID seems to be an event id.
     *
     * @see There is some prebuilt logic in the repository (commits of this class before the 23/11/2010) to parse
     *      the Lisbon timinfo string field and build occurrences out of it
     *
     */

    /**
     *
     * @var array $processedVendorEventIds
     */
    private $processedVendorEventIds = array();



    public function mapListings()
    {
        foreach( $this->xml->listings as $listingElement )
        {
            $vendorEventId = (int) $listingElement[ 'RecurringListingID' ];
            $vendorEventOccurrenceId = (int) $listingElement['musicid'];

            if ( $vendorEventId == 0 )
            {
                $vendorEventId = $vendorEventOccurrenceId;
            }

            /* update event
             *
             * @todo remove mulbiple updates, check out Corpo – Estado, Medicina e Sociedade no tempo da I República
             */
            try{

                $event = Doctrine::getTable( 'Event' )->findOneByVendorIdAndVendorEventId( $this->vendor[ 'id' ], $vendorEventId );

                if ( !in_array( $vendorEventId, $this->processedVendorEventIds ) )
                {
                    if ( $event === false )
                    {
                        $event = new Event();
                    }

                    //event
                    $this->mapAvailableData( $event, $listingElement, 'EventProperty' );
                    $this->appendBandInfoToDescription( $event, $listingElement );
                    $event['description']                                 = $this->clean( preg_replace( "/{(\/?\w+)}/", "<$1>", $event['description'] ) );
                    $event['price']                                       = $this->clean(str_replace( "?", "€", $event['price'] ) ); // Refs: #258b
                    $event['vendor_id']                                   = $this->vendor['id'];
                    $event['vendor_event_id']                             = $vendorEventId;
                    $event['review_date']                                 = str_replace( 'T', ' ', (string) $listingElement['ModifiedDate'] );
                    $eventName = html_entity_decode( (string) $listingElement[ 'gigKey' ], ENT_QUOTES, 'UTF-8' );
                    $event['name']                                        = $this->clean($eventName);

                    //category
                    //@todo should we move this out of the if and collect all the categories?
                    $category = array( (string) $listingElement['category'], (string) $listingElement['SubCategory'] );
                    $event->addVendorCategory( $category, $this->vendor['id'] );

                    //occurrence
                    //get rid of our old occurrences
                    $event['EventOccurrence']->delete();
                    // Save
                    $this->notifyImporter( $event );

                    $this->processedVendorEventIds[] = $vendorEventId;
                }

                try{
                    if ( $event === false )
                    {
                        $this->notifyImporterOfFailure( new Exception( 'Missing Lisbon Event, failed to create occurrence for event (vendor_event_id: ' . $vendorEventId . ' vendor_event_occurrence_id: ' . $vendorEventOccurrenceId . ')' ) );
                        continue;
                    }

                    // @todo if we go with this variant it should be optimized, its a little inefficient
                    $placeid = (int) $listingElement['placeid'];

                    if( $placeid == 0 )
                    {
                        $this->notifyImporterOfFailure( new Exception( 'Missing Lisbon Poi, failed to create occurrence for event (vendor_event_id: ' . $vendorEventId . ' vendor_event_occurrence_id: ' . $vendorEventOccurrenceId . ')' ) );
                        continue;
                    }

                    $poi = Doctrine::getTable( 'Poi' )->findOneByVendorPoiIdAndVendorId( $placeid, $this->vendor['id'] );

                    if( $poi === false )
                    {
                        $this->notifyImporterOfFailure( new Exception( 'Could not find Lisbon Poi with vendor_poi_id of '. $placeid . ', failed to create occurrence for event (vendor_event_id: ' . $vendorEventId . ' vendor_event_occurrence_id: ' . $vendorEventOccurrenceId . ')' ) );
                        continue;
                    }

                    $poi->addVendorCategory( $category, $this->vendor['id'] );

                    // Calling Poi->save() directly so that ImportLogger does not count poi record twice.
                    // Poi needs to be saved as event categories are added to the corresponding poi.
                    $poi->save();

                    $listingDate = strtotime((string) $listingElement['ListingDate']);

                    if( $listingDate == false )
                    {
                        $this->notifyImporterOfFailure( new Exception( 'Failed to convert date ( ' . (string) $node['ListingDate'] . ' ), failed to create occurrence for event (vendor_event_id: ' . $vendorEventId . ' vendor_event_occurrence_id: ' . $vendorEventOccurrenceId . ')' ) );
                        continue;
                    }

                    $listingDateFormatted = date( 'Y-m-d', $listingDate );

                    $occurrence = new EventOccurrence();
                    $occurrence['vendor_event_occurrence_id']             = $vendorEventOccurrenceId;
                    $occurrence['start_date']                             = $listingDateFormatted;
                    $occurrence['utc_offset']                             = $this->vendor->getUtcOffset( $listingDateFormatted );
                    $occurrence['Poi']                                    = $poi;
                    $occurrence['Event']                                  = $event;

                    $event['EventOccurrence'][] = $occurrence;
                    $event->save();

                } catch( Exception $exception )
                {
                    $this->notifyImporterOfFailure( $exception, ( isset($occurrence) ) ? $occurrence : null, 'Exception: LisbonFeedListingsMapper::mapListing (occurrence)');
                }

            } catch ( Exception $exception )
            {
                $this->notifyImporterOfFailure( $exception, ( isset($event) ) ? $event : null, 'Exception: LisbonFeedListingsMapper::mapListing (event)');
            }                
        }    
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

}
?>
