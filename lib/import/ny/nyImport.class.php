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
    foreach( $this->_venues as $venue )
    {
      $this->insertPoi( $venue ) ;
    }

    foreach($this->_events as $event)
    {
      $this->insertVendorCategories( $event );
    }

    foreach($this->_events as $event)
    {
      $this->insertEvent( $event );
    }
  }
  

  /**
   * Insert the events pois
   *
   * @param SimpleXMLElement $poi the poi we want to insert
   * @todo go through the xml with a proper source xml editor to make sure that
   *  no information is left out
   * @todo sort out categories
   *
   */
  public function insertPoi( SimpleXMLElement $poi )
  {

      //Get the venue
      // $venue = $this->_xmlFeed->xmlObj->xpath('/body/address[@id='. $eventVenueId .']');

      //Set the Poi's required values
      $poiObj = new Poi();
      $poiObj[ 'poi_name' ] = (string) $poi->identifier;
      $poiObj[ 'street' ] = (string) $poi->street;
      $poiObj[ 'city' ] = (string) $poi->town;
      $poiObj[ 'country' ] = (string) $poi->country;
      $poiObj[ 'vendor_poi_id' ] = (string)  $poi['id'];
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
      if ( isset( $poi->{'text_system'}->text ) )              {
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

      //Get and set the child category
      $categoriesArray = new Doctrine_Collection( Doctrine::getTable( 'PoiCategory' ) );
      $categoriesArray[] = Doctrine::getTable('PoiCategory')->findOneByName('theatre-music-culture');
      $poiObj['PoiCategories'] =  $categoriesArray;
      
      //save to database
      $poiObj->save();

      //store categories as properties
      if ( isset( $poi->category_combi ) )
      {
        foreach( $poi->category_combi->children() as $category )
        {
          $cat = (string) $category;

          if ( $cat != '')
          {
            $poiPropertyObj = new PoiProperty();
            $poiPropertyObj[ 'lookup' ] = 'category';
            $poiPropertyObj[ 'value' ] = $cat;
            $poiPropertyObj[ 'poi_id' ] = $poiObj[ 'id' ];
            $poiPropertyObj->save();
          }
        }
      }

      //Kill the object
      $poiObj->free();    
  }


  /**
   * Insert the vendor categories
   *
   * @param SimpleXMLElement $event the events we want to insert
   * the categories for
   *
   */
  public function insertVendorCategories( $event )
  {
    foreach( $event->category_combi->children() as $category )
    {
      $vendorEventCategory = Doctrine::getTable( 'VendorEventCategory' )->findOneByName( (string) $category );

      if ( is_object( $vendorEventCategory) === false )
      {
        $newVendorEventCategory = new VendorEventCategory();
        $newVendorEventCategory[ 'name' ] = (string) $category;
        $newVendorEventCategory[ 'vendor_id' ] = $this->_vendorObj->getId();
        $newVendorEventCategory->save();
      }
    }
  }

  /**
   * Insert the events
   *
   * @param SimpleXMLElement $event the events we want to insert
   * @todo sort out categories
   * @todo sort out attributes
   *
   */
  public function insertEvent( $event )
  {
      Doctrine::getTable('Event')->setAttribute( Doctrine::ATTR_VALIDATE, true );
      Doctrine::getTable('EventOccurence')->setAttribute( Doctrine::ATTR_VALIDATE, true );

      //Set the Events requirred values
      $eventObj = new Event();

      $eventObj[ 'vendor_id' ] = $this->_vendorObj->getId();

      $eventObj[ 'name' ] = (string) $event->identifier;
      $eventObj[ 'description' ] = (string) $event->description;

      //save to database
      if( $eventObj->isValid() )
      {

        $eventObj->save();

        //store categories
        if ( isset( $event->category_combi ) )
        {
          $eventObj['EventCategories'] = $this->mapCategories( $event->category_combi->children() );
        }

        //deal with the "text-system" nodes
        if ( isset( $event->{'text_system'}->text ) )
        {
          foreach( $event->{'text_system'}->text as $text )
          {
            switch( $text->{'text_type'} )
            {
              case 'Prices':
                // the prices tag you might wonder about belongs to the address
                // and not the event node, therefore we use this as price
                $eventPropertyObj = new EventProperty();
                $eventPropertyObj[ 'lookup' ] = 'prices';
                $eventPropertyObj[ 'value' ] = (string) $text->content;
                $eventPropertyObj[ 'event_id' ] = $eventObj[ 'id' ];
                $eventPropertyObj->save();
                break;
              case 'Contact Blurb':
                $url = $this->extractContactBlurbUrl( (string) $text->content );
                if ( $url != '' ) $eventObj->url = $url;
                
                $email = $this->extractContactBlurbEmail( (string) $text->content );
                if ( $email != ''  )
                {
                  $eventPropertyObj = new EventProperty();
                  $eventPropertyObj[ 'lookup' ] = 'email';
                  $eventPropertyObj[ 'value' ] = $email;
                  $eventPropertyObj[ 'event_id' ] = $eventObj[ 'id' ];
                  $eventPropertyObj->save();
                }

                $phone = $this->extractContactBlurbPhone( (string) $text->content );
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

        $eventObj->save();

        foreach ( $event->date as $occurrence )
        {
          $occurrenceObj = new EventOccurence();
          $occurrenceObj[ 'start' ] = (string) $occurrence->start;
          $occurrenceObj[ 'utc_offset' ] = '-05:00';

          $occurrenceObj[ 'event_id' ] = $eventObj[ 'id' ];

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
   * Extracts and fixes up a URL out of the contact blurb in the xml
   *
   * @param string $contactBlurb
   * @return string url
   */
  private function extractContactBlurbUrl( $contactBlurb )
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
  private function extractContactBlurbEmail( $contactBlurb  )
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
   * @param string $contactBlurb
   * @return string
   *
   * @todo implement it
   */
  private function extractContactBlurbPhone( $contactBlurb  )
  {
    return '';
  }

  /*
   * Maps categories and returns the mapped categories as Doctrine Collecion
   * out of EventCategories
   *
   * @param Object $categoryXml
   * @param string $otherCategoryNameString defaults to 'other'
   * @return array of EventCategories Doctrine_Collection
   *
   */
  public function mapCategories( $categoryXml, $otherCategoryNameString = 'other' )
  {
    $otherEventCategory = Doctrine::getTable( 'EventCategory' )->findOneByName( $otherCategoryNameString );

    $eventCategoriesMappingArray = Doctrine::getTable( 'EventCategoryMapping' )->findByVendorId( $this->_vendorObj[ 'id' ] );

    $mappedCategoriesArray = new Doctrine_Collection( Doctrine::getTable( 'EventCategory' ) );

    foreach( $categoryXml as $category )
    {
      $match = false;

      foreach ( $eventCategoriesMappingArray as $eventCategoryMappingArray )
      {
        if (  $eventCategoryMappingArray[ 'VendorEventCategory' ][ 'name' ] == (string) $category )
        {
          $mappedCategoriesArray[] = $eventCategoryMappingArray[ 'EventCategory' ];
          $match = true;
        }
      }

      if ( $match === false && is_object( $otherEventCategory ) )
      {
        $mappedCategoriesArray[] = $otherEventCategory;
      }
    }  

    return $mappedCategoriesArray;
  }


 
}