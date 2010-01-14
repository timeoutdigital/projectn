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
  public function  __construct($xmlFeed, $vendorObj)
  {
    $this->_xmlFeed = $xmlFeed;
    $this->_venues = $xmlFeed->getVenues();
    $this->_events = $xmlFeed->getEvents();
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

      $this->insertVenue( $venue ) ;
    }

    foreach($this->_events as $event)
    {

      $this->insertEvent( $event );
    }

  }


  /**
   * Insert the events venue
   *
   * @param SimpleXMLElement $venue the venue we want to insert
   *
   */
  public function insertVenue( $venue )
  {
     //Get the venue
     // $venue = $this->_xmlFeed->xmlObj->xpath('/body/address[@id='. $eventVenueId .']');


      //Set the Poi's required values
      $poiObj = new Poi();
      $poiObj->setPoiName($venue->identifier);
      $poiObj->setStreet( $venue->street);
      $poiObj->setCity($venue->town);
      $poiObj->setCountry($venue->country);
      $poiObj->setVendorPoiId( $venue['id'] );
      $poiObj->setLocalLanguage('en');
      $poiObj->setCountryCode($venue->country_symbol);
      $poiObj->setAdditionalAddressDetails($venue->cross_street);
      $poiObj->setUrl($venue->website);
      $poiObj->setVendorId($this->_vendorObj->getId());


      //Form and set phone number
      $countryCodeString = $venue->country_code;
      $areaCodeString = $venue->telephone->area_code;
      $phoneString = $venue->telephone->number;
      $fullnumber = $countryCodeString . ' '.$areaCodeString . ' '. $phoneString;
      $poiObj->setPhone($fullnumber);


      //Full address String
      $name = $venue->identifier;
      $street = $venue->street;
      $town = $venue->town;
      $country = $venue->country_symbol;
      $state = $venue->state;
      $suburb = $venue->suburb;
      $district = $venue->district;
      $addressString = "$name, $street, $district, $suburb, $town, $country, $state";

      
      //Get longitude and latitude for venue
      $geoEncode = new geoEncode();
      $geoEncode->setAddress($addressString);

      //Set longitude and latitude
      $poiObj->setLongitude($geoEncode->getLongitude());
      $poiObj->setLatitude($geoEncode->getLatitude());

      //Get and set the child category
      $childObj =  Doctrine::getTable('PoiCategory')->getByName('theatre-music-culture');
      $poiObj->setPoiCategoryId($childObj->getId());

      //save to database
      $poiObj->save();

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
      Doctrine::getTable('Event')->setAttribute( Doctrine::ATTR_VALIDATE, true );
      Doctrine::getTable('EventOccurence')->setAttribute( Doctrine::ATTR_VALIDATE, true );

      //Set the Events requirred values
      $eventObj = new Event();

      $eventObj->setVendorId($this->_vendorObj->getId());

      $eventObj->setName( $event->identifier );

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
          $venueObj = Doctrine::getTable('Poi')->findOneByVendorPoiId( $occurrence->venue[0]->address_id );
          
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



/*
    vendor_id:                  {type: integer, notnull: true}
    name:                       {type: string(256), notnull: true}
    vendor_category:            {type: string(256), notnull: true}
    short_description:          {type: string(1024), notnull: false}
    description:                {type: string(65535), notnull: false}
    booking_url:                {type: string(1024), notnull: false}
    url:                        {type: string(1024), notnull: false}
    price:                      {type: string(1024), notnull: false}
    rating:                     {type: float, notnull: false}
    event_category_id:          {type: integer, notnull: true}
    poi_id:                     {type: integer, notnull: true}
  relations:
    EventCategory:              {local: event_category_id, foreign: id}
    Poi:                        {local: poi_id, foreign: id}
    Vendor:                     {local: vendor_id, foreign: id}
 *
 *
 * EventOccurence:
  columns:
    vendor_id:                  {type: integer, notnull: true}
    booking_url:                {type: string(1024), notnull: false}
    start:                      {type: date, notnull: true}
    end:                        {type: date, notnull: true}
    utc_offset:                 {type: time, notnull: true}
    event_id:                   {type: integer, notnull: true}
    poi_id:                     {type: integer, notnull: true}
  relations:
    Event:                      {local: event_id, foreign: id, foreignType: one}
    Poi:                        {local: poi_id, foreign: id, foreignType: one}
 *
*/



  }


}