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
            $poi = Doctrine::getTable( 'Poi' )->findOneByVendorIdAndVendorPoiId( $this->vendor, $vendorPoiID );
            if( $poi === false )
            {
                $this->notifyImporterOfFailure( new Exception( "Poi not found, event {$event['vendor_event_id']} occurrence" ) );
                continue;
            }

            // Map Event Occurrences
            $occurrence = new EventOccurrence;
            $occurrence['utc_offset'] = $event['Vendor']->getUtcOffset();
            $occurrence['Poi'] = $poi;

            $occurrence['star_date'] = $this->clean( (string)$xmlOccurrence->start_date );
            $occurrence['end_date'] = $this->clean( (string)$xmlOccurrence->end_date );

            $occurrence['vendor_event_occurrence_id'] = stringTransform::concatNonBlankStrings( '-', array(
                                                                                                        $event['vendor_event_id'],
                                                                                                        $occurrence['start_date'],
                                                                                                        $occurrence['poi_id']
                                                                                                    ) );
            $event['EventOccurrence'][] = $occurrence;
        }
    }
}