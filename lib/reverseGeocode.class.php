<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class reverseGeocode
{
  /**
   * GoogleMaps API key
   * @todo put the key in one place, currently in geoEncode class as well
   *
   * @var string
   */
  private  $apiKey = 'ABQIAAAA_FL2s5FUf4LMO_mUYDXUABSveoBGowI7nqh6kHEuOnD_thDOzhQRISkLudF55CGQjHRYPqfn429wzw';

  /**
   * GoogleMaps Url
   *
   * @var string
   */
  private  $url = 'http://maps.google.com/maps/geo';
  
  /**
   * @var float
   */
  private $longitude;
  
  /**
   * @var float
   */
  private $latitude;

  /**
   * @var SimpleXMLElement
   */
  private $addressesXml;

  /**
   * 
   * @param float $longitude
   * @param float $latitude
   */
  public function __construct( $latitude, $longitude )
  {
    $this->longitude = $longitude;
    $this->latitude = $latitude;

    $curl = new Curl(
      $this->url,
      array(
        'q' => $latitude . ',' . $longitude,
        'output' => 'xml',
        'sensor' => 'false',
        'key' => $this->apiKey,
      )
    );
    
    $this->addressesXml = simplexml_load_string( $curl->getResponse() );
  }

  /**
   * Get addresses found for the latitude longitude provided
   *
   * @return SimpleXMLElement
   */
  public function getAddressesXml()
  {
    return $this->addressesXml;
  }
}
?>
