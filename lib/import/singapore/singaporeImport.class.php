<?php
/**
 * Description of singaporeImport
 *
 * @package projectn
 * @subpackage singapore.import.lib
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 *
 * @version 1.0.0
 * @todo go through the file after all the questions are answered
 *
 * <b>Example</b>
 * <code>
 *  $singaporeImportObj = new singaporeImport( $vendorObj, $curlImporterObj, $logger, 'http://www.timeoutsingapore.com/xmlapi/xml_detail/?venue={venueId}&key=ffab6a24c60f562ecf705130a36c1d1e' );
 *  $singaporeImportObj->insertPois( $xmlObj );
 * </code>
 *
 */
class singaporeImport
{

    /*
   * @var SimpleXMLElement
    */
    private $_dataXml;

    /*
   * @var Vendor
    */
    private $_vendor;

    /*
   * @var Vendor
    */
    private $_logger;

    /*
   * @var curlImporter
    */
    protected $_curlImporter;

    /*
    * @var poiLookupUrl
    */
    protected $_poiLookupUrl;

    /**
     * Construct
     *
     * @param $vendorObj Vendor
     * @param $curlImporterObj curlImporter
     * @param $curlImporterObj logImport
     * @param $poiLookupUrl
     *
     */
    public function  __construct( Vendor $vendorObj, curlImporter $curlImporterObj, logImport $loggerObj, $poiLookupUrl = '' )
                {
        $this->_vendor = $vendorObj;
        $this->_curlImporter = $curlImporterObj;
        $this->_logger = $loggerObj;
        $this->_poiLookupUrl = $poiLookupUrl;

        if ( ! $this->_vendor instanceof Vendor )
            throw new Exception( 'Invalid Vendor' );
        if ( ! $this->_curlImporter instanceof curlImporter )
            throw new Exception( 'Invalid curlImporter' );
        if ( ! $this->_logger instanceof logImport )
            throw new Exception( 'Invalid logger' );
    }

    /**
     *
     * @param SimpleXMLElement $xmlObj
     */
    public function insertPois( SimpleXMLElement $xmlObj )
    {

        $poisXmlObj = $xmlObj->xpath( '/rss/channel/item' );

        foreach( $poisXmlObj as $poiXmlObj )
        {
            try
            {
                $venueDetailObj = $this->fetchDetailUrl( (string) $poiXmlObj->link );
                if ( !( $venueDetailObj instanceof SimpleXMLElement ) )
                {
                    throw new Exception( 'could not retrieve valid venue node by url: ' . (string) $poiXmlObj->link );
                }
                $this->insertPoi( $venueDetailObj );
            }
            catch( Exception $e ) {
                $this->_logger->addError( $e );
            }            
        }

    }

    /**
     *
     * @param SimpleXMLElement $xmlObj
     */
    public function insertEvents( SimpleXMLElement $xmlObj )
    {

        $eventsXmlObj = $xmlObj->xpath( '/rss/channel/item' );

        foreach( $eventsXmlObj as $eventXmlObj )
        {
            try
            {
                $eventDetailObj = $this->fetchDetailUrl( (string)  $eventXmlObj->link  );
                if ( !( $eventDetailObj instanceof SimpleXMLElement ) )
                {
                    throw new Exception( 'could not retrieve valid venue node by url: ' . (string) $eventXmlObj->link );
                }
                $this->insertEvent( $eventDetailObj );
            }
            catch( Exception $e )
            {
                $this->_logger->addError( $e );
            }            
        }

    }

    /**
     *
     * @param SimpleXMLElement $xmlObj
     */
    public function insertMovies( SimpleXMLElement $xmlObj )
    {

        $moviesXmlObj = $xmlObj->xpath( '/rss/channel/item' );

        foreach( $moviesXmlObj as $movieXmlObj )
        {
            try {
                $movieDetailObj = $this->fetchDetailUrl( (string)  $movieXmlObj->link  );
                if ( !( $movieDetailObj instanceof SimpleXMLElement ) )
                {
                    throw new Exception( 'could not retrieve valid venue node by url: ' . (string) $movieXmlObj->link );
                }
                $this->insertMovie( $movieDetailObj );
            }
            catch( Exception $e )
            {
                $this->_logger->addError( $e );
            }            
        }

    }

