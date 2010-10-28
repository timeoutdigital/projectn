<?php
/**
 * UAE Feed Events Mapper
 *
 * @package projectn
 * @subpackage
 *
 * @author Rajeevan kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */
class UAEFeedEventsMapper extends UAEFeedBaseMapper
{
    public function mapEvents( )
    {
        foreach( $this->xml->event as $xmlNode)
        {
            try {

                // get Existing Event
                $event = Doctrine::getTable( 'Event' )->findOneByVendorIdAndVendorEventId( $this->vendor_id, trim( $xmlNode['id'] ) );
                if( $event === false )
                {
                    $event = new Event();
                }

                // map Data
                $event['vendor_id']                     = $this->vendor_id;
                $event['vendor_event_id']               = (string) $xmlNode['id'];
                $event['name']                          = (string) $xmlNode->{'name'};
                // $event['url']                           = stringTransform::formatUrl( (string) $xmlNode->{'landing_url'} );
                $event['description']                   = (string) $xmlNode->{'description'};
                $event['price']                         = (string) $xmlNode->{'prices'};

                // Timeout link
                $event->setTimeoutLinkProperty( stringTransform::formatUrl( (string) $xmlNode->{'landing_url'} ) );

                // Category
                $event->addVendorCategory( (string) $xmlNode->{'mobile-section'}['value'] );

                // Additional Information
                if( stringTransform::mb_trim( (string) $xmlNode->{'phone'} ) != '' )
                {
                    $event->addProperty( 'phone', stringTransform::formatPhoneNumber( stringTransform::mb_trim( (string) $xmlNode->{'phone'} ), $event['Vendor']['inernational_dial_code']) );
                }
                // Email
                if( stringTransform::mb_trim( (string) $xmlNode->{'email'} ) != '' )
                {
                    $event->addProperty( 'email',  stringTransform::mb_trim( (string) $xmlNode->{'email'} ) );
                }

                // handle Occurrences
                // Delete all Existing first to avoid Duplication or cancelled Occurrence
                $event['EventOccurrence']->delete();

                // add new Occurrences
                if( isset( $xmlNode->{'day-occurences'} ) )
                {
                    
                    foreach( $xmlNode->{'day-occurences'}->{'day-occurence'} as $xmlOccurrence )
                    { 
                        // Get related POI
                        $poi                                = Doctrine::getTable('Poi')->findOneByVendorIdAndVendorPoiId( $this->vendor_id, trim( (string) $xmlOccurrence->{'venue_id'} ) );
                        if( $poi === false )
                        {
                            $this->notifyImporterOfFailure( new Exception( 'UAEFeedEventsMapper::mapEvents - Occurrence - No poi found for Event :' . $event['name'] ) );
                            continue;
                        }

                        $occurrence                                 = new EventOccurrence();
                        $occurrence[ 'poi_id' ]                     = $poi['id'];
                        $occurrence[ 'utc_offset' ]                 = $event['Vendor']->getUtcOffset();
                        $occurrence[ 'start_date' ]                 = (string) $xmlOccurrence->{'start_date'};
                        $occurrence[ 'vendor_event_occurrence_id' ]  = stringTransform::concatNonBlankStrings('-', array( $event['vendor_event_id'], $poi['id'], $occurrence[ 'start_date' ] ) );
                        
                        // add to Event for saving
                        $event['EventOccurrence'][]                 = $occurrence;
                    }// occurrence
                }
                
                // save
                $this->notifyImporter( $event );

            }catch( Exception $exc ){
                $this->notifyImporterOfFailure( $exc );
            }
        } // foreach
    }
}
