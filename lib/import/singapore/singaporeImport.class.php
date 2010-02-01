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

      // @todo make sure venue exists
      $this->_insertVenue( $eventDetailObj->venue );

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
      Throw new Exception( "invalid poi url" );
    }
  }

  /*
   *
   */
  private function _insertVenue( $venueObj )
  {
    
    $poi = new Poi();
    $poi[ 'vendor_poi_id' ]              = (string) $venueObj->id;
    $poi[ 'review_date' ]                = (string) $venueObj->data_change;
    $poi[ 'local_language' ]             = substr( $this->_vendor[ 'language' ], 0, 1 );
    $poi[ 'poi_name' ]                   = (string) $venueObj->name;
    $poi[ 'country' ]                    = 'SGP';
    $poi[ 'email' ]                      = '';
    $poi[ 'url' ]                        = stringTransform::formatUrl( (string) $venueObj->website );
    $poi[ 'vendor_category' ]            = '';
    $poi[ 'keywords' ]                   = '';
    $poi[ 'short_description' ]          = '';
    $poi[ 'description' ]                = (string) $venueObj->excerpt;
    $poi[ 'price_information' ]          = '';
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

      $poi[ 'phone' ]                      = stringTransform::formatPhoneNumber( '+65 ' .  (string) $addressArray[0]->phone );
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


  }


}
?>