    /*
   *fetchEventDetails
   *
   * valid url format:
   * http://www.timeoutsingapore.com/xmlapi/xml_detail/?event=8514&key=ffab6a24c60f562ecf705130a36c1d1e
   * http://www.timeoutsingapore.com/xmlapi/xml_detail/?venue=2154&key=ffab6a24c60f562ecf705130a36c1d1e
   * http://www.timeoutsingapore.com/xmlapi/xml_detail/?movie=758&key=ffab6a24c60f562ecf705130a36c1d1e
   *
   * @param string $url
   *
    */
    public function fetchDetailUrl( $url )
    {
        $urlPartsArray = array();

        preg_match ( '/^(http:\/\/.*)\?(event|venue|movie)=(.*)&(?:amp;)?key=(.*)$/', $url, $urlPartsArray );

        if ( count( $urlPartsArray ) == 5 )
        {
            $parametersArray = array( $urlPartsArray[ 2 ] => $urlPartsArray[ 3 ], 'key' => $urlPartsArray[ 4 ] );
            $this->_curlImporter->pullXml ( $urlPartsArray[ 1 ], '', $parametersArray, 'GET', true );

            return $this->_curlImporter->getXml();
        }
        else
        {
            throw new Exception( "invalid detail url" );
        }
    }

    /*
   * _insertPoi
   *
   * @param SimpleXMLElement $venueObj
   *
   * @return int $poiId
   *
    */
    public function insertPoi( $poiObj )
    {

        $poi = Doctrine::getTable( 'Poi' )->findOneByVendorIdAndVendorPoiId( $this->_vendor[ 'id' ], (string) $poiObj->id );

        try
        {
            if ( $poi === false ) $poi = new Poi();

            $poi[ 'vendor_poi_id' ]              = (string) $poiObj->id;
            $poi[ 'review_date' ]                = (string) $poiObj->data_change;
            $poi[ 'local_language' ]             = substr( $this->_vendor[ 'language' ], 0, 2 );
            $poi[ 'poi_name' ]                   = (string) $poiObj->name;
            $poi[ 'country' ]                    = 'SGP';
            //$poi[ 'email' ]                      = '';
            $poi[ 'url' ]                        = (string) $poiObj->website;
            //$poi[ 'vendor_category' ]            = '';
            //$poi[ 'keywords' ]                   = '';
            //$poi[ 'short_description' ]          = '';
            $poi[ 'description' ]                = (string) $poiObj->excerpt;
            $poi[ 'price_information' ]          = stringTransform::formatPriceRange( $poiObj->min_price, $poiObj->max_price );
            $poi[ 'openingtimes' ]               = (string) $poiObj->opentime;
            //$poi[ 'star_rating' ]                = '';
            //$poi[ 'rating' ]                     = '';
            //$poi[ 'provider' ]                   = '';
            $poi[ 'vendor_id' ]                  = $this->_vendor[ 'id' ];

            $addressArray = $poiObj->xpath( 'addresses[1]/address_slot' );

            if ( 0 < count( $addressArray ) )
            {
                $poi[ 'longitude' ]                  = (string) $addressArray[0]->mm_lon;
                $poi[ 'latitude' ]                   = (string) $addressArray[0]->mm_lat;

                $publicTransportString = ( (string) $addressArray[0]->near_station != '' ) ? 'Near station: ' . (string) $addressArray[0]->near_station: '';
                $publicTransportString = ( (string) $addressArray[0]->buses != '' ) ? ' | ' . (string) $addressArray[0]->buses: '';
                $poi[ 'public_transport_links' ]     = $publicTransportString;

                $poi[ 'phone' ]                      = '+65 ' .  (string) $addressArray[0]->phone;
                //$poi[ 'phone2' ]                     = '';
                //$poi[ 'fax' ]                        = '';
                $poi[ 'additional_address_details' ] = (string) $addressArray[0]->location;
                $poi[ 'zips' ]                       = (string) $addressArray[0]->postcode;
                //$poi[ 'house_no' ]                   = '';
                $poi[ 'street' ]                     = (string) $addressArray[0]->address;
                $poi[ 'city' ]                       = 'Singapore';
                //$poi[ 'district' ]                   = '';
                $geoEncodeLookupString = stringTransform::concatNonBlankStrings( ', ', array( $poi[ 'street' ], $poi[ 'additional_address_details' ], $poi[ 'zips' ], $poi[ 'city' ]  ) );
                $poi->setGeoEncodeLookUpString( $geoEncodeLookupString );
            }

            
            $poi->addProperty( 'Critics_choice', (string) $poiObj->critic_choice );
            $poi->addProperty( 'Timeout_link', (string) $poiObj->link );

            /**
             * The commented out properties are not ented becuase they are not generic in relation to the other feeds
             */
            //$poi->addProperty( 'standfirst', (string) $poiObj->standfirst );
            //$poi->addProperty( 'issue', (string) $poiObj->issue );
            /**if ( count( $poiObj->tags ) == 1 )
            {
                foreach( $poiObj->tags->children() as $tag) {
                    $poi->addProperty( 'tag', (string) $tag );
                }
            }**/

            //add vendor categories
            $categoriesArray = array();
            if ( (string) $poiObj->section != '' ) $categoriesArray[] = (string) $poiObj->section;
            if ( (string) $poiObj->category != '' ) $categoriesArray[] = (string) $poiObj->category;
            if ( 0 < count( $categoriesArray ) )
            {
                $poi->addVendorCategory( $categoriesArray,  $this->_vendor[ 'id' ]);
            }

            // add images
            $this->addImageHelper( $poi, $poiObj->highres );
            $this->addImageHelper( $poi, $poiObj->large_image );
            $this->addImageHelper( $poi, $poiObj->thumbnail );
            $this->addImageHelper( $poi, $poiObj->thumb );
            $this->addImageHelper( $poi, $poiObj->image );

            //Save the object and log the changes
            //pre-save
            $logIsNew = $poi->isNew();
            $logChangedFields = $poi->getModified();
            //save
            $poi->save();
            //post-save
            ( $logIsNew ) ? $this->_logger->countNewInsert() : $this->_logger->addChange( 'update', $logChangedFields );

            $poiId = $poi[ 'id' ];
            $poi->free();

            return $poiId;

            //currently not used fields
            //
            //thumb
            //image
            //
            //hot_seat
            //views
            //data_add
            //redirect
            //standfirst
            //gallery
            //top_start
            //top_end
            //top_premium
            //top_platinum
            //has_top
            //top_logo
            //top_excerpt
            //link (to singapore website)
            //related venues (and children)
            //feature and subnodes (incl. rating, etc)

        }
        catch( Exception $e )
        {
          $log =  'Error processing Poi: \n Vendor = '. $this->_vendor['city'].' \n vendor_poi_id = ' . (string) $poiObj->id . ' \n';
          $this->_logger->addError($e, $poi, $log );
        }

    }


