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
 * @see There is some prebuilt logic in the repository (commits of this class before the 23/11/2010) to parse
 *      the Lisbon timinfo string field and build occurrences out of it, it was removed out of this class because
 *      it became obsolete
 *
 */
class LisbonFeedListingsMapper extends LisbonFeedBaseMapper
{

    /**
     *
     * @var array $processedVendorEventIds
     */
    private $processedVendorEventIds = array();

    /**
     *
     * @var SimpleXmlElement $xml
     */
    public $xml = null;




    public function mapListings()
    {

        $lastEventRecordData = false;

        for ( $i=0; $i < count($this->xml->listings); $i++ )
        {
            $vendorEventId = $this->getEventId( $this->xml->listings[$i] );
            $vendorEventOccurrenceId = (int) $this->xml->listings[$i]['musicid'];
           
            try{
                
                if ( !in_array( $vendorEventId, $this->processedVendorEventIds ) )
                {
                    $event = Doctrine::getTable( 'Event' )->findOneByVendorIdAndVendorEventId( $this->vendor[ 'id' ], $vendorEventId );

                    if ( $event === false )
                        $event = new Event();
                    else
                        $event['EventOccurrence']->delete();

                    $this->processedVendorEventIds[] = $vendorEventId;
                }

                //event
                $this->mapAvailableData( $event, $this->xml->listings[$i], 'EventProperty' );
                $this->appendBandInfoToDescription( $event, $this->xml->listings[$i] );
                $event['description']                                 = $this->clean( preg_replace( "/{(\/?\w+)}/", "<$1>", $event['description'] ) );
                $event['price']                                       = $this->clean(str_replace( "?", "€", $event['price'] ) ); // Refs: #258b
                $event['vendor_id']                                   = $this->vendor['id'];
                $event['vendor_event_id']                             = $vendorEventId;
                $event['review_date']                                 = str_replace( 'T', ' ', (string) $this->xml->listings[$i]['ModifiedDate'] );
                $eventName = html_entity_decode( (string) $this->xml->listings[$i][ 'gigKey' ], ENT_QUOTES, 'UTF-8' );
                $event['name']                                        = $this->clean($eventName);

                //category
                //@todo should we move this out of the if and collect all the categories?
                $category = array( (string) $this->xml->listings[$i]['category'], (string) $this->xml->listings[$i]['SubCategory'] );
                $event->addVendorCategory( $category, $this->vendor['id'] );

                //remove event details which are different for occurrences
                $event = $this->removeInconsistentEventDetails( $lastEventRecordData, $event );
                $lastEventRecordData = clone $event;

                /*sort out occurrences*/
                $occurrence = $this->populateOccurrence( $event, $vendorEventId, $vendorEventOccurrenceId, $category, $this->xml->listings[$i] );
                if ( $occurrence !== false ) $event['EventOccurrence'][] = $occurrence;

                if ( !isset( $this->xml->listings[$i+1] ) || $this->getEventId( $this->xml->listings[$i+1] ) != $vendorEventId )
                {
                    $this->notifyImporter( $event );
                    $lastEventRecordData = false;
                }

            } catch ( Exception $exception )
            {
                $this->notifyImporterOfFailure( $exception, ( isset($event) && $event instanceof Event ) ? $event : null, 'Exception: LisbonFeedListingsMapper::mapListing (event)');
            }
        }    
    }

  /**
   *
   * @param string $attribute
   * @return string
   *
   * @todo function will be cleaned up in another ticket (bottom of it) and
   * cdata preservation
   */
  public function sortSimpleXmlByAttribute( $attribute )
  {

$stylesheet = <<<EOT
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="xml" encoding="UTF-8" indent="yes" cdata-section-elements="testtextnode" />

<!--<xsl:strip-space elements="*"/>-->


<xsl:template match="node() | @*">
  <xsl:copy>
    <xsl:apply-templates select="node() | @*">
      <xsl:sort data-type="number" select="@$attribute"/>
    </xsl:apply-templates>
  </xsl:copy>
</xsl:template>
</xsl:stylesheet>
EOT;

    $xsl = new DOMDocument;
    $xsl->loadXML($stylesheet);

    $processor = new XSLTProcessor;
    $processor->importStyleSheet($xsl); // attach the xsl rules

    $domXml = dom_import_simplexml( $this->xml );

    $xmlString = $processor->transformToXML($domXml);

    $this->xml = simplexml_load_string($xmlString);

    return $this->xml->asXml();
  }

