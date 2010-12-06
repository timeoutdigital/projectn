<?php
/**
 * Sydney venues mapper
 *
 * @package projectn
 * @subpackage sydney.import.lib.unit
 *
 * @author Ralph Schwaninger <ralphschwaninger@timout.com>
 * @author Rajeevan Kumarathasan <rajeevankumarathasan.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class sydneyFtpEventsMapper extends sydneyFtpBaseMapper
{
    
  public function mapEvents()
  {

    $processedVendorEventsID = array(); // Store all evenets processed

    $todays_date = mktime( 0,0,0, date('m'), date('d'), date('Y') );
    foreach( $this->feed->event as $eventNode )
    {
      try
      {
          // Skip Outdated Events #633
          $start_date = $this->extractDate( (string) $eventNode->DateFrom, true );
    
          if( $todays_date > strtotime( $start_date ) )
          {
              continue;
          }

          // Get existing Event or Create New Event object
          $vendor_event_id = substr( md5( (string) $eventNode->Name ), 0, 9 );
          $event = Doctrine::getTable( 'Event' )->findOneByVendorIdAndVendorEventId( $this->vendor['id'], $vendor_event_id);
          if( $event === false )
          {
              $event = new Event();
          }

          $event['review_date']             = $this->extractDate( (string) $eventNode->DateUpdated );
          $event['vendor_event_id']         = $vendor_event_id;
          $event['name']                    = (string) $eventNode->Name;
          $event['description']             = (string) $eventNode->Description;
          $event['url']                     = (string) $eventNode->Website;
          $event['price']                   = stringTransform::formatPriceRange( (int) $eventNode->PriceFrom, (int) $eventNode->PriceTo, '$' );
          $event['rating']                  = (string) $eventNode->Rating;
          $event['Vendor']                  = $this->vendor;

          $event->addVendorCategory( $this->extractVendorCategories( $eventNode ), $this->vendor );

          if ( (string) $eventNode->CriticsPick == 'True' )
          {
              $event['CriticsChoiceProperty'] = true;
          } else {
              $event['CriticsChoiceProperty'] = false;
          }

          if ( (string) $eventNode->Recommended == 'True' )
          {
              $event['RecommendedProperty'] = true;
          } else {
              $event['RecommendedProperty'] = false;
          }

          if ( (string) $eventNode->Free == 'True' )
          {
              $event['FreeProperty'] = true;
          } else {
              $event['FreeProperty'] = false;
          }


          //#753 addImageHelper capture Exception and notify, this don't break the Import process
          $this->addImageHelper( $event, (string) $eventNode->ImagePath );

          // get the Poi for this Event occurrence
          $poi = Doctrine::getTable( 'Poi')->findOneByVendorIdAndVendorPoiId( $this->vendor['id'], $eventNode->VenueID );

          // Sydney provide occurrences as events, since we delete occurrences we have to make sure that we only delete once per import
          // this will make sure that we have all occurrences for that event saved..
          if( !in_array( $vendor_event_id, $processedVendorEventsID ) )
          {
            $event['EventOccurrence']->delete();
            $processedVendorEventsID[] = $vendor_event_id;

          }

          // This assumes only one occurence per Event.
          if ( $poi !== false )
          {
              $occurrence = new EventOccurrence();
              $occurrence['start_date']                   = $this->extractDate( (string) $eventNode->DateFrom, true );

              $timeInfo = $this->extractTime( (string) $eventNode->Time );
              if( isset( $timeInfo[ 'startTime' ] ) )
              {
                $occurrence['start_time'] = $timeInfo[ 'startTime' ];
              }

              if( isset( $timeInfo[ 'endTime' ] ) )
              {
                $occurrence['end_time'] = $timeInfo[ 'endTime' ];
              }

              $occurrence['end_date']                     = $this->extractDate( (string) $eventNode->DateTo, true );
              $occurrence['vendor_event_occurrence_id']   = Doctrine::getTable("EventOccurrence")
                                                                ->generateVendorEventOccurrenceId( $event['vendor_event_id'], $poi['id'], $occurrence[ 'start_date' ] );
              $occurrence['utc_offset']                   = $this->vendor->getUtcOffset();
              $occurrence['Poi']                          = $poi;
              $event['EventOccurrence'][] = $occurrence;
          }else
          {
              $this->notifyImporterOfFailure( new Exception( 'Could not find Sydney Poi with Vendor name of '. (string) $event['name']  ) );
              continue;
          }

          $this->notifyImporter( $event );
      }
      catch( Exception $exception )
      {
          $this->notifyImporterOfFailure( $exception, isset($event) ? $event : null );
      }
    }
  }

  private function  extractTime( $timeString )
 {
      $timeString = str_replace( '–', '-', $timeString );

      $timeRangePattern = '/^([0-9.]+)(a|p)m-([0-9.]+)(a|p)m$/'; //catches  "9.30am-10pm"
      preg_match( $timeRangePattern, $timeString, $matches );

      //print_r($matches);
      if( count( $matches ) == 5 )
      {
        if( strpos( $matches[1] , '.') ==false ) $matches[1] .= '.00'; //adding .00 to the start time if there isn't any
        if( strpos( $matches[3] , '.') ==false ) $matches[3] .= '.00'; //adding .00 to the end time if there isn't any
         //format the start and end times
        $startTime = date("H:i:s", strtotime( $matches[ 1 ] . ' '.$matches[ 2 ] . 'm' ) );
        $endTime = date("H:i:s", strtotime( $matches[ 3 ] . ' '.$matches[ 4 ] . 'm' ) ) ;
        return array( 'startTime' => $startTime  , 'endTime' => $endTime );
      }

      //range of time
      $timeRangePattern = '/^([0-9.]+)-([0-9.]+)(a|p)m$/'; //catches  "9.30-10pm"
      preg_match( $timeRangePattern, $timeString, $matches );

      if( count( $matches ) == 4 )
      {
        if( strpos( $matches[1] , '.') ==false ) $matches[1] .= '.00'; //adding .00 to the start time if there isn't any
        if( strpos( $matches[2] , '.') ==false ) $matches[2] .= '.00'; //adding .00 to the end time if there isn't any
         //format the start and end times
        $startTime = date("H:i:s", strtotime( $matches[ 1 ] . ' '.$matches[ 3 ] . 'm' ) );
        $endTime = date("H:i:s", strtotime( $matches[ 2 ] . ' '.$matches[ 3 ] . 'm' ) ) ;
        return array( 'startTime' => $startTime  , 'endTime' => $endTime );
      }


      //single time
      $singleTimePattern = '/^([0-9.]+)(a|p)m$/'; //catches  "9.30am"
      preg_match( $singleTimePattern, $timeString, $matches );
      if( count( $matches ) == 3 )
      {
        if( strpos( $matches[1] , '.') ==false ) $matches[1] .= '.00'; //adding .00 to the start time if there isn't any
        $startTime = date("H:i:s", strtotime( $matches[ 1 ] . ' '.$matches[ 2 ] . 'm' ) );
        return array( 'startTime' => $startTime );

      }

      return array();

 }

  private function extractDate( $dateString, $dateOnly = false )
  {
    if ( empty( $dateString ) )
      return;

    $date = DateTime::createFromFormat( 'd/m/Y h:i:s A', $dateString); //new DateTime( $dateString );
  
    if ($dateOnly)
    {
        return $date->format( 'Y-m-d' );
    }
    else
    {
        return $date->format( 'Y-m-d H:i:s' );
    }
  }

  private function extractVendorCategories( SimpleXMLElement $eventNode )
  {
    $vendorCats = array();

    $parentCategory = (string) $eventNode->categories->parent_category_name;

    if( !empty( $parentCategory ) && $parentCategory != 'N/A' )
      $vendorCats[] = $parentCategory;

    foreach( $eventNode->categories->childrens->children_category as $childCategory )
      $vendorCats[] = (string) $childCategory;

    return $vendorCats;
  }



}
