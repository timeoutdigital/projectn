<?php
/**
 * Base geocoder class
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communication Ltd &copyright; 2009
 *
 * @version 1.0.0
 *
 * <b>Example of usage:</b>
 *
 * The addressString must contain as much address info as possible
 *  <code>

 * </code>
 * 
 */

abstract class geocoder
{
    /**
   * Unknown location
   * @var integer
   */
  const ACCURACY_UNKNOWN = 0;

  /**
   * Country level accuracy
   * @var integer
   */
  const ACCURACY_COUNTRY = 1;

  /**
   * Region (state, province, prefecture, etc.) level accuracy.
   * @var integer
   */
  const ACCURACY_REGION = 2;

  /**
   * Sub-region (county, municipality, etc.) level accuracy.
   * @var integer
   */
  const ACCURACY_SUB_REGION = 3;

  /**
   * Town (city, village) level accuracy.
   * @var integer
   */
  const ACCURACY_TOWN = 4;

  /**
   * Post code (zip code) level accuracy.
   * @var integer
   */
  const ACCURACY_POSTCODE = 5;

  /**
   * Street level accuracy.
   * @var integer
   */
  const ACCURACY_STREET = 6;

  /**
   * Intersection level accuracy.
   * @var integer
   */
  const ACCURACY_INTERSECTION = 7;

  /**
   * Address level accuracy.
   * @var integer
   */
  const ACCURACY_ADDRESS = 8;

  /**
   * Premise (building name, property name, shopping center, etc.) level accuracy.
   * @var integer
   */
  const ACCURACY_PREMISE = 9;

  private  $addressString;
  private  $vendorObj;
  private  $response;
  private  $curl;
  private  $curlClass;
  private  $apiKey;
  private  $region;
  private  $bounds;
  protected  $longitude;
  protected  $latitude;
  protected  $accuracy;

  /**
   * Is false if address changes, is true after a call to google
   * This is so we don't make a request unnecessarily
   *
   * @var boolean
   */
  private  $settingsChanged = false;

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
    $this->addressString = $address;
    $this->settingsChanged = true;
  }

  public function getAddress()
  {
    return $this->addressString;
  }

  /**
   * Set the API key
   *
   * @param string $apiKey String
   */
  final public function setCurlClass( $className )
  {
    $this->curlClass = $className;
    $this->settingsChanged = true;
  }

  /**
   * Set the API key
   *
   * @param string $apiKey String
   */
  final public function setApiKey( $apiKey )
  {
    $this->apiKey = $apiKey;
    $this->settingsChanged = true;
  }

  public function getApiKey()
  {
    return $this->apiKey;
  }

  /**
   * Set the region
   *
   * @param string $region String
   */
  final public function setRegion( $region )
  {
    $this->region = $region;
    $this->settingsChanged = true;
  }

  public function getRegion()
  {
    return $this->region;
  }

  /**
   * Set the bounds
   *
   * @param string $bounds String
   */
  final public function setBounds( $bounds )
  {
    $this->bounds = $bounds;
    $this->settingsChanged = true;
  }

  public function getBounds()
  {
    return $this->bounds;
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
    if( !$this->settingsChanged )
      return $this;

    $this->setUpCurl();
    $this->curl->exec();
    $this->response = $this->curl->getResponse();
    $this->processResponse( $this->response );

    file_put_contents( sfConfig::get( 'sf_log_dir' ) . '/GoogleApiUsage.log', date( 'Y-m-d H:i:s' ) . PHP_EOL, FILE_APPEND );

    $this->settingsChanged = false;
    return $this;
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

  public function getRawResponse()
  {
      return $this->response;
  }

  /**
   * Get the longitude fo rthe address
   */
  public function getLongitude()
  {
    $this->getGeoCode();
    return $this->longitude;
  }


  /**
   * Get the latitude of the address
   */
  public function getLatitude()
  {
    $this->getGeoCode();
    return $this->latitude;
  }


  /**
   * Get the accuracy of the geocode lookup
   *
   * @return int (null if no results)
   */
  public function getAccuracy()
  {
    $this->getGeoCode();
    return $this->accuracy;
  }

  final protected function getAddressString()
  {
    return $this->addressString;
  }


  abstract public function getLookupUrl();

  abstract protected function apiKeyIsValid( $apiKey );

  abstract protected function processResponse( $response );

}
