<?php
/**
 * Class for importing NY and Chicago Event feeds.
 *
 * @package projectn
 * @subpackage ny.import.lib
 * @author Timmy Bowler <timbowler@timeout.com>
 *
 * @copyright Timeout Communications Ltd.
 * @version 1.0.1
 *
 *
 */
class importNyChicagoEvents
{
    /**
     * simpleXmlElement object
     *
     * @var simpleXmlElement
     */
    private $_events;

    /**
     * simpleXmlElement object
     *
     * @var simpleXmlElement
     */
    private $_venues;

    /**
     * processNyXml object
     *
     * @var processNyXml
     */
    private $_xmlFeed;

    /**
     * Store a vendor
     *
     * @var Vendor
     */
    private $_vendorObj;


    /**
     * Store cageory mapper
     *
     * @var CategoryMap
     */
    private $_categoryMap;


    /**
     * Constructor
     *
     * @param simpleXmlElement Xmlfeed
     * @param Vendor Vendor object
     * @todo possibly add transactions
     *
     */
    public function  __construct( $xmlFeed, $vendorObj )
    {
        $this->_xmlFeed = $xmlFeed;
        $this->_venues = $this->_xmlFeed->getVenues();
        $this->_events = $this->_xmlFeed->getEvents();
        $this->_vendorObj = $vendorObj;
        $this->_categoryMap = new CategoryMap( false );
        ImportLogger::getInstance()->setVendor( $vendorObj );
    }


    /**
     * Insert the Events and Venues data into the database
     *
     *
     */
    public function insertEventCategoriesAndEventsAndVenues()
    {
        //Add each venue to the database
        foreach( $this->_venues as $venue )
        {
            $this->insertPoi( $venue ) ;
        }

        //echo 'Pois done';

        //Loop through each event to add to the category mappings
        /*foreach($this->_events as $event)
    {
      $this->insertVendorEventCategories( $event );
    }
        */
        //echo 'Event cats done';

        //Loop through all the events to add them and occurances to database
        foreach($this->_events as $event)
        {
            $this->insertEvent( $event );
        }
    }


    /**
     *
     * Test if the poi already exists
     *
     * @param <simpleXml> $poi
     * @return <boolean> Whether the poi has been found
     *
     */
    public function getPoi(SimpleXMLElement $poi)
    {

        //Check database for existing Poi by vendor id
        $currentPoi = Doctrine::getTable('Poi')->findOneByVendorPoiIdAndVendorId((string) $poi['id'], $this->_vendorObj['id']);

        if($currentPoi)
        {
            //Count thisi as existing
            return $currentPoi;
        }
        else
        {
            return new Poi();
        }
    }

    /**
     *
     * Test if the Event  already exists
     *
     * @param <simpleXml> $event
     * @return <boolean> Whether the poi has been found
     *
     */
    public function getEvent( $event )
    {

        //Check database for existing Poi by vendor id
        $currentEvent = Doctrine::getTable('Event')->findOneByVendorEventIdAndVendorId((string) $event['id'], $this->_vendorObj['id']);

        if($currentEvent)
        {
            return $currentEvent;
        }
        else
        {
            return new Event();
        }
    }


