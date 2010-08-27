<?php
/**
 * Singapore event mapper
 *
 * @package projectn
 * @subpackage singapore.import.lib.unit
 *
 * @author Emre Basala <emrebasala@timout.com>
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

                $event[ 'vendor_event_id' ] = $vendorEventId;
                $event[ 'name' ] = (string) $eventElement->name;
                //$event[ 'short_description' ] = '';
                $event[ 'description' ] = (string)  $eventElement->excerpt;
                //$event[ 'booking_url' ] = '';
                $event[ 'url' ] = (string) $eventElement->website;
                $event[ 'price' ] = stringTransform::formatPriceRange( (string) $eventElement->min_price, (string) $eventElement->max_price, '$' );
                //$event[ 'rating' ] = '';
                $event[ 'vendor_id' ] = $this->vendor[ 'id' ];

                $event->addProperty( 'Critics_choice', (string)  $eventElement->critic_choice );
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
                    //$this->_createEventOccurrence( (string) $eventElement->venue->id, $event[ 'id' ], (string) $eventElement->date_start, (string) $eventElement->date_end );

                    $vendorPoiId = (string) $eventElement->venue->id;

                    $datesArray = array();
                    //dates come in Thu, 09 Sep 2010 16:00:00 +0000 format
                    //so need to convert to Y-m-d ...
                    $dateStart  =   date("Y-m-d" , strtotime( (string) $eventElement->date_start ) ) ;
                    $dateEnd    =  date("Y-m-d" , strtotime( (string) $eventElement->date_end )) ;

                    if ( $dateStart != '' )
                    {
                        if ( $dateEnd != '' )
                        {
                            $datesArray[] = array( 'start' => $dateStart, 'end' => $dateEnd );
                        }
                        else
                        {
                            $datesArray[] = array( 'start' => $dateStart );
                        }
                    }

                    //lookup if we have the poi and if not try to fetch it
                    $poi = Doctrine::getTable( 'Poi' )->findOneByVendorIdAndVendorPoiId( $this->vendor[ 'id' ], $vendorPoiId );

                    if ( $poi === false )
                    {
                       // $poi = $this->tryToInsertMissingPoi( $vendorPoiId );
                         $this->notifyImporterOfFailure( new Exception( 'Could not find a Poi vendor poi id: ' . (string) $vendorPoiId . ' for Event ' . $vendorEventId . ' in ' . $this->vendor['city'] . "." ) );
                         continue;
                    }

                    // before deleting occurrences get the ids of the current occurrences and reuse them while creating occurrences.
                    // recyle the ids, save the planet!
                    $occurrenceIdsOld = array();

                    foreach ( $event['EventOccurrence'] as $occurrenceToDelete )
                    {
                        $occurrenceIdsOld[] = $occurrenceToDelete[ 'id' ];
                    }

                    $occurrenceIdsOld = array_reverse( $occurrenceIdsOld );

                    $event['EventOccurrence']->delete();


                    foreach( $datesArray as $date )
                    {
                        $vendorEventOccurrenceId = $vendorEventId .'_' . $poi[ 'id' ] . '_' . $date[ 'start' ] ;

                        $occurrence = new EventOccurrence();
                        $occurrence[ 'id' ] = array_pop( $occurrenceIdsOld );
                        $occurrence[ 'vendor_event_occurrence_id' ] = $vendorEventOccurrenceId;

                        try {
                            //$eventOccurrence[ 'booking_url' ] ='';
                            $occurrence[ 'utc_offset' ] = $this->vendor->getUtcOffset( $date[ 'start' ] );

                            //the feeds do not provide an accurate time, therefore, just Y-m-d underneath
                            $occurrence[ 'start_date' ] =  $date[ 'start' ] ;
                            if ( isset( $date['end_date'] ) )
                            {
                                $occurrence[ 'end_date' ] = $date[ 'end' ];
                            }

                            $occurrence[ 'poi_id' ] = $poi[ 'id' ];
                            $event[ 'EventOccurrence' ][] = $occurrence;
                        }
                        catch( Exception $exception )
                        {
                           var_dump( $exception->getMessage() );
                           $this->notifyImporterOfFailure($exception, $event);
                        }
                    }
                }
                //end of adding occurrences

                // deal with the alternative dates
                //$alternativeDatesArray = $eventElement->xpath( 'alternative_dates' );
                //$this->_addAlternativeDates( (string) $eventElement->venue->id, $event[ 'id' ], $alternativeDatesArray );

                //free at last
               // $event->free();

                //currently not used fields
                //issue
                //hot seat
                //views
                //data_add
                //data_change
                //redirect
                //standfirst
                //gallery
                //top_start
                //top_end
                //top_premium
                //top_platinum
                //has_top
                //top_logo
                //link
                //feature
             $event->save();
            }
            catch (Exception $exception)
            {
               var_dump( $exception->getMessage() );
               $this->notifyImporterOfFailure($exception, $event);
            }
        }

    }

}