    /*
   * insertEvent
   *
   * @param SimpleXMLElement $eventObj
   * @param integer $poiId
   *
   * @return
   *
    */
    public function insertEvent( $eventObj )
    {

        $event = Doctrine::getTable( 'Event' )->findOneByVendorIdAndVendorEventId( $this->_vendor[ 'id' ], (string) $eventObj->id );

        try
        {

            if ( $event === false ) $event = new Event();

            $event[ 'vendor_event_id' ] = (string) $eventObj->id;
            $event[ 'name' ] = (string) $eventObj->name;
            //$event[ 'short_description' ] = '';
            $event[ 'description' ] = (string)  $eventObj->excerpt;
            //$event[ 'booking_url' ] = '';
            $event[ 'url' ] = (string) $eventObj->website;
            $event[ 'price' ] = stringTransform::formatPriceRange( (string) $eventObj->min_price, (string) $eventObj->max_price );
            //$event[ 'rating' ] = '';
            $event[ 'vendor_id' ] = $this->_vendor[ 'id' ];

            $event->addProperty( 'critic_choice', (string)  $eventObj->critic_choice );
            $event->addProperty( 'opentime', (string)  $eventObj->opentime );

            if ( count( $eventObj->tags ) == 1 )
            {
                foreach( $eventObj->tags->children() as $tag)
                {
                    $event->addProperty( 'tag', (string) $tag );
                }
            }

            //add vendor categories
            $categoriesArray = array();
            if ( (string) $eventObj->section != '' ) $categoriesArray[] = (string) $eventObj->section;
            if ( (string) $eventObj->category != '' ) $categoriesArray[] = (string) $eventObj->category;
            if ( 0 < count( $categoriesArray ) )
            {
                $event->addVendorCategory( $categoriesArray,  $this->_vendor[ 'id' ]);
            }

            // add images
            $this->addImageHelper( $event, $eventObj->highres );
            $this->addImageHelper( $event, $eventObj->size1 );
            $this->addImageHelper( $event, $eventObj->large_image );
            $this->addImageHelper( $event, $eventObj->thumbnail );

            //save to populate the id

            //Save the object and log the changes
            //pre-save
            $logIsNew = $event->isNew();
            $logChangedFields = $event->getModified();
            //save
            $event->save();
            //post-save
            ( $logIsNew ) ? $this->_logger->countNewInsert() : $this->_logger->addChange( 'update', $logChangedFields );

            if ( count( $eventObj->venue->id ) == 1 && (string) $eventObj->date_start != '' )
            {
                $this->_createEventOccurrence( (string) $eventObj->venue->id, $event[ 'id' ], (string) $eventObj->date_start, (string) $eventObj->date_end );
            }
            
            // deal with the alternative dates
            $alternativeDatesArray = $eventObj->xpath( 'alternative_dates' );
            $this->_addAlternativeDates( (string) $eventObj->venue->id, $event[ 'id' ], $alternativeDatesArray );

            //free at last
            $event->free();

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

        }
        catch(Exception $e)
        {
            $log =  'Error processing Event: \n Vendor = '. $this->_vendor['city'].' \n vendor_event_id = ' . (string) $eventObj->id . ' \n';
            $this->_logger->addError($e, $event, $log );
        }
    }


