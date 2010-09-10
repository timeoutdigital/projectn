<?php
/**
 * Chicago Feed EVENT mapper
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
class ChicagoFeedEventMapper extends ChicagoFeedBaseMapper
{

    private $eventNodes;
    private $startingPoint;
    private $endingPoint;

    /**
     * BaseMapper constructor is overwritten by Event constructor to take additional params like eventslist, start and ending points.
     * @param Doctrine_Record $vendor
     * @param SimpleXMLElement $xml
     * @param geoEncode $geoEncoder
     * @param <type> $eventsArray
     * @param <type> $startingPoint
     * @param <type> $endingPoint 
     */
    public function __construct( Doctrine_Record $vendor, SimpleXMLElement $xml, googleGeocoder $geoEncoder = null, $eventsArray = null, $startingPoint = 0, $endingPoint = 0 )
    {
        if( !isset( $eventsArray ) )
        {
            throw new Exception( 'ChicagoFeedEventMapper:: Require Events Array, Extracted from XML by xpath' );
        }

        if( $endingPoint <= 0)
        {
            throw new Exception( 'ChicagoFeedEventMapper:: Invalid Ending Point!' );
        }

        // Set variables
        $this->eventNodes       = $eventsArray;
        $this->startingPoint    = $startingPoint;   // used for Looping Through the Data
        $this->endingPoint      = $endingPoint; // used for Looping Through the Data

        parent::__construct( $vendor, $xml, $geoEncoder );
    }

    public function mapEvents()
    {
        if( !isset( $this->eventNodes )  || !is_array( $this->eventNodes ) )
        {
            $this->notifyImporterOfFailure( new Exception( 'ChicagoFeedEventMapper::mapEvents - No Event Nodes found...' ) );
            return;
        }

        for( $i = $this->startingPoint; $i < $this->endingPoint; $i++)
        {
            $eventNode = $this->eventNodes[ $i ]; // Get the Current Index node
            
            try
            {
                // Get the existing Event
                $event = Doctrine::getTable( 'Event' )->findOneByVendorEventIdAndVendorId( (string)$eventNode['id'], $this->vendorID );

                if( !$event )
                    $event = new Event();

                // Map Data
                $event[ 'vendor_id' ]                           =  $this->vendorID;

                $event[ 'vendor_event_id' ]                     = (string) $eventNode['id'];
                $event[ 'name' ]                                = (string) $eventNode->identifier;
                $event[ 'description' ]                         = $this->fixHtmlEntities( (string) $eventNode->description );

                /*
                 * Category_combi is a node in the xml that contains all the categories which is then used for mapping.
                 * in NY's case we must not add any event's or their occurances for <b>Film or Art-house &amp; indie cinema</b>
                 */
                if ( isset( $eventNode->category_combi ) )
                {
                    // Get Child Categories
                    $categories = $this->getCategories( $eventNode->category_combi->children() );

                    // SKIP EVENT Import for Firm or Art-House
                    if( in_array( 'Film', $categories) || in_array( 'Art-house & indie cinema', $categories) )
                    {
                        continue;
                    }
                    // Add Categories
                    $event->addVendorCategory( $categories , $this->vendorID );
                }// Category Combi

                // text System
                $textSystem = $this->getXMLNodesByPath( 'text_system/text', $eventNode );
                if( $textSystem )
                {
                    $this->extractTextSystemInfo( $textSystem, $event );
                } // if text_system

                // Attributes
                $includeAttributesArray = array( 'Critic\'s Picks', 'Recommended or notable' );
                if( isset( $eventNode->attributes ) )
                {
                    foreach ( $eventNode->attributes->children() as $attribute )
                    {
                        $attributeName = (string) $attribute->name ;
                        $attributeValue = (string) $attribute->value;
                        
                        if( stringTransform::mb_trim( $attributeName ) == '' || stringTransform::mb_trim( $attributeName ) == '' ||
                                        !in_array( $attributeName, $includeAttributesArray ))
                        {
                            continue; // don't need anything not in above array list
                        }

                        // Chicago seem to like to send us 'Yes' instead of 'y' every now and then.
                        $value = substr( strtolower( $attributeValue )  , 0, 1 );
                        
                        if( strtolower( $attributeName ) == "critic's picks" )
                        {    
                            $event->setCriticsChoiceProperty( ($value == 'y') ? true : false );
                            
                        } else if( strtolower( $attributeName ) == "recommended or notable" )
                        {
                            $event->setRecommendedProperty( ($value == 'y') ? true : false );
                        }else{

                            $event->addProperty( $attributeName , $attributeValue );
                        }
                        
                    } // attribute
                }

                // Event occurrences
                $this->addEventOccurrences( $eventNode, $event);

                // Save the Records
                $this->notifyImporter( $event );

                // $event->free( true );

                unset($event, $textSystem);

            }  catch ( Exception $exception)
            {
                $this->notifyImporterOfFailure( new Exception( 'Event Exception: ' . $exception->getMessage() . ' | Vendor Event ID: ' . (string)$eventNode['id'] ) );
            }
        } // for loop
    }

    private function getCategories(SimpleXMLElement $categories )
    {
        if( !isset($categories) || !$categories ) return array();

        $categoryArray = array();
        foreach( $categories as $category )
        {
            if( stringTransform::mb_trim ( (string) $category ) != '' )
            {
                $categoryArray[] = (string) $category;
            }
        }
        return $categoryArray;
    }

    /**
     * Extract informtion from Text System and add to Event model
     * @param SimpleXMLElement $textSystem
     * @param Doctrine_Record $event
     * @return null
     */
    private function extractTextSystemInfo(  $textSystem, Doctrine_Record $event)
    {
        if( !$textSystem || !$event)
            return;

        foreach ( $textSystem as $text )
        {
            switch ( strtolower( (string) $text->{'text_type'} ) )
            {
                case 'prices':
                    $event[ 'price' ] = (string) $text->content;
                    break;

                case 'contact blurb':
                    $url = stringTransform::extractUrlsFromText( (string) $text->content );
                    $url = ( is_array( $url ) ) ? (string) array_shift( $url ) : (string) $url;
                    $event[ 'url' ] = $url;

                    $email = stringTransform::extractEmailAddressesFromText( (string) $text->content );
                    $email = ( is_array( $email ) ) ? (string) array_shift( $email ) : (string) $email;

                    if( trim( $email ) != '' )
                        $event->addProperty( 'email', $email );

                    // $phone = stringTransform::extractPhoneNumbersFromText( (string) $text->content ); // Not Implemented YET!
                    break;

                // Add The rest in Property when Not Empty!
                case 'show end date':
                case 'chill out end note':
                case 'venue blurb':
                case 'approach descriptions':
                case 'web keywords':
                case 'legend':

                    $type = strtolower( (string) $text->{'text_type'} );
                    $type = trim( $type );
                    $type = str_replace( ' ', '_', $type );

                    if( trim( (string) $text->content ) !='' )
                            $event->addProperty( $type, (string) $text->content );
                    break;
            } // Switch
        } // foreach
    }

    /**
     * Add Event Occurrences and Delete the OLD once
     * @param SimpleXMLElement $eventNode
     * @param Doctrine_Record $event
     * @return bool
     */
    private function addEventOccurrences(  $eventNode , Doctrine_Record $event )
    {
        if( !$event || !$eventNode )
            return false;

        /* We don't delete existing Occurrences as there is no End date for Occurrences...
         * However, we will add new occurrences If we find that event->end_date exists and is not expired YET!
         * otherwise Skip occurrene
         */
         $date_end = (string) $eventNode->date_end;
         // convert to time and check todasy Date
         $endDate        = strtotime( $date_end );
         $todasyDate     = mktime(0,0,0, date('m'), date('d'), date('y'));
         /* Date check moved inside loop to compansate the POIs without Category*/
        // process Occurrences
        $eventOccurrences = $this->getXMLNodesByPath('date', $eventNode);

        if( !$eventOccurrences || count( $eventOccurrences ) <= 0)
            return false;

        foreach( $eventOccurrences as $eventOccurrence )
        {
            // Generate EventOccurrenceID
            $startDate = strtotime(  (string) $eventOccurrence->start );
            $startdate_formatted  = date( 'YmdHis', $startDate );
            $venueID    = (string) $eventOccurrence->venue[0]->address_id;

            // Get Event Venue / Poi
            $poi = Doctrine::getTable( 'Poi' )->findOneByVendorIdAndVendorPoiId( $this->vendorID, $venueID );

            if( !$poi )
            {
                $this->notifyImporterOfFailure( new Exception( 'Event ' . $event['name'] . ' POI not found. Poi ID: ' . $venueID ) );
                continue;
            }

            // add Poi Category and Save POI
            $this->addVendorPoiCategory( $poi, $event );

            // Check for Event->end_date
            if( trim( $date_end ) != '' && $endDate < $todasyDate ) // When date_end given, continue processing occurrences
            {
                continue; // Continue insted of return, because event may have many occurrence with different Venues!
            }

            $vendorEventOccurrenceId = stringTransform::concatNonBlankStrings('_', array( $event['vendor_event_id'],  $venueID, $startdate_formatted ) );
            // Get Existing Occurrence or reate new one
            $occurrence = null;
            if( !$event->isNew() )
                    $occurrence = Doctrine::getTable( 'EventOccurrence' )->findOneByVendorEventOccurrenceIdAndEventId( $vendorEventOccurrenceId, $event['id'] );

            if( !$occurrence )
                $occurrence = new EventOccurrence ();

            // Update Date
            $occurrence[ 'vendor_event_occurrence_id' ] = $vendorEventOccurrenceId;
            $occurrence[ 'start_date' ]                 = date( 'Y-m-d', $startDate );
            $occurrence[ 'start_time' ]                 = ( date('H:i:s' , $startDate ) != '00:00:00' ) ? date('H:i:s' , $startDate ) : null;
            $occurrence[ 'utc_offset' ]                 = $event['Vendor']->getUtcOffset();

            $occurrence[ 'Poi' ]                        = $poi;
            $event[ 'EventOccurrence' ][]               = $occurrence; // Set Occurrence

        } // foreach

        return true;
    }

    /**
     * Add Event Categories to OPI when updating the Occurrences
     * @param Doctrine_Record $poi
     * @param Doctrine_Record $event
     * @return null
     */
    private function addVendorPoiCategory( Doctrine_Record $poi, Doctrine_Record $event )
    {
        // Check for any Existing Category
        if( $poi['VendorPoiCategory']->count() > 0 )
                return;

        // Add all event vendor Category
        foreach( $event['VendorEventCategory'] as $eventCategory )
        {
            $poi->addVendorCategory( $eventCategory['name'], $this->vendorID );
        }

        try{
            $this->notifyImporter( $poi );
        }catch(Exception $exception)
        {
            $this->notifyImporterOfFailure( new Exception( 'ChicagoFeedEventMapper:: Error Adding Poi Category for Poi ' . $poi['name'] . ' message: ' . $exception->getMessage() ) );
        }
    }
}

?>
