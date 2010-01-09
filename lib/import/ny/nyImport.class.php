<?php
/**
 * Base class for importing Ny's feeds.
 * 
 * @author Timmy Bowler <timbowler@timeout.com>
 *
 */

//define('__DIR__', pathinfo(__FILE__, PATHINFO_DIRNAME));
//require(__DIR__.'/../../../geoEncode.class.php');

class importNy
{
  private $events;
  private $venues;
  private $xmlFeed;

  /**
   * Constructor
   *
   * @param object $xmlfeed
   * @param object $geoObj
   *
   */
  public function  __construct($xmlFeed)
  {
    $this->xmlFeed = $xmlFeed;
    $this->venues = $xmlFeed->getVenues();
    $this->events = $xmlFeed->getEvents();

   
    $dbObj = database::factory('dev');

    $sql = mysql_real_escape_string('
            INSERT INTO
              event
            VALUES(


            )
               
            ');
        $statement = $dbObj->prepare( $sql );

        if( $statement->execute() )
        {
            $results = $statement->fetchAll();
        }
print_r($results);
exit;


    foreach($this->events as $event)
    {
      //print_r($event);

      //Get the event's venue id
      $eventVenueId = $event->date->venue->address_id;

      //Get the venue
      $venue = $this->xmlFeed->xmlObj->xpath('/body/address[@id='. $eventVenueId .']');
 

      $name =     $venue[0]->identifier;
      $street =   $venue[0]->street;
      $district = $venue[0]->district;
      $town =     $venue[0]->town;
      $country =  $venue[0]->country_symbol;
      $state =    $venue[0]->state;
      $suburb =  $venue[0]->suburb;

      $addressString = "$name, $street, $district, $suburb, $town, $country, $state";
      
      $geoEncode = new geoEncode();
      $geoEncode->setAddress($addressString);
     

      $longitude =  $geoEncode->getLongitude();
      $latitude =  $geoEncode->getLatitude();

      $eventObj = new Event();

      $eventObj->setName($event->identifier);
      $eventObj->setDescription($event->description);


       exit;
    }
   exit;
  }

  


}