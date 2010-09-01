<?php
/**
 * Singapore event mapper

 *
 * @package projectn
 * @subpackage singapore.import.lib.unit
 *
 * @author Emre Basala <emrebasala@timout.com>
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class singaporeEventMapper extends DataMapper
{
    public function __construct( SimpleXMLElement $xml, geoEncode $geoEncoder = null )
    {
        $this->vendor     = Doctrine::getTable( 'Vendor' )->findOneByCityAndLanguage( 'singapore', 'en-US' );
        $this->geoEncoder = is_null( $geoEncoder ) ? new geoEncode() : $geoEncoder;
        $this->xml        = $xml;
    }

    public function mapEvents()
    {
        for( $i=0, $eventElement = $this->xml->event[ 0 ]; $i<$this->xml->event->count(); $i++, $eventElement = $this->xml->event[ $i ] )
        {
            $event = null;
            try
            {
                $vendorEventId = $this->clean( (string) $eventElement->id );
                $event = Doctrine::getTable( 'Event' )->findOneByVendorIdAndVendorEventId( $this->vendor[ 'id' ], $vendorEventId );

                if( !$event )
                {
                    $event = new Event();
                }

                $event[ 'vendor_id' ]               = $this->vendor[ 'id' ];
                $event[ 'vendor_event_id' ]         = $vendorEventId;
                $event[ 'name' ]                    = (string) $eventElement->name;
                //$event[ 'short_description' ]     = '';
                $event[ 'description' ]             = (string)  $eventElement->excerpt;
                //$event[ 'booking_url' ]           = '';
                $event[ 'url' ]                     = stringTransform::formatUrl( (string) $eventElement->website );
                $event[ 'price' ]                   = stringTransform::formatPriceRange( (string) $eventElement->min_price, (string) $eventElement->max_price, '$' );
                //$event[ 'rating' ] = '';
                

                $event->setCriticsChoiceProperty( ( trim( strtolower( (string)  $eventElement->critic_choice ) ) == 'y' ) ? true : false );
                $event->addProperty( 'opentime', (string)  $eventElement->opentime );

                if ( count( $eventElement->tags ) == 1 )
                {
                    foreach( $eventElement->tags->children() as $tag)
                    {
                        $event->addProperty( 'tag', (string) $tag );
                    }
                }

                //add vendor categories
                $categoriesArray = array();
                if ( (string) $eventElement->section != '' )
                {
                    $categoriesArray[] = (string) $eventElement->section;
                }

                if ( (string) $eventElement->category != '' )
                {
                    $categoriesArray[] = (string) $eventElement->category;
                }

                if (count( $categoriesArray ) > 0 )
                {
                    $event->addVendorCategory( $categoriesArray,  $this->vendor[ 'id' ]);
                }

                // -- Add Images --
                $event->addMediaByUrl( (string) $eventElement->highres );
                $event->addMediaByUrl( (string) $eventElement->large_image );
                $event->addMediaByUrl( (string) $eventElement->size1 );
                $event->addMediaByUrl( (string) $eventElement->thumbnail );

                //save to populate the id
                //ImportLogger::saveRecordComputeChangesAndLog( $event );

                //adding occurrences
                if ( count( $eventElement->venue->id ) == 1 && (string) $eventElement->date_start != '' )
                {
                    // get POI ID
                    $vendorPoiId = (string) $eventElement->venue->id;

                    //lookup if we have the poi and if not try to fetch it
                    $poi = Doctrine::getTable( 'Poi' )->findOneByVendorIdAndVendorPoiId( $this->vendor[ 'id' ], $vendorPoiId );
                    
                    if ( $poi === false )
                    {
                       // $poi = $this->tryToInsertMissingPoi( $vendorPoiId );
                         $this->notifyImporterOfFailure( new Exception( 'Could not find a Poi vendor poi id: ' . (string) $vendorPoiId . ' for Event ' . $vendorEventId . ' in ' . $this->vendor['city'] . "." ) );
                         continue;
                    }

                    // Create occurrences from Start Date and End Date
                    // 
                    // Get Dates Array
                    $dateArray = array();
                    //dates come in Thu, 09 Sep 2010 16:00:00 +0000 format
                    //so need to convert to Y-m-d ... & time
                    $dateArray['start']            =   date("Y-m-d" , strtotime( (string) $eventElement->date_start ) ) ;
                    $dateArray['start_time']       =   date("H:i:s" , strtotime( (string) $eventElement->date_start ) ) ;
                    if( trim( (string) $eventElement->date_end ) != '' )
                    {
                        $dateArray['end']          =   date("Y-m-d" , strtotime( (string) $eventElement->date_end ) ) ;
                        $dateArray['end_time']     =   date("H:i:s" , strtotime( (string) $eventElement->date_end ) ) ;
                    }

                    $this->createOccurrences($event, $poi, array( $dateArray ) );

                    // Create Occurrences from Alternative Dates
                    $altDates   = $eventElement->xpath( 'alternative_dates' );
                    $this->addAlternativeDates($event, $poi, $this->getAlternativeDates( $altDates ) );

                    // before deleting occurrences get the ids of the current occurrences and reuse them while creating occurrences.
                    // recyle the ids, save the planet!
//                    $occurrenceIdsOld = array();
//
//                    foreach ( $event['EventOccurrence'] as $occurrenceToDelete )
//                    {
//                        $occurrenceIdsOld[] = $occurrenceToDelete[ 'id' ];
//                    }
//
//                    $occurrenceIdsOld = array_reverse( $occurrenceIdsOld );
//
//                    $event['EventOccurrence']->delete();


                   
                }

                // Save
                $this->notifyImporter( $event );
                unset($event);
            }
            catch (Exception $exception)
            {
               var_dump( 'Exception singaporeEventMapper::mapEvents - ' . $exception->getMessage() );
               $this->notifyImporterOfFailure($exception, $event);
            }
        }

    }

    private function getAlternativeDates( $alertnativeDates )
    {
        if( !is_array($alertnativeDates) || count($alertnativeDates) <= 0 )
        {
            return array();
        }
        
        $dateArray = array();
        foreach ( $alertnativeDates as $date )
        {
            $exploded   = explode( PHP_EOL, (string)$date );

            if( count($exploded) > 1 )
            {
                $dateArray  = array_merge( $dateArray,  $exploded);

            }else if( stringTransform::mb_trim( (string)$date ) != '' ){

                $dateArray[] = stringTransform::mb_trim( (string)$date );
            }
            
        }

        return $dateArray;
    }
    
    private function addAlternativeDates( Doctrine_Record $event, Doctrine_Record $poi, $alertnativeDates )
    {
        if( !is_array( $alertnativeDates) || count( $alertnativeDates ) <= 0 )
        {
            return;
        }

        /* @todo : Singapore sending Alternative dates in text format likes "every other saturday",
         * "First week of Month" and event times line "Sat 9.30pm & Sun 8.30pm"
         * Now we only handle the Date-ranges and dates like 00/00/00 - 00/00/00 or 00/00/00
         */
        $parsedAlternativeDatesArray = array();
        $outputDateFormat = 'Y-m-d';

        // Handle Dateranges
        foreach( $alertnativeDates as $alternativeDate )
        { 
            //handle date ranges for example 03/17/2010 - 03/25/2010
            $dateRangeMatches = array();
            $dateRangeMatch = preg_match( '/^([0-9]{2}\/[0-9]{2}\/[0-9]{4})\s\-\s([0-9]{2}\/[0-9]{2}\/[0-9]{4})$/', (string) $alternativeDate, $dateRangeMatches );
            //handle single dates
            $singleDateMatches = array();
            $singleDateMatch = preg_match( '/^([0-9]{2}\/[0-9]{2}\/[0-9]{4})$/', (string) $alternativeDate, $singleDateMatches );

            if ( $dateRangeMatch )
            {
                $parsedAlternativeDatesArray[] = $this->createDateRange( $dateRangeMatches[ 1 ], $dateRangeMatches[2], $outputDateFormat );
            }
            elseif ( $singleDateMatch )
            {
                $parsedAlternativeDatesArray[][] = array( 'start'=> date( $outputDateFormat, strtotime( $singleDateMatches[ 1 ] ) ) );
            }
        }

        // Create Occurrences
        foreach ( $parsedAlternativeDatesArray as $wrapperArray )
        {
            $this->createOccurrences( $event, $poi, $wrapperArray );
        }
    }

    private function createOccurrences( Doctrine_Record $event, Doctrine_Record $poi, $datesArray )
    {
        // add occurrences based on Date array
        foreach( $datesArray as $date )
        {
            $vendorEventOccurrenceId = stringTransform::concatNonBlankStrings('_', array( $event['vendor_event_id'], $poi[ 'id' ], $date[ 'start' ] ) );

            $occurrence = new EventOccurrence();
            // $occurrence[ 'id' ] = array_pop( $occurrenceIdsOld );
            $occurrence[ 'vendor_event_occurrence_id' ] = $vendorEventOccurrenceId;

            try {
                //$eventOccurrence[ 'booking_url' ] ='';
                $occurrence[ 'utc_offset' ] = $this->vendor->getUtcOffset( $date[ 'start' ] );

                //the feeds do not always provide an accurate time, therefore, just Y-m-d underneath
                $occurrence[ 'start_date' ]     = $date[ 'start' ] ;
                $occurrence[ 'start_time' ]     = isset( $date[ 'start_time' ] ) ? $date[ 'start_time' ] : null ;
                $occurrence[ 'end_date' ]       = isset( $date[ 'end' ] ) ? $date[ 'end' ] : null;
                $occurrence[ 'end_time' ]       = isset( $date[ 'end_time' ] ) ? $date[ 'end_time' ] : null;

                $occurrence[ 'poi_id' ]         = $poi[ 'id' ];
                $event[ 'EventOccurrence' ][]   = $occurrence;
            }
            catch( Exception $exception )
            {
               var_dump( 'Exception singaporeEventMapper::createOccurrences - ' .$exception->getMessage() );
               $this->notifyImporterOfFailure($exception, $event);
            }
        }

    }

    /**
     * creates date ranges
     *
     * @todo move into a general helper class
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $returnFormat
     * @return array
     */
    private function createDateRange( $startDate, $endDate, $returnFormat = 'Y-m-d' )
    {
        $startDate  = DateTime::createFromFormat( 'm/d/Y', $startDate );
        $endDate  = DateTime::createFromFormat( 'm/d/Y', $endDate );

        if($endDate < $startDate )
        {
            return array();
        }
        
        $dateArray = array();
        // For Everyday, create Start date
        while ( $startDate <= $endDate )
        {
            $dateArray[] = array( 'start' => $startDate->format( $returnFormat ));
            $startDate->add(new DateInterval('P1D'));
        }

        return $dateArray;
    }
}
