<?php
/**
 * Gets geo data using Google.
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Tim Bowler <timbowler@timeout.com>
 * @copyright Timeout Communication Ltd &copyright; 2009
 *
 * @version 1.0.0
 *
 * <b>Example of usage:</b>
 *
 * The addressString must contain as much address info as possible
 *  <code>
 *
 *  $geoEncode = new geoEncode();
 *  $geoEncode->setAddress( $addressString );
 *
 *  //Set longitude and latitude
 *  $poiObj[ 'longitude' ] = $geoEncode->getLongitude();
 *  $poiObj[ 'latitude' ]  = $geoEncode->getLatitude();
 * </code>
 *
 * If a geocode cannot be found then an Exception is thrown
 *
 *
 * Google Documentation
 * @link http://code.google.com/apis/maps/documentation/services.html#Geocoding
 * @link http://code.google.com/apis/maps/documentation/reference.html#GGeoStatusCode.G_GEO_SUCCESS
 * 
 */
class geoEncode
{

  private  $addressString;
  private  $longitude;
  private  $latitude;
  private  $accuracy;
  private  $vendorObj;
  private  $lookupUrl;
  private  $response;
  private  $curl;
  private  $curlClass;
  private  $apiKey;
  private  $region;
  private  $bounds;

  /**
   * Is false if address changes, is true after a call to google
   * This is so we don't make a request unnecessarily
   *
   * @var boolean
   */
  private  $addressChanged = false;

  /**
   * 
   */
  public function  __construct( $curlClass='Curl' )
  {
    //this will is checked in setUpCurl();
    $this->curlClass = $curlClass;
  }

  /**
   * Set the address
   *
   * @param string $address String
   *
   * @return void
   * 
   */
  final public function setAddress( $address )
  {
    $this->addressString = urlencode($address);
    $this->addressChanged = true;
  }

  /**
   * Set the API key
   *
   * @param string $apiKey String
   */
  final public function setApiKey( $apiKey )
  {
    $this->apiKey = $apiKey;
  }

  /**
   * Set the region
   *
   * @param string $region String
   */
  final public function setRegion( $region )
  {
    $this->region = $region;
  }

  /**
   * Set the bounds
   *
   * @param string $bounds String
   */
  final public function setBounds( $bounds )
  {
    $this->bounds = $bounds;
  }

  /**
   *
   * Get the Longitude and Latitude details
   *
   * @param string How to return the data. array | string
   *
   */
  final public function getGeoCode()
  {
    if( !$this->addressChanged )
      return $this;

    $this->setUpCurl();
    $this->processResponse( $this->curl->exec() );
   
    $this->addressChanged = false;
    return $this;
  }

  protected function processResponse( $response )
  {
     $dataArray = explode(',', $response);

     switch($dataArray[0])
     {
         case '602': throw new GeoCodeException('G_GEO_UNKNOWN_ADDRESS');
             break;

         case '603': //throw new GeoCodeException('G_GEO_UNAVAILABLE_ADDRESS');
             break;

         case '620': //throw new GeoCodeException('G_GEO_TOO_MANY_QUERIES');
             break;
     }

     if($dataArray[0] != '200')
     {
          unset($dataArray[2]);
          unset($dataArray[3]);
     }

    $this->longitude =  ( isset( $dataArray[3] ) ? (float) $dataArray[3]: null );
    $this->latitude  =  ( isset( $dataArray[2] ) ? (float) $dataArray[2]: null );
    $this->accuracy  =  ( isset( $dataArray[1] ) ? (int) $dataArray[1]: 0 );
  }

  private function setUpCurl()
  {
    if( is_null( $this->curl ) )
      $this->curl = new $this->curlClass( $this->getLookupUrl() );

    //$this->curl->setCurlOption(CURLOPT_URL, $geoCode);
    $this->curl->setCurlOption(CURLOPT_HEADER,0); //Change this to a 1 to return headers
    $this->curl->setCurlOption(CURLOPT_FOLLOWLOCATION, 1);
    $this->curl->setCurlOption(CURLOPT_RETURNTRANSFER, 1);
  }

  final public function getRawResponse()
  {
      return $this->response;
  }

  /**
   * Get the longitude fo rthe address
   */
  final public function getLongitude()
  {
    $this->getGeoCode();
    return $this->longitude;
  }


  /**
   * Get the latitude of the address
   */
  final public function getLatitude()
  {
    $this->getGeoCode();
    return $this->latitude;
  }


  /**
   * Get the accuracy of the address
   */
  final public function getAccuracy()
  {
    $this->getGeoCode();
    return $this->accuracy;
  }

  protected function apiKeyIsValid( $apiKey )
  {
    return (!is_string( $apiKey ) || strlen( $apiKey ) != 86);
  }

  public function getLookupUrl()
  {
     $apiKey = $this->apiKey;

     if( $this->apiKeyIsValid( $apiKey ) ) $apiKey = sfConfig::get('app_google_api_key');

     $url = "http://maps.google.com/maps/geo?q=".$this->addressString."&output=csv&oe=utf8\&sensor=false&key=". $apiKey;

     if( $this->region )
       $url .= '&region='.$this->region;

     if( $this->bounds )
       $url .= '&bounds='.$this->bounds;

     return $url;
  }

}
