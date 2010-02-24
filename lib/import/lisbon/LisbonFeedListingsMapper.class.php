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
      $event = $this->dataMapperHelper->getEventRecord( (int) $listingElement['RecurringListingID'] );
      $this->mapAvailableData( $event, $listingElement, 'EventProperty' );
      $event['vendor_id'] = $this->vendor['id'];
      $event['vendor_event_id'] = (int) $listingElement['RecurringListingID'];
      $event->addVendorCategory( array( (string) $listingElement['category'], (string) $listingElement['SubCategory'] ), $this->vendor['id'] );
      $event['review_date'] = str_replace( 'T', ' ', (string) $listingElement['ModifiedDate'] );

      $occurrence = $this->dataMapperHelper->getEventOccurrenceRecord( $event, (int) $listingElement['musicid'] );
      $occurrence['vendor_event_occurrence_id'] = (int) $listingElement['musicid'];
      $occurrence['start'] = str_replace('T', ' ', (string) $listingElement['ListingDate'] );
      $occurrence['utc_offset'] = 0;
      $occurrence['event_id'] = $event['id'];
      
      $placeid = (int) $listingElement['placeid'];
      $poi = Doctrine::getTable('Poi')->findOneByVendorPoiId( $placeid );

      if( !$poi )
      {
        $this->notifyImporterOfFailure( new Exception( 'Could not find Lisbon Poi with vendor_poi_id of '. $placeid ), $occurrence );
        continue;
      }

      $occurrence['Poi'] = $poi;
      
      $event['EventOccurrence'][] = $occurrence;

       //we will try to find the event with name
      if( is_null (  $event[ 'id' ] ) && Doctrine::getTable('Event')->findOneByName( (string) $listingElement['gigKey'] ) )
      {
          $this->notifyImporterOfFailure( new Exception( 'An event of this name already exists; suspicious...' ) , $event );
          continue;
      }
      $this->notifyImporter( $event );
    }
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
    );
  }
}
?>
