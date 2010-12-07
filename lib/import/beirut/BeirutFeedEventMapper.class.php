<?php
/**
 * Description
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

class BeirutFeedEventMapper extends BeirutFeedBaseMapper
{
    public function mapEvent()
    {
        foreach( $this->xmlNodes->event as $xmlNode )
        {
            try
            {
                $vendorEventId = $this->clean( (string)$xmlNode['id'] );
                $event = Doctrine::getTable( 'Event' )->findOneByVendorIdAndVendorEventId( $this->vendor['id'], $vendorEventId );
                if( $event === false )
                {
                    $event = new Event();
                }

                // Map Data
                $event['vendor_event_id']       = $vendorEventId;
                $event['Vendor']                = $this->vendor;

                $event['name']                  = $this->clean( (string)$xmlNode->name );
                $event['description']           = $this->clean( (string)$xmlNode->description );
                $event['short_description']     = $this->clean( (string)$xmlNode->short_description );
                $event['booking_url']           = $this->clean( (string)$xmlNode->booking_url );
                $event['url']                   = $this->clean( (string)$xmlNode->url );
                $event['price']                 = $this->clean( (string)$xmlNode->price );
                $event['review_date']           = $this->clean( (string)$xmlNode->review_date );
                $event['rating']                = $this->roundNumberOrNull( $this->clean( (string)$xmlNode->rating ) );


                if( $this->clean( (string) $xmlNode->timeout_url) != '' )
                {
                    $event->setTimeoutLinkProperty( (string) $xmlNode->timeout_url );
                }

                if( isset( $xmlNode->categories ) )
                {
                    $this->addVendorCategory( $event, $xmlNode );
                }

                // add Occurrences
                if( isset( $xmlNode->occurrences ) )
                {
                    $event['EventOccurrence']->delete();

                    foreach( $xmlNode->occurrences->occurrence as $xmlOccurrence )
                    {
                        $poiID = intval($this->clean( (string) $xmlOccurrence->venue_id ) );
                        if( !is_numeric( $poiID ) || $poiID <= 0 )
                        {
                            $this->notifyImporterOfFailure( new Exception( "Event {$vendorEventId}, Don't have any Venue_ID specified for the occurrence" ) );
                            continue;
                        }

                        $poi = Doctrine::getTable( 'Poi' )->findOneByVendorIdAndVendorPoiId( $event['Vendor']['id'], $poiID );
                        if( $poi === false )
                        {
                            $this->notifyImporterOfFailure( new Exception( "Event {$vendorEventId}'s Occurrence Poi not found. vendor poi id: {$poiID} '" ) );
                            continue;
                        }

                        $occurrence = new EventOccurrence;
                        $occurrence['utc_offset']   = $event['Vendor']->getUtcOffset();
                        $occurrence['Poi']          = $poi;
                        
                        $occurrence['start_date']   = $this->clean( (string) $xmlOccurrence->start_date );
                        $occurrence['end_date']     = $this->clean( (string) $xmlOccurrence->end_date );
                        $occurrence['start_time']   = $this->_validateAndGetTime( $this->clean( (string) $xmlOccurrence->start_time ) );
                        $occurrence['end_time']     = $this->_validateAndGetTime( $this->clean( (string) $xmlOccurrence->end_time ) );

                        $occurrence['vendor_event_occurrence_id']  = stringTransform::concatNonBlankStrings('-', array(
                                                                                                                    $event['vendor_event_id'],
                                                                                                                    $occurrence['start_date'],
                                                                                                                    $occurrence['poi_id'],
                                                                                                                ));
                        $event['EventOccurrence'][] = $occurrence;

                    }
                }

                $this->notifyImporter( $event );

            } catch ( Exception $e )
            {
                $this->notifyImporterOfFailure( $e, isset( $event) ? $event : null );
            }
        }

    }

    /**
     * Format time into HH:MM:SS or return NULL
     * @param string $time
     * @return mixed
     */
    private function _validateAndGetTime( $time )
    {
        if( trim( $time ) == '' || strlen( $time ) != 8 || $time == '00:00:00' )
        {
            return null;
        }

        $date = DateTime::createFromFormat( 'H:i:s', $time );
        return ( $date === false ) ? null : $time;
    }
}