    /*
   * insertMovie
   *
   * @param SimpleXMLElement $venueObj
   *
   * @return int $poiId
   *
    */
    public function insertMovie( $movieXml )
    {
        // check if we can insert the movie (workaround as the if showing information is missing
        if ( !$this->checkIfMovieInsertable( $movieXml, 61 ) ) return false;

        $movieObj = Doctrine::getTable( 'Movie' )->findOneByVendorIdAndVendorMovieId( $this->_vendor[ 'id' ], (string) $movieXml->id );

        try
        {
            if ( $movieObj === false ) $movieObj = new Movie();

            $movieObj[ 'vendor_id' ] = $this->_vendor[ 'id' ];
            $movieObj[ 'vendor_movie_id' ] = (string) $movieXml->id;
            $movieObj[ 'name' ] = (string) $movieXml->title;
            //$movieObj[ 'plot' ] = (string) $movieXml->synopsis;
            $movieObj[ 'review' ] = (string) $movieXml->synopsis;
            $movieObj[ 'url' ] = (string) $movieXml->link;
            //$movieObj[ 'rating' ] = ;

           

            $movieObj[ 'utf_offset' ] = $this->_vendor->getUtcOffset();
            //$movieObj[ 'poi_id' ] = ;

            //properties
            if ( (string) $movieXml->director != '' ) $movieObj->addProperty( 'Director', (string) $movieXml->director );
            if ( (string) $movieXml->cast != '' ) $movieObj->addProperty( 'Cast', (string) $movieXml->cast );
            if ( (string) $movieXml->length != '' ) $movieObj->addProperty( 'Runtime', (string) $movieXml->length );
           // if ( (string) $movieXml->origin != '' ) $movieObj->addProperty( 'origin', (string) $movieXml->origin );
           // if ( (string) $movieXml->year_production != '' ) $movieObj->addProperty( 'Year', (string) $movieXml->year_production );
            if ( (string) $movieXml->trailer_url != '' ) $movieObj->addProperty( 'Trailer_url', (string) $movieXml->trailer_url );
            if ( (string) $movieXml->website != '' ) $movieObj->addProperty( 'Website', (string) $movieXml->website );
            if ( (string) $movieXml->critic_choice != '' ) $movieObj->addProperty( 'Critics_choice', (string) $movieXml->critic_choice );
            if ( (string) $movieXml->certificate != '' ) $movieObj->addProperty( 'Certificate', (string) $movieXml->certificate );
            //if ( (string) $movieXml->opens != '' ) $movieObj->addProperty( 'opens', (string) $movieXml->opens );
            if ( (string) $movieXml->link != '' ) $movieObj->addProperty( 'Timeout_link', (string) $movieXml->link );
            
            $movieObj->addProperty( 'Age_rating', $this->extractSingaporeAgeRatingCode( (string) $movieXml->certificate));
           
            //genres
            if ( (string) $movieXml->category != '' ) $movieObj->addGenre( (string) $movieXml->category );

            // add images
            $this->addImageHelper( $movieObj, $movieXml->highres );
            $this->addImageHelper( $movieObj, $movieXml->large_image );
            $this->addImageHelper( $movieObj, $movieXml->thumbnail );
            $this->addImageHelper( $movieObj, $movieXml->image );
            $this->addImageHelper( $movieObj, $movieXml->thumb );

            // currently not used fields
            //issue
            //data_add
            //data_change
            //local
            //
            //image
            //thumb
            //
            //views
            //feature (and all its children)
            //tags (and its children)

            //Save the object and log the changes
            //pre-save
            $logIsNew = $movieObj->isNew();
            $logChangedFields = $movieObj->getModified();
            //save
            $movieObj->save();
            //post-save
            ( $logIsNew ) ? $this->_logger->countNewInsert() : $this->_logger->addChange( 'update', $logChangedFields );

            $movieId = $movieObj[ 'id' ];
            $movieObj->free();

            return $movieId;
        }
        catch( Exception $e )
        {
            $log =  'Error processing Movie: \n Vendor = '. $this->_vendor['city'].' \n vendor_movie_id = ' . (string) $movieObj->id . ' \n';
            $this->_logger->addError($e, $movieObj, $log );
        }

    }


