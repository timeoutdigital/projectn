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

class ChinaFeedEventsMapper extends ChinaFeedBaseMapper
{
    public function mapEvents()
    {
        foreach( $this->xmlNodes as $eventNode )
        {
            // get existing Event or create new one
            $event = Doctrine::getTable( 'Event' )->findOneByVendorIdAndVendorEventId( $this->vendor['id'], (string)$eventNode['id'] );
            if( $event === false )
            {
                $event = new Event();
            }

            // Map Data
            try
            {
                $event['vendor_event_id']       = (string)$eventNode['id'];
                $event['Vendor']                = $this->vendor;

                $event['name']                  = $this->clean( (string) $eventNode->name );
                $event['review_date']           = $this->clean( (string) $eventNode->review_date );
                $event['description']           = $this->clean( (string) $eventNode->description );
                $event['short_description']     = $this->clean( (string) $eventNode->short_description );
                $event['price']                 = $this->clean( (string) $eventNode->price );

                // Extract Category
                if( isset( $eventNode->categories ) )
                {
                    $categoryArray;
                    foreach( $eventNode->categories->category as $parentCategory )
                    {
                        $categoryArray = array(); // Reset

                        // Adding parent category to Array
                        $categoryArray[] = $this->clean( (string) $parentCategory->name );

                        // Look for Any child category and Add them to The list
                        if( isset( $parentCategory->children ) )
                        foreach ( $parentCategory->children as $childCategory )
                        {
                            if( $this->clean( (string)$childCategory->name ) != '' )
                            {
                                $categoryArray[] = $this->clean( (string)$childCategory->name );
                            }
                        }

                        // addVendorCategory will Implode the array into | seperated Value
                        // hence we add Each Top level category with its child
                        $event->addVendorCategory( $categoryArray, $this->vendor['id'] );

                    }
                }

                // Extract Event occurrences
                if( isset( $eventNode->occurrences->occurrence ) )
                {
                    $event['EventOccurrence']->delete();
                    
                    foreach( $eventNode->occurrences->occurrence as $xmlOccurrence )
                    {
                        // Check for POI
                        $occurrenceVenue = Doctrine::getTable( 'Poi' )->findOneByVendorIdAndVendorPoiId( $this->vendor['id'], (string)$xmlOccurrence->venue_id );

                        if( $occurrenceVenue === false )
                        {
                            $this->notifyImporterOfFailure( new Exception ( "Poi not found, China Mapper; Event {$event['vendor_event_id']} occurrence." ) );
                            continue;
                        }

                        $eventOccurrence = new EventOccurrence;
                        $eventOccurrence['start_date']                  = (string)$xmlOccurrence->start_date;
                        $eventOccurrence['start_time']                  = $this->extractTimeOrNull( (string)$xmlOccurrence->start_time );
                        $eventOccurrence['end_date']                    = (string)$xmlOccurrence->end_date;
                        $eventOccurrence['end_time']                    = $this->extractTimeOrNull( (string)$xmlOccurrence->end_time );
                        $eventOccurrence['poi_id']                      = $occurrenceVenue['id'];
                        $eventOccurrence['utc_offset']                  = $this->vendor->getUtcOffset();
                        $eventOccurrence['vendor_event_occurrence_id']  = stringTransform::concatNonBlankStrings('-', array(
                                                                                                                        $event['vendor_event_id'],
                                                                                                                        $eventOccurrence['start_date'],
                                                                                                                        $eventOccurrence['poi_id'],
                                                                                                                    ));

                        $event['EventOccurrence'][] = $eventOccurrence;
                    }
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