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

    $recurringListingIdArray = array();
    foreach( $this->xml->listings as $listingElement )
    {
      $recurringListingId = (int) $listingElement[ 'RecurringListingID' ];
      $musicId = (int) $listingElement['musicid'];

      if( $recurringListingId != 0)
      {
        if( !array_key_exists( $recurringListingId, $recurringListingIdArray ) || $recurringListingIdArray[ $recurringListingId ] < $musicId )
        {
          $recurringListingIdArray[ $recurringListingId ] = $musicId;
        }
      }
    }

    foreach( $this->xml->listings as $listingElement )
    {
      $recurringListingId = (int) $listingElement[ 'RecurringListingID' ];
      $musicId = (int) $listingElement['musicid'];

      if( !array_key_exists( $recurringListingId, $recurringListingIdArray) )
      {
         continue;
      }

//      $eventName = html_entity_decode( (string) $listingElement[ 'gigKey' ], ENT_QUOTES, 'UTF-8' );
//
//      $occurrence = Doctrine::getTable( 'EventOccurrence' )->createQuery( 'o' )
//                                                           ->innerJoin( 'o.Event e' )
//                                                           ->where( 'e.vendor_id = ?', $this->vendor[ 'id' ] )
//                                                           ->andWhere( 'o.vendor_event_occurrence_id = ?', $musicId )
//                                                           ->fetchOne();
//
//      if (  $occurrence !== false )
//      {
//        $event = $occurrence['Event'];
//      }
//      else
//      {
//        $event = Doctrine::getTable( 'Event' )->findOneByVendorEventIdAndName( $recurringListingId, $eventName );
//
//        if ( $event === false )
//        {
//          $event = new Event();
//        }
//
//        $occurrence = new EventOccurrence();
//        $occurrence['vendor_event_occurrence_id']           = $musicId;
//      }

      $event = Doctrine::getTable( 'Event' )->findOneByVendorIdAndVendorEventId( $this->vendor[ 'id' ], $recurringListingId );

      if ( $event === false )
      {
        $event = new Event();
      }

      //category
      $category = array( (string) $listingElement['category'], (string) $listingElement['SubCategory'] );

      //event
      $this->mapAvailableData( $event, $listingElement, 'EventProperty' );
      $this->appendBandInfoToDescription( $event, $listingElement );
      $event['description']                                 = $this->clean( preg_replace( "/{(\/?\w+)}/", "<$1>", $event['description'] ) );
      $event['price']                                       = $this->clean(str_replace( "?", "€", $event['price'] ) ); // Refs: #258b
      $event['vendor_id']                                   = $this->vendor['id'];
      $event['vendor_event_id']                             = $recurringListingId;
      $event['review_date']                                 = str_replace( 'T', ' ', (string) $listingElement['ModifiedDate'] );
      $eventName = html_entity_decode( (string) $listingElement[ 'gigKey' ], ENT_QUOTES, 'UTF-8' );
      $event['name']                                        = $this->clean($eventName);
      $event->addVendorCategory( $category, $this->vendor['id'] );

      //occurrence

      //get rid of our old occurrences
      $event['EventOccurrence']->delete();

      //$start = $this->extractStartTimes( $listingElement );

      $possibleDays = $this->extractDays( $listingElement );

      $occurrenceDates = $this->getOccurrenceDates( $listingElement, $possibleDays );

      // @todo if we go with this variant it should be optimized, its a little inefficient
      $placeid = (int) $listingElement['placeid'];

      foreach( $occurrenceDates as $occurrenceDate )
      {
        if( $placeid == 0 )
        {
          $this->notifyImporterOfFailure( new Exception( 'Missing Lisbon Poi, failed to create occurrence for event (vendor_event_id: ' . $recurringListingId . ')' ) );
          continue;
        }

        $occurrence = new EventOccurrence();
        $occurrence['vendor_event_occurrence_id']             = Doctrine::getTable( 'EventOccurrence' )->generateVendorEventOccurrenceId( $recurringListingId, $placeid, $occurrenceDate );
        $occurrence['start_date']                             = $occurrenceDate;
        $occurrence['utc_offset']                             = $this->vendor->getUtcOffset( $occurrenceDate );

        $poi = $this->dataMapperHelper->getPoiRecord( $placeid, $this->vendor['id'] );

        if( $poi === false )
        {
          $this->notifyImporterOfFailure( new Exception( 'Could not find Lisbon Poi with vendor_poi_id of '. $placeid ), $occurrence );
          continue;
        }

        $poi->addVendorCategory(   $category, $this->vendor['id'] );
        $this->notifyImporter( $poi );

        $occurrence['Poi']                                    = $poi;

        $event['EventOccurrence'][]                           = $occurrence;


      }

      $this->notifyImporter( $event );
    }

  }

