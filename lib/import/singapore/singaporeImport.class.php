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
  protected $_curlImporter;

  /**
   * Construct
   *
   * @param $vendorObj Vendor
   * @param $curlImporterObj curlImporter
   *
   */
  public function  __construct( $vendorObj, $curlImporterObj )
  {

    $this->_vendor = $vendorObj;
    $this->_curlImporter = $curlImporterObj;

    if ( ! $this->_vendor instanceof Vendor )
      throw new Exception( 'Invalid Vendor' );
    if ( ! $this->_curlImporter instanceof curlImporter )
      throw new Exception( 'Invalid curlImporter' );
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
      $venueDetailObj = $this->fetchDetailUrl( $poiXmlObj->link  );
      $this->insertPoi( $venueDetailObj );
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
      $eventDetailObj = $this->fetchDetailUrl( $eventXmlObj->link  );
      $this->insertEvent( $eventDetailObj );
    }

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
      $this->_curlImporter->pullXml ( $urlPartsArray[ 1 ], '', $parametersArray );

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

      if ( count( $poiObj->tags ) == 1 )
      {
        foreach( $poiObj->tags->children() as $tag)
        {
          $poi->addProperty( 'tag', (string) $tag );
        }
      }

      //add vendor categories
      $categoriesArray = array();
      if ( (string) $poiObj->section != '' ) $categoriesArray[] = (string) $poiObj->section;
      if ( (string) $poiObj->category != '' ) $categoriesArray[] = (string) $poiObj->category;
      if ( 0 < count( $categoriesArray ) )
      {
        $poi->addVendorCategory( $categoriesArray,  $this->_vendor[ 'id' ]);
      }

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


      return $poiId;
    }
    catch(Exception $e)
    {
      echo 'failed to insert/update poi: ' . (string) $poiObj->name . ' (id: ' . (string) $poiObj->id . ')' . PHP_EOL;
    }

    return null;
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

      //save to populate the id
      $event->save();

      if ( count( $eventObj->venue->id ) == 1 && (string) $eventObj->date_start != '' )
      {
        $event[ 'EventOccurrence' ] = $this->_createEventOccurrences( (string) $eventObj->venue->id, $event[ 'id' ], (string) $eventObj->date_start, (string) $eventObj->date_end, $eventObj->alternative_dates );
        $event->save();
      }

      $event->free();

    //issue
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
    //top_platinum
    //has_top
    //top_logo
    //link
    //feature


    }
    catch(Exception $e)
    {
      echo 'failed to insert/update event / occurrence: ' . (string) $eventObj->name . ' (id: ' . (string) $eventObj->id . ')' . PHP_EOL;
    }
  }

  /*
   * creates and saves the event occurences
   *
   * @param integer $poiId
   * @param integer $eventId
   * @param string $dataStart (the node containing the start date)
   * @param string $dataEnd (the node containing the end date)
   * @param SimpleXmlElement  $alternativeDates (the node containing alternative dates)
   *
   * @todo finish implementation of the alernative dates as soon as we have an example node
   *
   *  $poiId, $event[ 'id' ], $eventObj->date_start, $eventObj->date_end, $eventObj->alternative_dates
   */
  private function _createEventOccurrences( $poiId, $eventId, $dateStart, $dateEnd, $alternativeDates )
  {

    $datesArray = array();
    
    if ( $dateStart != '' )
    {
      if ( $dateEnd != '' )
      {
        $datesArray[] = array( 'start' => $dateStart, 'end' => $dateEnd );
      }
      else
      {
        $datesArray[] = array( 'start' => $dateStart );
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
      $eventOccurrence->generateVendorEventOccurrenceId( $eventId, $poiId, $date[ 'start' ] );
      //$eventOccurrence[ 'booking_url' ] ='';
      $eventOccurrence[ 'utc_offset' ] = '0';

      //the feeds do not provide an accurate time, therefore, just Y-m-d underneath
      $eventOccurrence[ 'start' ] = date( 'Y-m-d', strtotime( $date[ 'start' ] ) );
      if ( isset( $date['end'] ) )
      {
        $eventOccurrence[ 'end' ] = date( 'Y-m-d', strtotime( $date[ 'end' ] ) );
      }

      $eventOccurrence[ 'poi_id' ] = $poiId;
      $eventOccurrence[ 'event_id' ] = $eventId;

      $eventOccurrence->save();
      $eventOccurrencesArray[] = $eventOccurrence;
      $eventOccurrence->free();
    }

    return $eventOccurrencesArray;
  }
}
?>
