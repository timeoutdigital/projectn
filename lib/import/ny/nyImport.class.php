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
  public function insertEventsAndVenues()
  {

    

    foreach( $this->_venues as $venue )
    {
      $this->insertPoi( $venue ) ;
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
  public function insertPoi( $poi )
  {

      //Get the venue
      // $venue = $this->_xmlFeed->xmlObj->xpath('/body/address[@id='. $eventVenueId .']');

      //Set the Poi's required values
      $poiObj = new Poi();
      $poiObj[ 'poi_name' ] = $poi->identifier;
      $poiObj[ 'street' ] = $poi->street;
      $poiObj[ 'city' ] = $poi->town;
      $poiObj[ 'country' ] = $poi->country;
      $poiObj[ 'vendor_poi_id' ] = $poi['id'];
      $poiObj[ 'local_language' ] = 'en';
      $poiObj[ 'country_code' ] = $poi->country_symbol;
      $poiObj[ 'additional_address_details' ] = $poi->cross_street;
      $poiObj[ 'url' ] = $poi->website;

      $poiObj[ 'vendor_id' ] = $this->_vendorObj->getId();

      //Form and set phone number
      $countryCodeString = $poi->country_code;
      $areaCodeString = $poi->telephone->area_code;
      $phoneString = $poi->telephone->number;
      $fullnumber = $countryCodeString . ' '.$areaCodeString . ' '. $phoneString;
      $poiObj[ 'phone' ] = $fullnumber;


      //Full address String
      $name = $poi->identifier;
      $street = $poi->street;
      $town = $poi->town;
      $country = $poi->country_symbol;
      $state = $poi->state;
      $suburb = $poi->suburb;
      $district = $poi->district;
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
              $poiObj[ 'description' ] = $text->content;
              break;
            case 'Approach Descriptions':
              $poiObj[ 'public_transport_links' ] = $text->content;
              break;
            case 'Web Keywords':
              $poiObj[ 'keywords' ] = $text->content;
              break;
          }
        }
      }

      //Get and set the child category
      $poiCategoryObj =  Doctrine::getTable( 'PoiCategory' )->getByName( 'theatre-music-culture' );
      $poiObj[ 'poi_category_id' ] = $poiCategoryObj->getId();

      //save to database
      $poiObj->save();

      //Kill the object
      $poiObj->free();    
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

      $eventObj['vendor_id' ] = $this->_vendorObj->getId();

      $eventObj[ 'name' ] = $event->identifier;
      $eventObj[ 'description' ] = $event->description;

      //deal with the "text-system" nodes
      if ( isset( $poi->{'text_system'}->text ) )              {
        foreach( $poi->{'text_system'}->text as $text )
        {
          switch( $text->{'text_type'} )
          {
            case 'Prices':
              // the prices tag you might wonder about belongs to the address
              // and not the event node, therefore we use this as price
              $eventPropertyObj = new EventProperty();
              $eventPropertyObj[ 'lookup' ] = 'prices';
              $eventPropertyObj[ 'value' ] = $text->content;
              //$eventPropertyObj[ 'event_id' ] = $eventObj->getId();

              //$eventObj[ 'EventProperty' ] = $eventPropertyObj;

              break;
            case 'Contact Blurb':
              // add property with email, phone, url and stuff
              break;
            case 'Show End Date':
              // create function to detect end date
              break;
            case 'Legend':
              //stick it in as property
              break;
            case 'Chill Out End Note':
              // stick in property
              break;
            case 'Venue Blurb':
              // stick in property
              $poiObj[ 'description' ] = $text->content;
              break;
            case 'Approach Descriptions':
              // stick in property
              $poiObj[ 'public_transport_links' ] = $text->content;
              break;
            case 'Web Keywords':
              // stick in property
              $poiObj[ 'keywords' ] = $text->content;
              break;
          }
        }
      }

      //save to database
      if( $eventObj->isValid() )
      {

        $eventObj->save();
      
        foreach ( $event->date as $occurrence )
        {

          $occurrenceObj = new EventOccurence();
          $occurrenceObj->setStart( $occurrence->start );
          $occurrenceObj->setUtcOffset( '-05:00' );

          $occurrenceObj->setEventId( $eventObj->getId() );

          //set poi id
          $venueObj = Doctrine::getTable('Poi')->findOneByVendorPoiId( (string) $occurrence->venue[0]->address_id );


          //var_export( $venueObj );


          $occurrenceObj->setPoiId( $venueObj->getId() );

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

}