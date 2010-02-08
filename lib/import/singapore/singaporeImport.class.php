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
 *  $singaporeImportObj = new singaporeImport( $xmlObj, $vendorObj, $curlImporterObj );
    $singaporeImportObj->insertCategoriesPoisEvents();
 * </code>
 *
 */
class singaporeImport {

  /*
   * @var SimpleXMLElement
   */
  private $_dataXml;

  /*
   * @var Vendor
   */
  private $_vendor;

  /*
   * @var curlImporter
   */
  private $_curlImporter;

  /**
   * Construct
   *
   * @param $dataXml SimpleXMLElement
   * @param $vendorObj Vendor
   * @param $curlImporterObj curlImporter
   *
   */
  public function  __construct( $dataXml, $vendorObj, $curlImporterObj )
  {
    $this->_dataXml = $dataXml;
    $this->_vendor = $vendorObj;
    $this->_curlImporter = $curlImporterObj;

    if ( ! $this->_vendor instanceof Vendor )
      throw new Exception( 'Invalid Vendor' );
    if ( ! $this->_dataXml instanceof SimpleXMLElement )
      throw new Exception( 'Invalid SimpleXmlElement' );
    if ( ! $this->_curlImporter instanceof curlImporter )
      throw new Exception( 'Invalid curlImporter' );
  }


  /*
   * insertCategoriesPoisEvents
   */
  public function insertCategoriesPoisEvents()
  {
    $eventsObj = $this->_dataXml->xpath( '/rss/channel/item' );

    foreach( $eventsObj as $eventObj )
    {
      $eventDetailObj = $this->fetchEventDetails( (string) $eventObj->link );

      $poiId = null;
      if ( $eventDetailObj->venue->children()->asXml() !== false )
      {
        if ( $eventDetailObj->venue->children()->asXml() !== false )
        {
          $poiId = $this->_insertPoi( $eventDetailObj->venue );
        }
      }

      /*
       * @todo look at this issue here
       * commented out since poi currently is not a must field
       * possibly replace with none venue venue
       *
      if ( $poiId === null )
      {
        throw new Exception( 'Poi is missing' );
      }*/

      if ( $eventDetailObj->children()->asXml() !== false )
      {
        $this->_insertEvent( $eventDetailObj, $poiId );
      }
      else
      {
        throw new Exception( 'Event details are missing' );
      }
    }    

    return true;
  }

