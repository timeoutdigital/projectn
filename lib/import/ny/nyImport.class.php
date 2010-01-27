<?php
/**
 * Class for importing Ny's feeds.
 *
 * @package ny.import.lib.projectn
 *
 * @author Timmy Bowler <timbowler@timeout.com>
 * 
 * @copyright Timeout Communications Ltd.
 * @version 1.0.1
 *
 *
 */


class importNy
{
  private $_events;
  private $_venues;
  private $_xmlFeed;
  private $_vendorObj;
  
  /**
   * Constructor
   *
   * @param object $xmlfeed
   *
   */
  public function  __construct( $xmlFeed, $vendorObj )
  {
    $this->_xmlFeed = $xmlFeed;
    $this->_venues = $this->_xmlFeed->getVenues();
    $this->_events = $this->_xmlFeed->getEvents();
    $this->_vendorObj = $vendorObj;
  }


  /**
   * Insert the Events and Venues data into the database
   *
   *
   */
  public function insertEventCategoriesAndEventsAndVenues()
  {
    foreach($this->_venues as $venue)
    {
      $this->insertVendorPoiCategories( $venue );
    }

    foreach( $this->_venues as $venue )
    {
      $this->insertPoi( $venue ) ;
    }

    foreach($this->_events as $event)
    {
      $this->insertVendorEventCategories( $event );
    }

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

        if ( is_object( $vendorPoiCategory) === false )
        {
          $newVendorPoiCategory = new VendorPoiCategory();
          $newVendorPoiCategory[ 'name' ] = $categoryString;
          $newVendorPoiCategory[ 'vendor_id' ] = $this->_vendorObj->getId();
          $newVendorPoiCategory->save();
        }
      }
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

      //Get the venue
      // $venue = $this->_xmlFeed->xmlObj->xpath('/body/address[@id='. $eventVenueId .']');

      //Set the Poi's required values
      $poiObj = new Poi();
      $poiObj[ 'vendor_poi_id' ] = (string)  $poi['id'];
      $poiObj[ 'poi_name' ] = (string) $poi->identifier;
      $poiObj[ 'street' ] = (string) $poi->street;
      $poiObj[ 'city' ] = (string) $poi->town;
      $poiObj[ 'country' ] = (string) $poi->country;      
      $poiObj[ 'local_language' ] = 'en';
      $poiObj[ 'country_code' ] = (string) $poi->country_symbol;
      $poiObj[ 'additional_address_details' ] = (string) $poi->cross_street;
      $poiObj[ 'url' ] = (string) $poi->website;

      $poiObj[ 'vendor_id' ] = $this->_vendorObj->getId();

      //Form and set phone number
      $countryCodeString = (string) $poi->country_code;
      $areaCodeString = (string) $poi->telephone->area_code;
      $phoneString = (string) $poi->telephone->number;
      $fullnumber = (string) $countryCodeString . ' '.$areaCodeString . ' '. $phoneString;
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
      
      //Get longitude and latitude for venue
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

      //save to database
      $poiObj->save();

      //deal prices node
      if ( isset( $poi->prices ) )
      {
        foreach( $poi->prices->children() as $priceId )
        {
         foreach( $priceId->children() as $price )
          {
            if ( $price->getName() == 'general_remark')
            {
              $poiPropertyObj = new PoiProperty();
              $poiPropertyObj[ 'lookup' ] = 'price_general_remark';
              $poiPropertyObj[ 'value' ] = (string) $price;
              $poiPropertyObj[ 'poi_id' ] = $poiObj[ 'id' ];
              $poiPropertyObj->save();
            }
            else
            {
              $priceInfoString = ( (string) $price->price_type != '' ) ? (string) $price->price_type . ' ' : '';
              $priceInfoString .= ( (string) $price->currency != '' ) ? (string) $price->currency . ' ' : '';
              $priceInfoString .= ( (string) $price->value != '0.00' ) ? (string) $price->value . ' ' : '';
              $priceInfoString .= ( (string) $price->value_to != '0.00' ) ? '-' . (string) $price->value_to . ' ' : '';

              $poiPropertyObj = new PoiProperty();
              $poiPropertyObj[ 'lookup' ] = 'price';
              $poiPropertyObj[ 'value' ] = trim( $priceInfoString );
              $poiPropertyObj[ 'poi_id' ] = $poiObj[ 'id' ];
              $poiPropertyObj->save();
            }
          }
        }
      }

      $categoryArray = array();
      
      // deal with the attributes
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

