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
 *
 * <b>Example</b>
 * <code>
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
      $eventDetailObj = $this->fetchPoiAndPoiCategory( (string) $eventObj->link );

      $poiId = NULL;
      if ( $eventDetailObj->venue instanceof SimpleXMLElement )
      {
        $poiId = $this->_insertVenue( $eventDetailObj->venue );
      }

      if ( $poiId === NULL )
      {
        throw new Exception( 'Poi is missing' );
      }

      if ( $eventDetailObj instanceof SimpleXMLElement )
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
   *fetchPoiAndPoiCategory
   *
   * 
   *
   */
  public function fetchPoiAndPoiCategory( $url )
  {
    $urlPartsArray = array();
    
    preg_match ( '/^(http:\/\/.*)\?event=(.*)&key=(.*)$/', $url, $urlPartsArray );

    if ( count( $urlPartsArray ) == 4 )
    {
      $parametersArray = array( 'event' => $urlPartsArray[ 2 ], 'key' => $urlPartsArray[ 3 ] );
      $this->_curlImporter->pullXml ( $urlPartsArray[ 1 ], '', $parametersArray );

      return $this->_curlImporter->getXml();
    }
    else
    {
      throw new Exception( "invalid poi url" );
    }
  }

  /*
   * _insertVenue
   *
   * @param SimpleXMLElement $venueObj
   *
   * @return int $poiId
   *
   */
  private function _insertVenue( $venueObj )
  {
    
    $q = Doctrine_Query::create()
                       ->select( '*' )
                       ->from( 'Poi' )
                       ->where( 'vendor_id = ?', $this->_vendor[ 'id' ] )
                       ->andWhere( 'vendor_poi_id = ?',  (string) $venueObj->id )
                       ->execute();

    if ( count( $q ) == 0 )
    {
      $poi = new Poi();
    }
    else
    {
      $poi = $q[ 0 ];
    }
    
    $poi[ 'vendor_poi_id' ]              = (string) $venueObj->id;
    $poi[ 'review_date' ]                = (string) $venueObj->data_change;
    $poi[ 'local_language' ]             = substr( $this->_vendor[ 'language' ], 0, 1 );
    $poi[ 'poi_name' ]                   = (string) $venueObj->name;
    $poi[ 'country' ]                    = 'SGP';
    $poi[ 'email' ]                      = '';
    $poi[ 'url' ]                        = (string) $venueObj->website;
    $poi[ 'vendor_category' ]            = '';
    $poi[ 'keywords' ]                   = '';
    $poi[ 'short_description' ]          = '';
    $poi[ 'description' ]                = (string) $venueObj->excerpt;
    $poi[ 'price_information' ]          = stringTransform::formatPriceRange( $venueObj->min_price, $venueObj->max_price );
    $poi[ 'openingtimes' ]               = (string) $venueObj->opentime;
    $poi[ 'star_rating' ]                = '';
    $poi[ 'rating' ]                     = '';
    $poi[ 'provider' ]                   = '';
    $poi[ 'vendor_id' ]                  = $this->_vendor[ 'id' ];

    $addressArray = $venueObj->xpath( 'addresses[1]/address_slot' );

    if ( 0 < count($addressArray) )
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

    $poi->addProperty( 'issue', (string) $venueObj->issue );
    $poi->addProperty( 'critic_choice', (string) $venueObj->critic_choice );
    $poi->addProperty( 'min_price', (string) $venueObj->min_price );
    $poi->addProperty( 'max_price', (string) $venueObj->max_price );
    $poi->addProperty( 'standfirst', (string) $venueObj->standfirst );

    $poi->save();
    $poiId = $poi[ 'id' ];
    $poi->free();

    return $poiId;

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

    if ( count( $q ) == 0 )
    {
      $event = new Event();
    }
    else
    {
      $event = $q[ 0 ];
    }

    $event[ 'vendor_event_id' ] = (string) $eventObj->id;
    $event[ 'name' ] = (string) $eventObj->name;
    $event[ 'short_description' ] = '';
    $event[ 'description' ] = $eventObj->excerpt;
    $event[ 'booking_url' ] = '';
    $event[ 'url' ] = $eventObj->website;
    $event[ 'price' ] = stringTransform::formatPriceRange( $eventObj->min_price, $eventObj->max_price );
    $event[ 'rating' ] = '';
    $event[ 'vendor_id' ] = '';

    $event->addProperty( 'critic_choice', $eventObj->critic_choice );
    $event->addProperty( 'opentime', $eventObj->opentime );
    
    //$event[ 'EventOccurrence' ] =
    //$this->_getEventOccurrences( $eventObj->date_start, $eventObj->date_end, $eventObj->alternative_dates );

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

  /*
   *
   */
  private function _getEventOccurrences( $date_start, $date_end, $alternative_dates )
  {

    $datesArray = array();
    $datesArray[] = array( 'start' => $date_start, 'end' => $date_end );

    foreach( $alternative_dates as $alternative_date )
    {
      $datesArray[] = array( 'start' => $alternative_date->date_start, 'end' => $alternative_date->date_end );
    }

    $eventOccurrencesArray = new Doctrine_Collection( Doctrine::getTable( 'EventOccurrence' ) );

    foreach( $datesArray as $date )
    {
      
      
     

      $eventOccurrence = new EventOccurrence();
      $eventOccurrence[ 'vendor_event_occurrence_id' ] ='';
      $eventOccurrence[ 'booking_url' ] ='';
      $eventOccurrence[ 'start' ] ='';
      $eventOccurrence[ 'end' ] ='';
      $eventOccurrence[ 'utc_offset' ] ='';
      $eventOccurrence[ 'event_id' ] ='';
      $eventOccurrence[ 'poi_id' ] ='';



    }

  }
}
?>