  /**
   *
   * @param Event $lastEventRecordData
   * @param Event $event
   * @return Event
   */
  private function removeInconsistentEventDetails( $lastEventRecordData, $event )
  {
      if ( $lastEventRecordData instanceof Event )
      {
          foreach ( $lastEventRecordData->toArray( false ) as $field => $value )
          {
              if ( $event[ $field ] != $value ) $event[ $field ] = NULL;
          }

          foreach( $lastEventRecordData['EventProperty'] as $lastEventProperty )
          {
              foreach ( $event['EventProperty'] as $i => $eventProperty )
              {
                  $lookupIsSame = ( $eventProperty[ 'lookup' ] == $lastEventProperty[ 'lookup' ] );
                  $valueIsNotSame  = ( $eventProperty[ 'value' ]  != $lastEventProperty[ 'value' ]  );

                  if( $lookupIsSame && $valueIsNotSame )
                  {
                      if( $event['EventProperty'][$i]->exists() )
                          $event['EventProperty'][$i]->delete();
                      unset( $event['EventProperty'][$i] );
                  }
              }

          }
      }

      return $event;
  }

  /**
   *
   * @param Event $event
   * @param mixed $vendorEventId
   * @param mixed $vendorEventOccurrenceId
   * @param array $category
   * @param SimpleXMLElement $listingElement
   * @return EventOccurrence
   */
  private function populateOccurrence( $event, $vendorEventId, $vendorEventOccurrenceId, $category, $listingElement )
  {
    if ( $event === false )
    {
        $this->notifyImporterOfFailure( new Exception( 'Missing Lisbon Event, failed to create occurrence for event (vendor_event_id: ' . $vendorEventId . ' vendor_event_occurrence_id: ' . $vendorEventOccurrenceId . ')' ) );
        return false;
    }

    $placeid = (int) $listingElement['placeid'];

    if( $placeid == 0 )
    {
        $this->notifyImporterOfFailure( new Exception( 'Missing Lisbon Poi, failed to create occurrence for event (vendor_event_id: ' . $vendorEventId . ' vendor_event_occurrence_id: ' . $vendorEventOccurrenceId . ')' ) );
        return false;
    }

    $poi = Doctrine::getTable( 'Poi' )->findOneByVendorPoiIdAndVendorId( $placeid, $this->vendor['id'] );

    if( $poi === false )
    {
        $this->notifyImporterOfFailure( new Exception( 'Could not find Lisbon Poi with vendor_poi_id of '. $placeid . ', failed to create occurrence for event (vendor_event_id: ' . $vendorEventId . ' vendor_event_occurrence_id: ' . $vendorEventOccurrenceId . ')' ) );
        return false;
    }

    $poi->addVendorCategory( $category, $this->vendor['id'] );
    
    // Calling Poi->save() directly so that ImportLogger does not count poi record twice.
    // Poi needs to be saved as event categories are added to the corresponding poi.
    $poi->save();

    $listingDate = strtotime((string) $listingElement['ListingDate']);

    if( $listingDate == false )
    {
        $this->notifyImporterOfFailure( new Exception( 'Failed to convert date ( ' . (string) $node['ListingDate'] . ' ), failed to create occurrence for event (vendor_event_id: ' . $vendorEventId . ' vendor_event_occurrence_id: ' . $vendorEventOccurrenceId . ')' ) );
        return false;
    }

    $listingDateFormatted = date( 'Y-m-d', $listingDate );

    $occurrence = new EventOccurrence();
    $occurrence['vendor_event_occurrence_id']             = $vendorEventOccurrenceId;
    $occurrence['start_date']                             = $listingDateFormatted;
    $occurrence['utc_offset']                             = $this->vendor->getUtcOffset( $listingDateFormatted );
    $occurrence['Poi']                                    = $poi;
    $occurrence['Event']                                  = $event;

    return $occurrence;
  }

  /**
   *
   * @param SimpleXMLElement $listingsElement
   * @return integer
   */
  private function getEventId( $listingsElement )
  {
    $vendorEventId = (int) $listingsElement[ 'RecurringListingID' ];

    if ( $vendorEventId == 0 )
    {
        $vendorEventId = (int) $listingsElement['musicid'];
    }

    return $vendorEventId;
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