    /**
     * Insert the events pois
     *
     * @param SimpleXMLElement $poi the poi we want to insert
     * @todo go through the xml with a proper source xml editor to make sure that
     *  no information is left out
     *
     */
    public function insertPoi( SimpleXMLElement $poi )
    {
        $poiObj = $this->getPoi($poi);


        $poiObj[ 'vendor_poi_id' ] = (string)  $poi['id'];
        $poiObj[ 'poi_name' ] = (string) $poi->identifier;
        $poiObj[ 'street' ] = (string) $poi->street;
        $poiObj[ 'city' ] = (string) $poi->town;
        $poiObj[ 'country' ] = 'USA';
        $poiObj[ 'local_language' ] = substr( $this->_vendorObj[ 'language' ], 0, 2 );
        $poiObj[ 'additional_address_details' ] = (string) $poi->cross_street;
        $poiObj[ 'url' ] = stringTransform::formatUrl((string) $poi->website);
        $poiObj[ 'vendor_id' ] = $this->_vendorObj->getId();

        //Form and set phone number
        $countryCodeString = (string) $poi->telephone->country_code;
        $areaCodeString = (string) $poi->telephone->area_code;
        $poiObj[ 'phone' ]  = $areaCodeString. (string) $poi->telephone->number;


        //Full address String
        $name = (string) $poi->identifier;
        $street = (string) $poi->street;
        $town = (string) $poi->town;
        $country = (string) $poi->country_symbol;
        $state = (string) $poi->state;
        $suburb = (string) $poi->suburb;
        $district = (string) $poi->district;

        $addressString = "$name, $street, $district, $suburb, $town, $country, $state";

        //Set geocoding address string
        $poiObj->setGeoEncodeLookUpString($addressString);


        //deal with the "text-system" nodes
        if ( isset( $poi->{'text_system'}->text ) )
        {
            foreach( $poi->{'text_system'}->text as $text )
            {
                switch( $text->{'text_type'} )
                {
                    case 'Venue Blurb':
                        $poiObj[ 'description' ] = (string) $text->content;
                        break;
                    case 'Approach Descriptions':
                        $poiObj[ 'public_transport_links' ] = (string) $text->content;
                        break;
                    case 'Web Keywords':
                        $poiObj[ 'keywords' ] = (string) $text->content;
                        break;
                }
            }
        }

        // I deleted a $poiObj->save from here, not sure why there are 2 - pj 4-jun-10

        //Loop throught the prices node
        if ( isset( $poi->prices ) )
        {
            foreach( $poi->prices->children() as $priceId )
            {
                foreach( $priceId->children() as $price )
                {
                    if ( $price->getName() == 'general_remark')
                    {
                        $poiObj->addProperty( 'price_general_remark', (string) $price );
                    }
                    else
                    {
                        $priceInfoString =  ( (string) $price->price_type != '' ) ? (string) $price->price_type . ' ' : '';
                        $priceInfoString .= ( (string) $price->currency != '' ) ? (string) $price->currency . ' ' : '';
                        $priceInfoString .= ( (string) $price->value != '0.00' ) ? (string) $price->value . ' ' : '';
                        $priceInfoString .= ( (string) $price->value_to != '0.00' ) ? '-' . (string) $price->value_to . ' ' : '';

                        $poiObj->addProperty( 'price', trim( $priceInfoString ) );
                    }
                }
            }//end foreach
        }//end if


        $categoryArray = array();

        //Loop throught the attributes node
        if ( isset( $poi->attributes ) )
        {
            foreach ( $poi->attributes->children() as $attribute )
            {
                $attributeNameString = (string) $attribute->name;
                $attributeValueString = (string) trim( $attribute->value );

                if ( 'Venue type: ' == substr( $attributeNameString, 0, 12 ) && 12 < strlen( $attributeNameString ) )
                {
                    $categoryString = substr( $attributeNameString, 12 );

                    if ( ! in_array( $categoryString, $categoryArray) )
                    {
                        $categoryArray[] = $categoryString;
                    }
                }

                if( $attribute->name == "Critics_choice" )
                {
                    $critics_choice_value = (string) strtolower( $attribute->value );

                    // Chicago and New York seem to like to send us 'Yes' instead of 'y' every now and then.
                    if( $critics_choice_value == "yes" ) $attributeValueString = "y";
                    if( $critics_choice_value == "no" ) $attributeValueString = "n";
                }

                $poiObj->addProperty( $attributeNameString, $attributeValueString );
            }
        }
        foreach ($categoryArray as $category)
        {
            $poiObj->addVendorCategory( trim ( $category ), $this->_vendorObj->getId()  );
        }

        ImportLogger::saveRecordComputeChangesAndLog( $poiObj );

        //Kill the object
        $poiObj->free();
    }



