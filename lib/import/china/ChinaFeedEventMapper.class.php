<?php
/**
 * China City Feed Events Import Mapper
 *
 * @package projectn
 * @subpackage
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class ChinaFeedEventMapper extends ChinaFeedBaseMapper
{
    private $eventVendor;

    public function mapEvents()
    {
        foreach( $this->xmlNodes as $eventNode )
        {
            // Set Vendor Unknown For Import Logger
            ImportLogger::getInstance()->setVendorUnknown();

            try
            {
            
                // get existing Event or create new one
                $event = Doctrine::getTable( 'Event' )->findByVendorEventIdAndVendorLanguage((string)$eventNode['id'], 'zh-Hans' );
                if( $event === false )
                {
                    $event = new Event();
                }


                // Map Event Data
                $event['vendor_event_id']       = (string)$eventNode['id'];

                $event['name']                  = $this->clean( (string) $eventNode->name );
                $event['review_date']           = $this->clean( (string) $eventNode->review_date );
                $event['description']           = $this->clean( (string) $eventNode->description );
                $event['short_description']     = $this->clean( (string) $eventNode->short_description );
                $event['price']                 = $this->clean( (string) $eventNode->price );

                // Extract Event occurrences
                if( isset( $eventNode->occurrences->occurrence ) )
                {
                    // Delete existing Occurrences
                    $event['EventOccurrence']->delete();
                    
                    foreach( $eventNode->occurrences->occurrence as $xmlOccurrence )
                    {
                        // Check for POI
                        $occurrenceVenue = Doctrine::getTable( 'Poi' )->findByVendorPoiIdAndVendorLanguage( (string)$xmlOccurrence->venue_id, 'zh-Hans' );

                        if( $occurrenceVenue === false )
                        {
                            $this->notifyImporterOfFailure( new Exception ( "Poi not found with id: {(string)$xmlOccurrence->venue_id}, China Mapper; Event {$event['vendor_event_id']} occurrence." ) );
                            continue;
                        }

                        // Set Vendor For Import Logger
                        ImportLogger::getInstance()->setVendor( $occurrenceVenue['Vendor'] );


                        $eventOccurrence = new EventOccurrence;
                        $eventOccurrence['start_date']                  = (string)$xmlOccurrence->start_date;
                        $eventOccurrence['start_time']                  = $this->extractTimeOrNull( (string)$xmlOccurrence->start_time );
                        $eventOccurrence['end_date']                    = (string)$xmlOccurrence->end_date;
                        $eventOccurrence['end_time']                    = $this->extractTimeOrNull( (string)$xmlOccurrence->end_time );
                        $eventOccurrence['poi_id']                      = $occurrenceVenue['id'];
                        $eventOccurrence['utc_offset']                  = $occurrenceVenue['Vendor']->getUtcOffset();
                        $eventOccurrence['vendor_event_occurrence_id']  = stringTransform::concatNonBlankStrings('-', array(
                                                                                                                        $event['vendor_event_id'],
                                                                                                                        $eventOccurrence['start_date'],
                                                                                                                        $eventOccurrence['poi_id'],
                                                                                                                    ));

                        $event['EventOccurrence'][] = $eventOccurrence;
                        $event['Vendor']            = $occurrenceVenue['Vendor'];
                    }

                }else{
                    
                    $this->notifyImporterOfFailure( new Exception( "Event doesn't have any occurrences" ));
                    continue;
                }

                // Extract Category, require vendor
                if( isset( $eventNode->categories ) )
                {
                    $this->extractCategory( $event, $eventNode);
                }

                $this->notifyImporter( $event );

            } catch ( Exception $e )
            {
                echo 'Exception: ' . $e->getMessage() . PHP_EOL; // Debug only
                $this->notifyImporterOfFailure( $e , isset( $event ) ? $event : null );
            }
        }
    }
}