  /*
   *fetchEventDetails
   *
   * @param string $url
   *
   */
  public function fetchEventDetails( $url )
  {
    $urlPartsArray = array();
    
    preg_match ( '/^(http:\/\/.*)\?event=(.*)&(?:amp;)?key=(.*)$/', $url, $urlPartsArray );

    if ( count( $urlPartsArray ) == 4 )
    {
      $parametersArray = array( 'event' => $urlPartsArray[ 2 ], 'key' => $urlPartsArray[ 3 ] );
      $this->_curlImporter->pullXml ( $urlPartsArray[ 1 ], '', $parametersArray );

      return $this->_curlImporter->getXml();
    }
    else
    {
      throw new Exception( "invalid event detail url" );
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
  private function _insertPoi( $poiObj )
  {
    
    $q = Doctrine_Query::create()
                       ->select( '*' )
                       ->from( 'Poi' )
                       ->where( 'vendor_id = ?', $this->_vendor[ 'id' ] )
                       ->andWhere( 'vendor_poi_id = ?',  (string) $poiObj->id )
                       ->execute();

    try
    {
      ( count( $q ) == 0 ) ? $poi = new Poi() : $poi = $q[ 0 ];

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

      if ( $addressArray !== false )
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
      }

      $poi->addProperty( 'issue', (string) $poiObj->issue );
      $poi->addProperty( 'critic_choice', (string) $poiObj->critic_choice );
      $poi->addProperty( 'standfirst', (string) $poiObj->standfirst );

      $poi->save();
      $poiId = $poi[ 'id' ];
      $poi->free();

    //section
    //category
    //thumb
    //image
    //hot_seat
    //views
    //data_add
    //redirect
    //highres
    //thumbnail
    //large_image
    //gallery
    //top_start
    //top_end
    //top_premium
    //top_platinum
    //has_top
    //top_logo
    //top_excerpt
    //link (to singapore website)
    //related venues

      return $poiId;
    }
    catch(Exception $e)
    {
      echo 'failed to insert/update poi: ' . (string) $poiObj->name . ' (id: ' . (string) $poiObj->id . ')' . PHP_EOL;
    }

    return null;
  }

  /*
   * _insertEvent
   *
   * @param SimpleXMLElement $eventObj
   * @param integer $poiId
   *
   * @return
   *
   */
  private function _insertEvent( $eventObj, $poiId )
  {
    $q = Doctrine_Query::create()
                       ->select( '*' )
                       ->from( 'Event' )
                       ->where( 'vendor_id = ?', $this->_vendor[ 'id' ] )
                       ->andWhere( 'vendor_event_id = ?',  (string) $eventObj->id )
                       ->execute();

    $conn = Doctrine_Manager::connection();
    try
    {
      $conn->beginTransaction();

      ( count( $q ) == 0 ) ? $event = new Event() : $event = $q[ 0 ];

      $event[ 'vendor_event_id' ] = (string) $eventObj->id;
      $event[ 'name' ] = (string) $eventObj->name;
      //$event[ 'short_description' ] = '';
      $event[ 'description' ] = (string)  $eventObj->excerpt;
      //$event[ 'booking_url' ] = '';
      $event[ 'url' ] = (string) $eventObj->website;
      $event[ 'price' ] = stringTransform::formatPriceRange( (string)  $eventObj->min_price, (string)  $eventObj->max_price );
      //$event[ 'rating' ] = '';
      $event[ 'vendor_id' ] = $this->_vendor[ 'id' ];

      $event->addProperty( 'critic_choice', (string)  $eventObj->critic_choice );
      $event->addProperty( 'opentime', (string)  $eventObj->opentime );

      //save to populate the id
      $event->save();

      if ( $poiId !== null && (string) $eventObj->date_start != '' )
      {
        $event[ 'EventProperty' ] = $this->_createEventOccurrences( $poiId, $event[ 'id' ], $eventObj->date_start, $eventObj->date_end, $eventObj->alternative_dates );
        $event->save();
      }

      $event->free();

    //issue
    //section
    //category
    //hot seat
    //views
    //data_add
    //data_change
    //redirect
    //highres
    //thumbnail
    //large_image
    //standfirst
    //gallery
    //top_start
    //top_end
    //top_premium
    //has_top
    //top_logo
    //link

    }
    catch(Exception $e)
    {
      $conn->rollback();
      echo 'failed to insert/update event / occurrence: ' . (string) $eventObj->name . ' (id: ' . (string) $eventObj->id . ')' . PHP_EOL;
    }
  }

  /*
   * creates and saves the event occurences
   *
   * @param integer $poiId
   * @param integer $eventId
   * @param SimpleXmlElement $dataStart (the node containing the start date)
   * @param SimpleXmlElement $dataEnd (the node containing the end date)
   * @param SimpleXmlElement  $alternativeDates (the node containing alternative dates)
   *
   * @todo finish implementation of the alernative dates as soon as we have an example node
   *
   *  $poiId, $event[ 'id' ], $eventObj->date_start, $eventObj->date_end, $eventObj->alternative_dates
   */
  private function _createEventOccurrences( $poiId, $eventId, $dateStart, $dateEnd, $alternativeDates )
  {

    $datesArray = array();
    
    if ( (string) $dateStart != '' )
    {
      if ( (string) $dateEnd != '' )
      {
        $datesArray[] = array( 'start' => (string) $dateStart, 'end' => (string) $dateEnd );
      }
      else
      {
        $datesArray[] = array( 'start' => (string) $dateStart );
      }
    }

    /*foreach( $alternativeDates as $alternativeDate )
    {
      if ( (string) $alternativeDate->date_start != '' )
      {
        if ( (string) $alternativeDate->date_end != '' )
        {
          $datesArray[] = array( 'start' => (string) $alternativeDate->date_start, 'end' => (string) $alternativeDate->date_end );
        }
        else
        {
          $datesArray[] = array( 'start' => (string) $alternativeDate->date_start );
        }
      }
    }*/

    $eventOccurrencesArray = new Doctrine_Collection( Doctrine::getTable( 'EventOccurrence' ) );

    foreach( $datesArray as $date )
    {
      $eventOccurrence = new EventOccurrence();
      $eventOccurrence->generateVendorEventOccurrenceId( $eventId, $poiId, (string) $date[ 'start' ] );
      //$eventOccurrence[ 'booking_url' ] ='';
      $eventOccurrence[ 'utc_offset' ] = '0';

      //the feeds do not provide an accurate time, therefore, just Y-m-d underneath
      $eventOccurrence[ 'start' ] = date( 'Y-m-d', strtotime( $date[ 'start' ] ) );
      if ( isset( $date['end'] ) )
      {
        $eventOccurrence[ 'end' ] = date( 'Y-m-d', strtotime( $date[ 'end' ] ) );
      }

      $eventOccurrence->link( 'Poi' , $poiId);
      $eventOccurrence->link( 'Event' , $eventId);

      $eventOccurrence->save();
      $eventOccurrencesArray[] = $eventOccurrence;
      $eventOccurrence->free();
    }

    return $eventOccurrencesArray;
  }
}
?>