   /*
   * creates and saves the event occurences
   *
   * @param integer $poiId
   * @param integer $eventId
   * @param string $dataStart (the node containing the start date)
   * @param string $dataEnd (the node containing the end date)
    */
    private function _createEventOccurrence( $poiId, $eventId, $dateStart, $dateEnd = '' )
    {

        $datesArray = array();

        if ( $dateStart != '' )
        {
            if ( $dateEnd != '' )
            {
                $datesArray[] = array( 'start' => $dateStart, 'end' => $dateEnd );
            }
            else {
                $datesArray[] = array( 'start' => $dateStart );
            }
        }

        //lookup if we have the poi and if not try to fetch it
        $poi = Doctrine::getTable( 'Poi' )->findOneByVendorIdAndVendorPoiId( $this->_vendor[ 'id' ], $poiId );

        if ( $poi === false )
        {
            $this->tryToInsertMissingPoi( $poiId );
        }

        foreach( $datesArray as $date )
        {

            try {
                $vendorEventOccurrenceId = Doctrine::getTable( 'EventOccurrence' )->generateVendorEventOccurrenceId( $eventId, $poiId, $date[ 'start' ] );
                $eventOccurrence = Doctrine::getTable( 'EventOccurrence' )->findOneByVendorEventOccurrenceId( $vendorEventOccurrenceId );

                if ( $eventOccurrence === false )
                {
                    $eventOccurrence = new EventOccurrence();
                    $eventOccurrence[ 'vendor_event_occurrence_id' ] = $vendorEventOccurrenceId;
                }

                //$eventOccurrence[ 'booking_url' ] ='';
                $eventOccurrence[ 'utc_offset' ] = $this->_vendor->getUtcOffset( $date[ 'start' ] );

                //the feeds do not provide an accurate time, therefore, just Y-m-d underneath
                $eventOccurrence[ 'start_date' ] = date( 'Y-m-d', strtotime( $date[ 'start' ] ) );
                if ( isset( $date['end_date'] ) )
                {
                    $eventOccurrence[ 'end_date' ] = date( 'Y-m-d', strtotime( $date[ 'end' ] ) );
                }

                $eventOccurrence[ 'poi_id' ] = $poiId;
                $eventOccurrence[ 'event_id' ] = $eventId;

                //save
                $eventOccurrence->save();
                $eventOccurrence->free();
            }
            catch( Exception $e )
            {
                $log =  'Error processing EventOccurrence: \n Vendor = '. $this->_vendor['city'].' \n vendor_event_occurrence_id = ' . $vendorEventOccurrenceId . ' \n';
                $this->_logger->addError($e, $eventOccurrence, $log );
            }
        }
    }


    /**
     * extracts the age rating codes for Singapore out of an
     * arbitrary string
     *
     * @param string $ageratingString
     * @return string
     */
    public function extractSingaporeAgeRatingCode( $ageratingString )
    {
        $ageratingArray = explode( '-',  $ageratingString );
        $ageratingCodeString = trim( $ageratingArray[ 0 ] );

        if ( in_array( $ageratingCodeString, array( 'G', 'PG', 'NC16', 'M18', 'R18', 'R21' ) ) )
        {
            return $ageratingCodeString;
        }

        return '';
    }


