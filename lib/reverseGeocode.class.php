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
   * @param string $countryBiasccTLD a ccTLD string
   */
  public function __construct( $latitude, $longitude, $countryBiasccTLD = null )
  {
    $this->longitude = $longitude;
    $this->latitude = $latitude;
    
    $params = array(
        'q' => $latitude . ',' . $longitude,
        'output' => 'xml',
        'sensor' => 'false',
        'key' => $this->apiKey,
      );

    if( $countryBiasccTLD )
    {
      $params['gl'] = $countryBiasccTLD;
    }

    $this->params = $params;
  }

  /**
   * set a different Google maps API key
   * 
   * @param string $apiKey
   */
  public function setApiKey( $apiKey )
  {
    $this->apiKey = $apiKey;
  }

  /**
   * Get addresses found for the latitude longitude provided
   *
   * @return SimpleXMLElement
   */
  public function getAddressesXml()
  {
    if( is_null( $this->addressesXml ) )
    {
      $this->callApi();
    }
    return $this->addressesXml;
  }

  /**
   * Call the Google Maps API for data
   */
  protected function callApi()
  {
    $curl = new Curl( $this->url, $this->params );
    
    $responseXml = simplexml_load_string( $curl->getResponse() );

    $this->registerNameSpaces( $responseXml );
    $this->ensureSuccessStatus( $responseXml );
    $this->addressesXml = $responseXml;
    
  }

  /**
   * Register namespaces in response xml
   *
   * @param SimpleXMLElement $responseXml
   */
  protected function registerNameSpaces( SimpleXMLElement $responseXml )
  {
    $responseXml->registerXPathNamespace( 'g', 'http://earth.google.com/kml/2.0' );
    $responseXml->registerXPathNamespace( 'o', 'urn:oasis:names:tc:ciq:xsdschema:xAL:2.0' );
  }

  /**
   * Check the status code and throw an Exception if status code is not 200 (ok)
   *
   * @param SimpleXMLElement $responseXml
   */
  protected function ensureSuccessStatus( SimpleXMLElement $responseXml )
  {
    $statusCodeTags = $responseXml->xpath('/g:kml/g:Response/g:Status/g:code');
    $statusCode = (int) $statusCodeTags[0];

    $codeNames = array(
      200 =>	'G_GEO_SUCCESS',
      500 =>	'G_GEO_SERVER_ERROR',
      601 =>	'G_GEO_MISSING_QUERY',
      602 =>	'G_GEO_UNKNOWN_ADDRESS',
      603 =>	'G_GEO_UNAVAILABLE_ADDRESS',
      610 =>	'G_GEO_BAD_KEY',
      620 =>	'G_GEO_TOO_MANY_QUERIES',
    );

    if( $statusCode != 200 )
    {
      throw new Exception( 
        'Google Maps failed with code: ' .
        $statusCode .
        ' ( '. $codeNames[ $statusCode ] .' )'
      );
    }
  }
}
?>
