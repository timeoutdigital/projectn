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
  private $dbObj;

  /**
   * Constructor
   *
   * @param object $xmlfeed
   *
   */
  public function  __construct($xmlFeed)
  {
    $this->xmlFeed = $xmlFeed;
    $this->venues = $xmlFeed->getVenues();
    $this->events = $xmlFeed->getEvents();   
    $this->dbObj = database::factory('dev');

   
  }


  /**
   * Insert the Events and Venues data into the database
   *
   *
   */
  public function insertEventsAndVenues()
  {
     /*$sql = mysql_real_escape_string('
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

*/
    foreach($this->events as $event)
    {

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
      $suburb =   $venue[0]->suburb;

      $addressString = "$name, $street, $district, $suburb, $town, $country, $state";

      //Get longitude and latitude for venue
      $geoEncode = new geoEncode();
      $geoEncode->setAddress($addressString);

      $longitude =  $geoEncode->getLongitude();
      $latitude =  $geoEncode->getLatitude();


      //insert into database
      $sql = mysql_real_escape_string('
            INSERT INTO
              poi (
                `poi_name`,
                `house_no`,
                `street`,
                `city`,
                `district`,
                `country`,
                `country_code`,
                `longitude`,
                `latitude`,


            )
            VALUES(


            )

            ');
        $statement = $dbObj->prepare( $sql );

local_language:             {type: string(10), notnull: false}
    poi_name:                   {type: string(80), notnull: false}
    house_no:                   {type: string(16), notnull: false}
    street:                     {type: string(128), notnull: true}
    city:                       {type: string(32), notnull: true}
    district:                   {type: string(128), notnull: false}
    country:                    {type: string(3), notnull: true}
    additional_address_details: {type: string(128), notnull: false}
    zips:                       {type: string(16), notnull: false}
    country_code:               {type: string(2), notnull: true}
    extension:                  {type: string(128), notnull: false}
    longitude:                  {type: decimal(18), scale: 15, notnull: true}
    latitude:                   {type: decimal(18), scale: 15, notnull: true}
    email:                      {type: string(128, notnull: false}
    url:                        {type: string(1024), notnull: false}
    phone:                      {type: string(32), notnull: false}
    phone2:                     {type: string(32), notnull: false}
    fax:                        {type: string(32), notnull: false}
    language:                   {type: string, notnull: false}
    vendor_category:            {type: string(128), notnull: false}
    keywords:                   {type: string(512), notnull: false}
    short_description:          {type: string(2048), notnull: false}
    description:                {type: string(65535),notnull: false}
    public_transport_links:     {type: string(1024), notnull: false}
    price_information:          {type: string(512), notnull: false}
    openingtimes:               {type: string(512), notnull: false}
    star_rating:                {type: integer(1), notnull: false}
    rating:                     {type: integer(1), notnull: false}
    provider:                   {type: string(512), notnull: false}
    poi_category_id:            {type: integer, notnull: true}
    vendor_id:                  {type: integer, notnull: true}
  relations:
    Vendor:                     {local: vendor_id, foreign: id}
    PoiCategory:                {local: poi_category_id, foreign: id}



       exit;
    }
   exit;
  }


}