          $poiPropertyObj = new PoiProperty();
          $poiPropertyObj[ 'lookup' ] = $attributeNameString;
          $poiPropertyObj[ 'value' ] = $attribute->value;
          $poiPropertyObj[ 'poi_id' ] = $poiObj[ 'id' ];
          $poiPropertyObj->save();
        }
      }

      //store categories
      if ( 0 < count( $categoryArray ) )
      {
        $poiObj['PoiCategories'] = $this->mapCategories( $categoryArray, 'PoiCategory', 'theatre-music-culture' );

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

      //save to database
      $poiObj->save();

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
          $newVendorEventCategory->save();
        }
      }
    }
  }

  /**
   * Insert the events
   *
   * @param SimpleXMLElement $event the events we want to insert
   * @todo sort out attributes
   *
   */
  public function insertEvent( $event )
  {
      Doctrine::getTable('Event')->setAttribute( Doctrine::ATTR_VALIDATE, true );
      Doctrine::getTable('EventOccurrence')->setAttribute( Doctrine::ATTR_VALIDATE, true );

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

        //store categories
        if ( isset( $event->category_combi ) )
        {
          //Event Categories
          $categoryArray = $this->_concatVendorEventCategories( $event->category_combi, true );
          $eventObj['EventCategories'] = $this->mapCategories( $categoryArray, 'EventCategory' );

          //Vendor Event Categories
          $vendorCategoriesArray = new Doctrine_Collection( Doctrine::getTable( 'VendorEventCategory' ) );
          $categoryArray = $this->_concatVendorEventCategories( $event->category_combi );

          foreach( $categoryArray as $categoryString )
          {
            $vendorEventCategory = Doctrine::getTable('VendorEventCategory')->findOneByName( (string) $categoryString );

            if ( is_object( $vendorEventCategory ) )
            {
              $vendorCategoriesArray[] = $vendorEventCategory;
            }
            else
            {
              Throw new Exception("Invalid Vendor Event Category");
            }
          }
          $eventObj['VendorEventCategories'] = $vendorCategoriesArray;
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
                  $eventPropertyObj = new EventProperty();
                  $eventPropertyObj[ 'lookup' ] = 'email';
                  $eventPropertyObj[ 'value' ] = $email;
                  $eventPropertyObj[ 'event_id' ] = $eventObj[ 'id' ];
                  $eventPropertyObj->save();
                }

                $phone = $this->_extractContactBlurbPhone( (string) $text->content );
                if ( $phone != '' )
                {
                  $eventPropertyObj = new EventProperty();
                  $eventPropertyObj[ 'lookup' ] = 'phone';
                  $eventPropertyObj[ 'value' ] = $phone;
                  $eventPropertyObj[ 'event_id' ] = $eventObj[ 'id' ];
                  $eventPropertyObj->save();
                }

                // add property with email, phone, url and stuff
                break;
              case 'Show End Date':
                $eventPropertyObj = new EventProperty();
                $eventPropertyObj[ 'lookup' ] = 'show_end_date';
                $eventPropertyObj[ 'value' ] = (string) $text->content;
                $eventPropertyObj[ 'event_id' ] = $eventObj[ 'id' ];
                $eventPropertyObj->save();
                break;
              case 'Legend':
                $eventPropertyObj = new EventProperty();
                $eventPropertyObj[ 'lookup' ] = 'legend';
                $eventPropertyObj[ 'value' ] = (string) $text->content;
                $eventPropertyObj[ 'event_id' ] = $eventObj[ 'id' ];
                $eventPropertyObj->save();
                break;
              case 'Chill Out End Note':
                $eventPropertyObj = new EventProperty();
                $eventPropertyObj[ 'lookup' ] = 'chill_out_end_note';
                $eventPropertyObj[ 'value' ] = (string) $text->content;
                $eventPropertyObj[ 'event_id' ] = $eventObj[ 'id' ];
                $eventPropertyObj->save();
                break;
              case 'Venue Blurb':
                $eventPropertyObj = new EventProperty();
                $eventPropertyObj[ 'lookup' ] = 'venue_blurb';
                $eventPropertyObj[ 'value' ] = (string) $text->content;
                $eventPropertyObj[ 'event_id' ] = $eventObj[ 'id' ];
                $eventPropertyObj->save();
                break;
              case 'Approach Descriptions':
                $eventPropertyObj = new EventProperty();
                $eventPropertyObj[ 'lookup' ] = 'approach_description';
                $eventPropertyObj[ 'value' ] = (string) $text->content;
                $eventPropertyObj[ 'event_id' ] = $eventObj[ 'id' ];
                $eventPropertyObj->save();
                break;
              case 'Web Keywords':
                $eventPropertyObj = new EventProperty();
                $eventPropertyObj[ 'lookup' ] = 'web_keywords';
                $eventPropertyObj[ 'value' ] = (string) $text->content;
                $eventPropertyObj[ 'event_id' ] = $eventObj[ 'id' ];
                $eventPropertyObj->save();
                break;
            }
          }
        }

        //deal with attributes node
        foreach( $event->attributes->children() as $attribute )
        {
          if ( is_object( $attribute->name ) && is_object( $attribute->value ) )
          {
            $eventPropertyObj = new EventProperty();
            $eventPropertyObj[ 'lookup' ] = (string) $attribute->name;
            $eventPropertyObj[ 'value' ] = (string) $attribute->value;
            $eventPropertyObj[ 'event_id' ] = $eventObj[ 'id' ];
            $eventPropertyObj->save();
          }
        }

        $eventObj->save();

        foreach ( $event->date as $occurrence )
        {
          $occurrenceObj = new EventOccurrence();
          $occurrenceObj[ 'start' ] = (string) $occurrence->start;
          $occurrenceObj[ 'utc_offset' ] = '-05:00';
          $occurrenceObj[ 'event_id' ] = $eventObj[ 'id' ];
          $occurrenceObj[ 'vendor_event_occurrence_id' ] = $this->_createOccurrenceId( (string) $event['id'], (string) $occurrence->venue[0]->address_id, (string) $occurrence->start );

          //set poi id
          $venueObj = Doctrine::getTable('Poi')->findOneByVendorPoiId( (string) $occurrence->venue[0]->address_id );
          $occurrenceObj[ 'poi_id' ] = $venueObj[ 'id' ];

          if( $occurrenceObj->isValid() )
          {
            //save to database
            $occurrenceObj->save();
          }
          else
          {
            echo $occurrenceObj->getErrorStackAsString();
          }
          
          //Kill the object
          $occurrenceObj->free();
        }

      }
      else
      {
        echo $eventObj->getErrorStackAsString();
      }

      //Kill the object
      $eventObj->free();

  }

  /*
   * Creates an occurrence id out of the occurrence object
   *
   * @param SimpleXMLElement $occurrence
   * @return string
   *
   */
  private function _createOccurrenceId( $eventId, $poiId, $occurrenceStartDate  )
  {
    return $eventId . '_' . $poiId . '_' . date( 'YmdHis', strtotime( $occurrenceStartDate ) );
  }

  /*
   * Extracts and fixes up a URL out of the contact blurb in the xml
   *
   * @param string $contactBlurb
   * @return string url
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
   * @todo implement it
   * @todo move to general toolbox class
   *
   */
  private function _extractContactBlurbPhone( $contactBlurb  )
  {
    return '';
  }

  /*
   * Maps categories and returns the mapped categories as Doctrine Collecion
   *
   * @param mixed $sourceCategory (can be SimpleXMLElement or Array
   * @param string $mapClass ('PoiCategory' or 'EventCategory' supported)
   * @param string $otherCategoryNameString defaults to 'other'
   * @return array of Doctrine_Collection
   *
   * @todo move to general toolbox class
   *
   */
  public function mapCategories( $sourceCategory, $mapClass, $noMatchCategoryNameString = 'other' )
  {

    if ( ! in_array( $mapClass, array( 'PoiCategory', 'EventCategory' ) ) )
    {
      Throw new Exception("mapping class not supported");
    }

    $noMatchCategory = Doctrine::getTable( $mapClass )->findOneByName( $noMatchCategoryNameString );
    
    $categoriesMappingArray = Doctrine::getTable( $mapClass . 'Mapping' )->findByVendorId( $this->_vendorObj[ 'id' ] );

    $mappedCategoriesArray = new Doctrine_Collection( Doctrine::getTable( $mapClass ) );

    foreach( $sourceCategory as $category )
    {
      $match = false;

      foreach ( $categoriesMappingArray as $categoryMappingArray )
      {
        if (  $categoryMappingArray[ 'Vendor' . $mapClass ][ 'name' ] == (string) $category )
        {
          $mappedCategoriesArray[] = $categoryMappingArray[ $mapClass ];
          $match = true;
        }
      }

    }

    if ( $match === false && is_object( $noMatchCategory ) )
    {
      $mappedCategoriesArray[] = $noMatchCategory;
    }

    return $mappedCategoriesArray;
  }


  /*
   * _concatVendorEventCategories helper function to concatenate the vendor event
   * categories
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