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
   * Store a poi logger
   *
   * @var logImport
   */
  private $_poiLoggerObj;

  /**
   * Store a event logger
   *
   * @var logImport
   */
  private $_eventLoggerObj;


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
    $this->_categoryMap = new CategoryMap();
    $this->_poiLoggerObj = new logImport($vendorObj);
    $this->_eventLoggerObj = new logImport($vendorObj);
    $this->_poiLoggerObj->setType('poi');
    $this->_eventLoggerObj->setType('event');
  }


  /**
   * Insert the Events and Venues data into the database
   *
   *
   */
  public function insertEventCategoriesAndEventsAndVenues()
  {

    //Loop through each venue to add the cvendor categories
    foreach($this->_venues as $venue)
    {
      $this->insertVendorPoiCategories( $venue );
    }

    //Add each venue to the database
    foreach( $this->_venues as $venue )
    {
      $this->insertPoi( $venue ) ;
    }

    //Save the logger
    $this->_poiLoggerObj->save();

    //Loop through each event to add to the category mappings
    foreach($this->_events as $event)
    {
      $this->insertVendorEventCategories( $event );
    }

    //Loop through all the events to add them and occurances to database
    foreach($this->_events as $event)
    {
      $this->insertEvent( $event );
    }
  }
  

  /**
   * Insert the vendor poi categories
   *
   * @param SimpleXMLElement $poi the pois we want to insert
   * the categories for
   *
   */
  public function insertVendorPoiCategories( $poi )
  {
    foreach ( $poi->attributes->children() as $attribute )
    {
      $attributeNameString = (string) $attribute->name;

      if ( 'Venue type: ' == substr( $attributeNameString, 0, 12 ) && 12 < strlen( $attributeNameString ) )
      {
        $categoryString = substr( $attributeNameString, 12 );

        $vendorPoiCategory = Doctrine::getTable( 'VendorPoiCategory' )->findOneByName( $categoryString );
        if ( !is_object( $vendorPoiCategory) )
        {
          $newVendorPoiCategory = new VendorPoiCategory();
          $newVendorPoiCategory[ 'name' ] = $categoryString;
          $newVendorPoiCategory[ 'vendor_id' ] = $this->_vendorObj->getId();

          if( $newVendorPoiCategory->isValid() )
          {
            $newVendorPoiCategory->save();
          }
          else
          {
            Throw new Exception( $newVendorPoiCategory->getErrorStackAsString() );
          }

          $newVendorPoiCategory->free();
        }
      }
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
        $currentPoi = Doctrine::getTable('Poi')->findOneByVendorPoiIdAndVendorId((string)  $poi['id'], $this->_vendorObj['id']);

        if($currentPoi)
        {
            //Count thisi as existing
            $this->_poiLoggerObj->countExisting();
            return $currentPoi;
        }
        else
        {
            return new Poi();
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


    try{
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
      $phoneString = (string) $poi->telephone->number;
      $fullnumber = (string) $countryCodeString . ' '.$areaCodeString . ' '. $phoneString;
      $fullnumber = stringTransform::formatPhoneNumber($fullnumber , '+1');

      $poiObj[ 'phone' ] = $fullnumber;

      //Full address String
      $name = (string) $poi->identifier;
      $street = (string) $poi->street;
      $town = (string) $poi->town;
      $country = (string) $poi->country_symbol;
      $state = (string) $poi->state;
      $suburb = (string) $poi->suburb;
      $district = (string) $poi->district;

      $addressString = "$name, $street, $district, $suburb, $town, $country, $state";
      
      //gett longitude and latitude for venue
      $geoEncode = new geoEncode();
      $geoEncode->setAddress( $addressString );

      //Set longitude and latitude
      $poiObj[ 'longitude' ] = $geoEncode->getLongitude();
      $poiObj[ 'latitude' ] = $geoEncode->getLatitude();
      
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


         //Check before save if Poi is new one
        $logIsNew = $poiObj->isNew();

        //Get all changed fields
        $logChangedFields = $poiObj->getModified();

        //save the object
        $poiObj->save();

        ( $logIsNew ) ? $this->_poiLoggerObj->countNewInsert() : $this->_poiLoggerObj->addChange( 'update', $logChangedFields );
        
      }

      catch(Doctrine_Validator_Exception $error)
      {
          $log =  "Error processing Poi: \n Vendor = ". $this->_vendorObj['city']." \n type = B/C \n vendor_poi_id = ".(string) $poi->{'ID'}. " \n";
          $this->_poiLoggerObj->addError($error, $poiObj, $log);

          return $poiObj;
      }

      catch(Exception $e)
      {
          $log =  "Error processing Poi: \n Vendor = ". $this->_vendorObj['city']." \n type = B/C \n vendor_poi_id = ".(string) $poi->{'ID'}. " \n";
          $this->_poiLoggerObj->addError($e, $poiObj, $log);

          return $poiObj;
      }



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
              $priceInfoString = ( (string) $price->price_type != '' ) ? (string) $price->price_type . ' ' : '';
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

          if ( 'Venue type: ' == substr( $attributeNameString, 0, 12 ) && 12 < strlen( $attributeNameString ) )
          {
            $categoryString = substr( $attributeNameString, 12 );

            if ( ! in_array( $categoryString, $categoryArray) )
            {
              $categoryArray[] = $categoryString;
            }
          }

          $poiObj->addProperty( $attributeNameString, trim( $attribute->value ) );
        }
      }

      //store categories
      if ( 0 < count( $categoryArray ) )
      {
        $poiObj['PoiCategories'] = $this->_categoryMap->mapCategories( $this->_vendorObj, $categoryArray, 'Poi', 'theatre-music-culture' );

        $vendorCategoriesArray = new Doctrine_Collection( Doctrine::getTable( 'VendorPoiCategory' ) );
        foreach( $categoryArray as $category )
        {
          $vendorPoiCategory = Doctrine::getTable('VendorPoiCategory')->findOneByName( $category );
        
          if ( is_object( $vendorPoiCategory ) )
          {
            $vendorCategoriesArray[] = $vendorPoiCategory;
          }
          else
          {
            Throw new Exception("Invalid Vendor Poi Category");
          }
        }
        $poiObj['VendorPoiCategories'] = $vendorCategoriesArray;
      }


      try{
        //save to database
        $poiObj->save();
      }
      catch(Doctrine_Validator_Exception $error)
      {
          $log =  "Error processing Poi: \n Vendor = ". $this->_vendorObj['city']." \n type = B/C \n vendor_poi_id = ".(string) $poi->{'ID'}. " \n";
          $this->_poiLoggerObj->addError($error, $poiObj, $log);

          return $poiObj;
      }

      catch(Exception $e)
      {
          $log =  "Error processing Poi: \n Vendor = ". $this->_vendorObj['city']." \n type = B/C \n vendor_poi_id = ".(string) $poi->{'ID'}. " \n";
          $this->_poiLoggerObj->addError($e, $poiObj, $log);

          return $poiObj;
      }


      //Kill the object
      $poiObj->free();    
  }


  /**
   * Insert the vendor event categories
   *
   * @param SimpleXMLElement $event the events we want to insert
   * the categories for
   *
   */
  public function insertVendorEventCategories( $event )
  {
    

    if ( isset($event->category_combi) )
    {
      $categoryArray = $this->_concatVendorEventCategories( $event->category_combi );

      foreach( $categoryArray as $categoryString )
      {
        $vendorEventCategory = Doctrine::getTable( 'VendorEventCategory' )->findOneByName( $categoryString );

        if ( is_object( $vendorEventCategory) === false )
        {
          $newVendorEventCategory = new VendorEventCategory();
          $newVendorEventCategory[ 'name' ] = $categoryString;
          $newVendorEventCategory[ 'vendor_id' ] = $this->_vendorObj->getId();

          //save to database
          if( $newVendorEventCategory->isValid() )
          {
            $newVendorEventCategory->save();
          }
          else
          {
            Throw new Exception( $newVendorEventCategory->getErrorStackAsString() );
          }

          $newVendorEventCategory->free();
        }
      }
    }
  }

  /**
   * Insert the events
   *
   * @param SimpleXMLElement $event the events we want to insert
   *
   */
  public function insertEvent( $event )
  {
        $movieFound = false;

     /*
      * Category_combi is a node in the xml that contains all the categories which is then used for mapping.
      * in NY's case we must not add any event's or their occurances for <b>Film or Art-house &amp; indie cinema</b>
      */
      if ( isset( $event->category_combi ) )
      {
         //Concatinate the categories Categories
          $categoryArray = $this->_concatVendorEventCategories( $event->category_combi, true );

          //Get the Event Categories and their mappings
          $eventCategoriesCollection = $this->_categoryMap->mapCategories( $this->_vendorObj, $categoryArray, 'Event' );

          //Get all the Vendor Event Categories that were inserted during the first loop
          $vendorCategoriesArray = new Doctrine_Collection( Doctrine::getTable( 'VendorEventCategory' ) );

      

          //Loop through  the vendor categories
          foreach( $categoryArray as $categoryString )
          {


            $vendorEventCategory = Doctrine::getTable('VendorEventCategory')->findOneByName( (string) $categoryString );

            //If a match is found (Which should happen as the cats are inserted already)
            if ( is_object( $vendorEventCategory ) )
            {
              $vendorCategoriesArray[] = $vendorEventCategory;
            }
            else
            {
               /**
                * @todo log error
                */
              //Throw new Exception("Invalid Vendor Event Category");
            }
          }

          //Loop through and check to see if any vendor mapped catagories are 'movies'
          foreach($eventCategoriesCollection as $eventCat)
          {
              if($eventCat['name'] == 'movies')
              {
                  $movieFound = true;
                  break;
              }
          }
       }//end if loop through categories


       //Only add the event if its not movie
       if(!$movieFound)
       {
           //Set the Events requirred values
          $eventObj = new Event();
          $eventObj[ 'vendor_id' ] = $this->_vendorObj->getId();
          $eventObj[ 'vendor_event_id' ] = (string) $event['id'];
          $eventObj[ 'name' ] = (string) $event->identifier;
          $eventObj[ 'description' ] = (string) $event->description;


           //save to database
          if( $eventObj->isValid() )
          {
              $eventObj->save();


              //Event Categories
              $eventObj['EventCategories'] = $eventCategoriesCollection;
              $eventObj['VendorEventCategories'] = $vendorCategoriesArray;


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
                }

                  //deal with attributes node
                    $includeAttributesArray = array( 'Critic\'s Picks', 'Recommended or notable' );
                    foreach( $event->attributes->children() as $attribute )
                    {
                        
                          if ( is_object( $attribute->name ) && is_object( $attribute->value ) && in_array( (string) $attribute->name, $includeAttributesArray ) )
                          {
                            $eventObj->addProperty( (string) $attribute->name, (string) $attribute->value );
                          }
                    }


                    //save to database
                    $eventObj->save();
                    
                    //Loop throught the actual occurances now
                    foreach ( $event->date as $occurrence )
                    {

                      $occurrenceObj = new EventOccurrence();
                      $occurrenceObj[ 'utc_offset' ] = '-05:00';
                      $occurrenceObj[ 'start' ] = (string) $occurrence->start;
                      $occurrenceObj[ 'event_id' ] = $eventObj[ 'id' ];
                      $occurrenceObj->generateVendorEventOccurrenceId( (string) $event['id'], (string) $occurrence->venue[0]->address_id, (string) $occurrence->start );

                      //set poi id
                      $venueObj = Doctrine::getTable('Poi')->findOneByVendorPoiId( (string) $occurrence->venue[0]->address_id );
                     
                      $occurrenceObj[ 'poi_id' ] = $venueObj[ 'id' ];

                      //save to database
                      if( $occurrenceObj->isValid() )
                      {
                        $occurrenceObj->save();
                      }
                      else
                      {
                        Throw new Exception( $occurrenceObj->getErrorStackAsString() );
                      }

                      //Kill the object
                      $occurrenceObj->free();
                    }//end foreach
        }
        else
        {
            echo $eventObj->getErrorStackAsString();
        }//end if event obj is valid

        //Kill the object
            $eventObj->free();
       }//end if movie found
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