    /**
     * Insert the events
     *
     * @param SimpleXMLElement $event the events we want to insert
     *
     */
    public function insertEvent( $event )
    {

        $eventObj = $this->getEvent( $event );

        //Set the Events required values
        $eventObj[ 'vendor_id' ] = $this->_vendorObj->getId();
        $eventObj[ 'vendor_event_id' ] = (string) $event['id'];
        $eventObj[ 'name' ] = (string) $event->identifier;
        $eventObj[ 'description' ] = (string) $event->description;

        /*
       * Category_combi is a node in the xml that contains all the categories which is then used for mapping.
       * in NY's case we must not add any event's or their occurances for <b>Film or Art-house &amp; indie cinema</b>
        */
        $categoryArray = array();
        if ( isset( $event->category_combi ) )
        {
            $categoryArray = $this->_concatVendorEventCategories( $event->category_combi, true );
        }

        foreach( $categoryArray as $category )
        {
            $eventObj->addVendorCategory($category, $this->_vendorObj);
        }

        //deal with the "text-system" nodes
        if ( isset( $event->{'text_system'}->text ) )
        {
            foreach( $event->{'text_system'}->text as $text )
            {
                switch( $text->{'text_type'} )
                {
                    case 'Prices':
                        $eventObj[ 'price' ] = (string) $text->content;
                        break;
                    case 'Contact Blurb':
                        $url = $this->_extractContactBlurbUrl( (string) $text->content );
                        if ( $url != '' ) $eventObj->url = $url;

                        $email = $this->_extractContactBlurbEmail( (string) $text->content );
                        if ( $email != ''  )
                        {
                            $eventObj->addProperty( 'email', $email );
                        }

                        $phone = $this->_extractContactBlurbPhone( (string) $text->content );
                        if ( $phone != '' )
                        {
                            $eventObj->addProperty( 'phone', $phone );
                        }

                        // add property with email, phone, url and stuff
                        break;
                    case 'Show End Date':
                        $eventObj->addProperty( 'show_end_date', (string) $text->content );
                        break;
                    case 'Legend':
                        $eventObj->addProperty( 'legend', (string) $text->content );
                        break;
                    case 'Chill Out End Note':
                        $eventObj->addProperty( 'chill_out_end_note', (string) $text->content );
                        break;
                    case 'Venue Blurb':
                        $eventObj->addProperty( 'venue_blurb', (string) $text->content );
                        break;
                    case 'Approach Descriptions':
                        $eventObj->addProperty( 'approach_description', (string) $text->content );
                        break;
                    case 'Web Keywords':
                        $eventObj->addProperty( 'web_keywords', (string) $text->content );
                        break;
                }
            }
        }//end if

        //deal with attributes node
        $includeAttributesArray = array( 'Critic\'s Picks', 'Recommended or notable' );

        foreach( $event->attributes->children() as $attribute )
        {
            if ( is_object( $attribute->name ) && is_object( $attribute->value ) && in_array( (string) $attribute->name, $includeAttributesArray ) )
            {
                $critics_choice_value = (string) strtolower( $attribute->value );

                // Chicago and New York seem to like to send us 'Yes' instead of 'y' every now and then.
                if( $critics_choice_value == "yes" ) $critics_choice_value = "y";
                if( $critics_choice_value == "no" ) $critics_choice_value = "n";

                $eventObj->addProperty( "Critics_choice", $critics_choice_value );
            }
        }

        //Add the event occurances @todo can we not rely on the event id?
        $this->addEventOccurance($event->{'date'}, $eventObj );

        ImportLogger::saveRecordComputeChangesAndLog( $eventObj );

        //Kill the object
        $eventObj->free();
    }


    /**
     * Add the
     *
     * @param Event $eventObj
     * @param Poi $poiObj
     */
    public function addEventCategoriesToPoi(Event $eventObj, Poi $poiObj)
    {
        //Loop through all categories
        foreach($eventObj['VendorEventCategory']->toArray() as $categories)
        {
            $poiObj->addVendorCategory($categories['name'], $this->_vendorObj['id']);
        }

        ImportLogger::saveRecordComputeChangesAndLog( $poiObj );
    }


    /**
     * Add the event occurances
     *
     * @param SimpleXMLElement $Occurrences
     * @param Event The events
     */
    public function addEventOccurance(SimpleXMLElement $Occurrences, Event $eventObj)
    {
        //Loop throught the actual occurances now
        foreach ( $Occurrences as $occurrence )
        {
            try {
                $vendorEventOccurrenceId = Doctrine::getTable( 'EventOccurrence' )->generateVendorEventOccurrenceId( (string) $eventObj['id'], (string) $occurrence->venue[0]->address_id, (string) $occurrence->start );
                $occurrenceObj = Doctrine::getTable( 'EventOccurrence' )->findOneByVendorEventOccurrenceId( $vendorEventOccurrenceId );

                if ( $occurrenceObj === false )
                {
                    $occurrenceObj = new EventOccurrence();
                    $occurrenceObj[ 'vendor_event_occurrence_id' ] = $vendorEventOccurrenceId;
                }

                $start = $this->extractStartDateTime( (string) $occurrence->start );
                $occurrenceObj[ 'start_date' ] = $start['date'];

                if ( $start['time'] != '00:00:00' )
                {
                    $occurrenceObj[ 'start_time' ] = $start['time'];
                }
                else
                {
                    $occurrenceObj[ 'start_time' ] = NULL;
                }

                $occurrenceObj[ 'utc_offset' ] = $this->_vendorObj->getUtcOffset( $start['datetime'] );

                $occurrenceObj[ 'event_id' ] = $eventObj[ 'id' ];

                //set poi id
                $venueObj = Doctrine::getTable('Poi')->findOneByVendorIdAndVendorPoiId( $this->_vendorObj['id'], (string) $occurrence->venue[0]->address_id );

                $occurrenceObj[ 'poi_id' ] = $venueObj[ 'id' ];

                ImportLogger::saveRecordComputeChangesAndLog( $occurrenceObj );

                //Add event categories to the POI
                $this->addEventCategoriesToPoi( $eventObj, $venueObj );

                //Kill the object
                $occurrenceObj->free();
            }
            catch( Exception $exception )
            {
                if( $occurrenceObj ) ImportLogger::getInstance()->addFailed( $occurrenceObj );
                ImportLogger::getInstance()->addError( $exception, isset( $occurrenceObj ) ? $occurrenceObj : NULL, "New York / Chicago Event Occurence Failed to Save, possibly due to Event Occurence Object Not Found in DB." );
            }
        }//end foreach
    }

