<?php
/**
 * Bucharest Event Mapper
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Rajeevan kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class bucharestEventMapper extends bucharestBaseMapper
{

    public function mapEvents()
    {
        foreach( $this->xmlNodes->event as $xmlNode )
        {
            try
            {
                $vendorEventId = $this->clean( $xmlNode['id'] );
                $event = Doctrine::getTable( 'Event' )->findOneByVendorIdAndVendorEventId( $this->vendor, $vendorEventId );
                if( $event === false )
                {
                    $event = new Event();
                }

                // Map data
                $event['vendor_event_id'] = $vendorEventId;
                $event['Vendor'] = $this->vendor;

                $event['name'] = $this->clean( (string)$xmlNode->name );
                $event['description'] = $this->clean( (string)$xmlNode->description );

                // Map Category
                if( isset( $xmlNode->categories->category ) )
                {
                    $this->addVendorCategories( $event, $xmlNode );
                }

                // Map Occurrences
                if( isset( $xmlNode->occurrences->occurrence ) )
                {
                    $this->addOccurrences( $event, $xmlNode);
                }

                $this->notifyImporter( $event );
            }
            catch ( Exception $e )
            {
                $this->notifyImporterOfFailure( $e, isset( $event ) ? $event : null );
            }
        }
    }

    private function addOccurrences( Event $event, $xmlNode)
    {
        foreach( $xmlNode->occurrences->occurrence as $xmlOccurrence )
        {
            $vendorPoiID = $this->clean( (string)$xmlOccurrence->venue_id );
            $poi = Doctrine::getTable( 'Poi' )->findOneByVendorIdAndVendorPoiId( $this->vendor['id'], $vendorPoiID );
            if( $poi === false )
            {
                $this->notifyImporterOfFailure( new Exception( "Poi not found, event {$event['vendor_event_id']} occurrence" ) );
                continue;
            }

            // Map Event Occurrences
            $occurrence = new EventOccurrence;
            $occurrence['utc_offset'] = $event['Vendor']->getUtcOffset();
            $occurrence['Poi'] = $poi;

            $start_date = $this->getFormatedDateArrayOrNull( (string)$xmlOccurrence->start_date );
            $occurrence['start_date'] = $start_date['date'];
            $occurrence['start_time'] = $start_date['time'];
            
            $end_date = $this->getFormatedDateArrayOrNull( (string)$xmlOccurrence->end_date );
            $occurrence['end_date'] = $end_date['date'];
            $occurrence['end_time'] = $end_date['time'];

            $occurrence['vendor_event_occurrence_id'] = stringTransform::concatNonBlankStrings( '-', array(
                                                                                                        $event['vendor_event_id'],
                                                                                                        $occurrence['start_date'],
                                                                                                        $occurrence['poi_id']
                                                                                                    ) );
            $event['EventOccurrence'][] = $occurrence;
        }
    }

    /**
     * Split Date/Time and return array of date/time
     * @param string $dateString
     * @return array
     */
    private function getFormatedDateArrayOrNull( $dateString )
    {
        $dateArray = array( 'date' => null, 'time' => null );
        
        $dateString = $this->clean( $dateString );
        if( $dateString == '' )
        {
            return $dateArray;
        }

        // It seems that Bucharest giving us Date Time together in one line,
        // we should split it and use for Date & time
        
        if( strlen( $dateString ) > 10 )
        {
            $exploded = explode( 'T', $dateString );
            if( count($exploded) == 1 )
            {
                $dateArray['date'] = substr( $dateString, 0, 10 );
            } else if( count( $exploded == 2 ) )
            {
                $dateArray['date'] = $exploded[0];
                $dateArray['time'] = $exploded[1];
            }
        }
        else
        {
            $dateArray['date'] = $dateString;
        }

        return $dateArray;
    }
}