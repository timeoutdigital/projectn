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


    foreach($this->_events as $event)
    {

      //Get the event's venue id
      $this->insertVenues((int) $event->date->venue->address_id);

       exit;
    }
   exit;
  }

  /**
   * Insert the events venue
   *
   * @param int $eventVenueId The venue ID
   *
   */
  public function insertVenues($eventVenueId)
  {
     //Get the venue
      $venue = $this->_xmlFeed->xmlObj->xpath('/body/address[@id='. $eventVenueId .']');


       //insert into database
      $poiObj = new Poi();
      $poiObj->setPoiName($venue[0]->identifier);
      $poiObj->setStreet( $venue[0]->street);
      $poiObj->setCity($venue[0]->town);
      $poiObj->setCountry('USA');

      //Full address String
      $addressString = "$name, $street, $district, $suburb, $town, $country, $state";

      //Get longitude and latitude for venue
      $geoEncode = new geoEncode();
      $geoEncode->setAddress($addressString);

      $longitude =  $geoEncode->getLongitude();
      $latitude =  $geoEncode->getLatitude();

      
      $poiObj->setVendorId($this->_vendorObj->getId());

      //save to database
      $poiObj->save();

      //Kill the object
      $poiObj->free();
  }
}