    /**
     * checks whether a movie should be inserted
     *
     * @param SimpleXMLElement $xmlElement
     * @param integer $daysInThePast
     * @return boolean
     */
    private function checkIfMovieInsertable( SimpleXMLElement $xmlElement, $daysInThePast )
    {

        $elementTime = strtotime( (string) $xmlElement->opens );

        if ( $elementTime === false )
        {
            $elementTime = strtotime( (string) $xmlElement->data_add );
        }

        if ( $elementTime === false)
        {
            return true;
        }

        $someDaysAgo = strtotime( '-' . $daysInThePast . ' days' );

        if ( $someDaysAgo < $elementTime  )
        {
            return true;
        }

        return false;
    }

    /**
     * helper function to add images
     *
     * @param Doctrine_Record $storeObject
     * @param SimpleXMLElement $element
     */
    protected function addImageHelper( Doctrine_Record $storeObject, SimpleXMLElement $element )
    {
        if ( (string) $element != '' )
        {
            try
            {
                $storeObject->addMediaByUrl( (string) $element );
            }
            catch( Exception $e )
            {
                $this->_logger->addError( $e );
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
    private function _createDateRange( $startDate, $endDate, $returnFormat = 'Y-m-d' )
    {
        $dates = array();
        $startDate = strtotime( $startDate );
        $endDate = strtotime( $endDate );

        if ( $startDate === false || $endDate === false || $endDate < $startDate )
        {
            return $dates;
        }
       
        $currentDate = $startDate;

        while( $currentDate <= $endDate )
        {
            $dates[] = date( $returnFormat, $currentDate );
            $currentDate = $currentDate + 86400 ;
        }

        return $dates;
    }


    /**
     * adds occurrences by alternative dates
     *
     * @param integer $poiId
     * @param integer $eventId
     * @param array $alternativeDates
     */
    private function _addAlternativeDates( $poiId, $eventId, $alternativeDates )
    {
        $parsedAlternativeDatesArray = array();
        $outputDateFormat = 'D, j M Y 16:00:00 +0000';

        foreach( $alternativeDates as $alternativeDate )
        {

            //handle date ranges for example 03/17/2010 - 03/25/2010
            $dateRangeMatches = array();
            $dateRangeMatch = preg_match( '/^([0-9]{2}\/[0-9]{2}\/[0-9]{4})\s\-\s([0-9]{2}\/[0-9]{2}\/[0-9]{4})$/', (string) $alternativeDate, $dateRangeMatches );
            //handle single dates
            $singleDateMatches = array();
            $singleDateMatch = preg_match( '/^([0-9]{2}\/[0-9]{2}\/[0-9]{4})$/', (string) $alternativeDate, $singleDateMatches );

            if ( $dateRangeMatch )
            {
                $parsedAlternativeDatesArray[] = $this->_createDateRange( $dateRangeMatches[ 1 ], $dateRangeMatches[2], $outputDateFormat );
            }
            elseif ( $singleDateMatch )
            {
                $parsedAlternativeDatesArray[][] = date( $outputDateFormat, strtotime( $singleDateMatches[ 1 ] ) );
            }
        }

        foreach ( $parsedAlternativeDatesArray as $wrapperArray )
        {
            foreach ( $wrapperArray as $date )
            {
                $this->_createEventOccurrence( $poiId, $eventId, $date );
            }
        }
    }

    public function tryToInsertMissingPoi( $poiId )
    {
        if ( $this->_poiLookupUrl == '')
        {
            throw new Exception( 'no venue lookup url provided, venue lookup failed' );
        }

        $lookupUrl = str_replace( '{venueId}', $poiId, $this->_poiLookupUrl );

        try
        {
            $venueDetailObj = $this->fetchDetailUrl( $lookupUrl );
            if ( !( $venueDetailObj instanceof SimpleXMLElement ) )
            {
                throw new Exception( 'could not retrieve valid venue node by url: ' . $lookupUrl );
            }       
            $this->insertPoi( $venueDetailObj );
        }
        catch( Exception $e ) {
            $this->_logger->addError( $e );
        }

    }

}
?>