    /**
     * Takes a date and returns an array in the form:
     * array(
     *  'date' => '0000-00-00',
     *  'time' => '00:00:00',
     *  'datetime' => '0000-00-00 00:00:00'
     * )
     *
     * @param string $datetime
     * @return array
     */
    private function extractStartDateTime( $datetime )
    {
        $startParts = explode( ' ', $datetime );

        $start[ 'date' ]     = $startParts[0];
        $start[ 'time' ]     = $startParts[1];
        $start[ 'datetime' ] = $datetime;

        return $start;
    }

    /*
   * Extracts and fixes up a URL out of the contact blurb in the xml
   *
   * @param string $contactBlurb
   * @return string url
   *
   * @todo remove this function and replace its occurrencis with the stringTransform equivalent
    */
    private function _extractContactBlurbUrl( $contactBlurb )
    {
        $elements = explode( ',', $contactBlurb );
        $pattern = '/^(http|https|ftp)?(:\/\/)?(www\.)?([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i';

        foreach ( $elements as $element )
        {
            if ( preg_match( $pattern, trim( $element) , $matches ) )
            {
                $url = ( $matches[ 1 ] != '' ) ? $matches[ 1 ] : 'http'; //protocol
                $url .= '://';
                $url .= ( $matches[ 3 ] != '' ) ? $matches[ 3 ] : ''; //www.
                $url .= $matches[ 4 ]; //domain

                return $url;
            }
        }
    }

    /*
   * Extracts and fixes up an email address out of the contact blurb in the xml
   *
   * @param string $contactBlurb
   * @return string email address
   *
   * @todo remove this function and replace its occurrencis with the stringTransform equivalent
    */
    private function _extractContactBlurbEmail( $contactBlurb  )
    {
        $elements = explode( ',', $contactBlurb );

        foreach ( $elements as $element )
        {
            $element = trim( $element );

            if ( filter_var( $element, FILTER_VALIDATE_EMAIL ) )
            {
                return $element;
            }
        }

        return '';
    }

    /*
   * Extracts and fixes up a phone number out of the contact blurb in the xml
   *
   * @param string $contactBluGITrb
   * @return string
   *
   * @todo remove this function and replace its occurrencis with the stringTransform equivalent
   *
    */
    private function _extractContactBlurbPhone( $contactBlurb  )
    {
        return '';
    }



    /*
   * _concatVendorEventCategories helper function to concatenate the vendor event
   * categories
   *
   * The reason is that there are some instances where the vendors categories map to
   * several nokia events and the only way is to concatinate this
   *
   *
   * E.G
   *
   * Nokia: Concerts
   * NY's concatinate to: Music | Reggie, World, Latin
   *
   * Nokia: Clubs
   * NY's concatinate to: Clubs | Reggie, World, Latin
   *
   * @param SimpleXMLElement $categoryCombiNodeObject
   * @param boolean $returnOneElementOnly flag if only one element should be
   *                 returned (the deepest nested that would be)
   * @return array of concatenated category names
   *
    */
    private function _concatVendorEventCategories( $categoryCombiNodeObject, $returnOneElementOnly = false )
    {
        $category1Object = $categoryCombiNodeObject->xpath( 'category1/.' );
        $category2Object = $categoryCombiNodeObject->xpath( 'category2/.' );
        $category3Object = $categoryCombiNodeObject->xpath( 'category3/.' );

        $delimiter = ' | ';

        $categoryArray = array();

        if ( count($category1Object) == 1 &&  trim( (string) $category1Object[ 0 ] ) != '' )
        {
            $categoryArray[ 0 ] = (string) $category1Object[ 0] ;

            if ( count($category2Object) == 1 && trim( (string) $category2Object[ 0 ] ) != '' )
            {
                $categoryArray[ ($returnOneElementOnly) ? 0 : 1 ] = (string) $category1Object[ 0 ] . $delimiter . (string) $category2Object[ 0 ];

                if ( count($category3Object) == 1 && trim( (string) $category3Object[ 0 ] )  != '' )
                {
                    $categoryArray[ ($returnOneElementOnly) ? 0 : 1 ] = (string) $category1Object[ 0 ] . $delimiter . (string) $category2Object[ 0 ] . $delimiter . (string) $category3Object[ 0 ];
                }
            }
        }

        return $categoryArray;
    }
}