//  private function getPoi( $placeid )
//  {
//      $poi = $this->dataMapperHelper->getPoiRecord( $placeid, $this->vendor['id'] );
//
//      if( $poi === false )
//      {
//        $this->notifyImporterOfFailure( new Exception( 'Could not find Lisbon Poi with vendor_poi_id of '. $placeid ), $occurrence );
//        continue;
//      }
//
//      $poi->addVendorCategory(   $category, $this->vendor['id'] );
//      $this->notifyImporter( $poi );
//
//      return $poi;
//  }

  private function getEventRecordFrom( $listingElement )
  {
    $id = (int) $listingElement['RecurringListingID'];
    return $this->dataMapperHelper->getEventRecord( $id );
  }

  private function extractStartTimes( $listingElement )
  {

//      echo (string) $listingElement['ProposedFromDate'] . '  /  ';
//      echo (string) $listingElement['ProposedToDate'] . '  /  ';
//      echo (string) $listingElement['timeinfo'] . '  /  ';
//      echo stringTransform::formatAsTime( (string) $listingElement['timeinfo'] ) . '  /  ';
//      echo implode( '|', stringTransform::extractTimesFromText( (string) $listingElement['timeinfo'] ) ) . '  /  ';
//      echo implode( '|', stringTransform::extractTimeRangesFromText( (string) $listingElement['timeinfo'] ) ) . '  /  ';
//      echo stringTransform::extractStartTime( (string) $listingElement['timeinfo'] );
//      echo PHP_EOL;







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



  private function extractDays( $listingElement )
  {

      $weekDaysMap = array( 'Sunday' => array( 'Domingo', 'dom' ,'domingos'),
                            'Monday' => array( 'segunda-feira', 'segunda', 'seg' ,'segundas-feiras' ,'segunda-feiras' ),
                            'Tuesday' => array( 'terça-feira', 'terça', 'ter' ,'Terças' ,'terças-feiras' ,'terça-feiras' ),
                            'Wednesday' => array( 'quarta-feira', 'quarta', 'qua' ,'quartas' ,'quartas-feiras' , 'quarta-feiras' ),
                            'Thursday' => array( 'quinta-feira', 'quinta', 'qui' ,'quintas', 'quintas-feiras' ),
                            'Friday' => array( 'sexta-feira', 'sexta', 'sex' ,'sextas-feiras' ),
                            'Saturday' => array( 'sábado', 'sab', 'sáb' ,'sábados' ),
                     );

      $weekDays = array();
      foreach ( $weekDaysMap as $weekday )
      {
        $weekDays = array_merge( $weekDays, $weekday);
      }

      $weekDaysOrString = implode( '|', $weekDays );

      //ranges
      $dayRangePattern = '/(' . $weekDaysOrString . ')\-(' . $weekDaysOrString . ')/i';
      $dayRanges = preg_match( $dayRangePattern, $listingElement['timeinfo'], $matches );
      if ( 0 < count($matches) ) array_shift($matches);
      $daysWhenItHappens = $matches;

      //pairs
      $dayPairsPattern = '/(' . $weekDaysOrString . ')\se\s(' . $weekDaysOrString . ')/i';
      $dayRanges = preg_match( $dayPairsPattern, $listingElement['timeinfo'], $matches );
      if ( 0 < count($matches) ) array_shift($matches);
      $daysWhenItHappens = array_merge($daysWhenItHappens, $matches );

      //single days
      $singleDaysPattern = '/(' . $weekDaysOrString . ')[\s,:]+/i';
      $dayRanges = preg_match_all( $singleDaysPattern, $listingElement['timeinfo'], $matches );
      if ( 0 < count($matches) ) array_shift($matches);
      $daysWhenItHappens = array_merge($daysWhenItHappens, $matches[0] );

      //every day
      $everyDayPattern = '/(todos os dias)/i';

      if ( preg_match( $everyDayPattern, $listingElement['timeinfo'] ) && count( $daysWhenItHappens ) == 0 && !preg_match( '/(' . $weekDaysOrString . ')/i', $listingElement['timeinfo'] ) )
      {
        $daysWhenItHappens = $weekDays;
      }
      else if ( preg_match( $everyDayPattern, $listingElement['timeinfo'] ) && count( $daysWhenItHappens ) != 0 )
      {
        $daysWhenItHappens = array();
      }

      //weekends
      $weekendPattern = '/(Fins-de-semana)/i';

      if ( preg_match( $weekendPattern, $listingElement['timeinfo'] ) )
      {
        $daysWhenItHappens [] = 'sábado';
        $daysWhenItHappens [] = 'Domingo';
      }

      //working days
      $workingdaysPattern = '/(Dias úteis)/i';

      if ( preg_match( $workingdaysPattern, $listingElement['timeinfo'] ) )
      {
        $daysWhenItHappens [] = 'segunda-feira';
        $daysWhenItHappens [] = 'terça-feira';
        $daysWhenItHappens [] = 'quarta-feira';
        $daysWhenItHappens [] = 'quinta-feira';
        $daysWhenItHappens [] = 'sexta-feira';
      }


      $daysWhenItHappensTranslated = array();
      foreach ( $weekDaysMap as $englishDay => $dayInForeignLangArray )
      {
          if ( 0 < count( array_uintersect($dayInForeignLangArray, $daysWhenItHappens, "strcasecmp") ) ) $daysWhenItHappensTranslated[] = $englishDay;
      }

      return array_unique( $daysWhenItHappensTranslated );
  }


  private function getOccurrenceDates( $listingElement, $possibleDays )
  {
      $zone = new DateTimeZone( $this->vendor[ 'time_zone' ] );
      $zoneGB = new DateTimeZone( 'Europe/London' );

      //get rid of the times
      $proposedFromDate = date( 'Y-m-d', strtotime( $listingElement['ProposedFromDate'] ) );
      $proposedToDate = date( 'Y-m-d', strtotime( $listingElement['ProposedToDate'] ) );
      $todaysDate = date( 'Y-m-d' );

      $eventFromDate = new DateTime( $proposedFromDate, $zone );
      $eventToDate = new DateTime( $listingElement['ProposedToDate'], $zone );
      $currentDay = new DateTime( $todaysDate, $zoneGB );

      $occurrenceDates = array();
      for ( $i=0; $i < 7; $i++ )
      {
          if ( $eventFromDate <= $currentDay && $eventToDate >= $currentDay && in_array( $currentDay->format( 'l' ), $possibleDays )  )
          {
              $occurrenceDates[] = $currentDay->format( 'Y-m-d' );
          }
          $currentDay->add( new DateInterval('P1D') );
      }

      return $occurrenceDates;
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
