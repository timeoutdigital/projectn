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
 *  $geocoder = new googleGeocoder();
 *  $geocoder->setAddress( $addressString );
 *
 *  //Set longitude and latitude
 *  $poiObj[ 'longitude' ] = $geocoder->getLongitude();
 *  $poiObj[ 'latitude' ]  = $geocoder->getLatitude();
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
class googleGeocoder extends geocoder
{

  public function getLookupUrl()
  {
    $params = array();
    $params[ 'q' ] = $this->getAddress();
    $params[ 'output' ] = 'csv';
    $params[ 'oe' ] = 'utf8';
    $params[ 'sensor' ] = 'false';

    $apiKey = $this->getApiKey();
    if( $this->apiKeyIsValid( $apiKey ) ) $apiKey = sfConfig::get('app_google_api_key');
    $params[ 'key' ] = $apiKey;

    if( $this->getRegion() )
      $params[ 'region' ] = $this->getRegion();

    if( $this->getBounds() )
      $params[ 'bounds' ] = $this->getBounds();

    $query = http_build_query( $params );

    $url = "http://maps.google.com/maps/geo?" . $query;


     return $url;
  }

  protected function apiKeyIsValid( $apiKey )
  {
    return (!is_string( $apiKey ) || strlen( $apiKey ) != 86);
  }

  protected function responseIsValid()
  {
    if( !is_string( $this->response ) || empty( $this->response ) || stripos( $this->response, ',' ) === false )
        return false;

    $dataArray = explode( ',', $this->response );
    if( count( $dataArray ) !== 4 ) return false;

    $responseCode = $dataArray[0];
    return ( $responseCode == 200 );
  }

  protected function processResponse( $response )
  {
     $dataArray = explode(',', $response);

     switch($dataArray[0])
     {
         case '602': //throw new GeoCodeException('G_GEO_UNKNOWN_ADDRESS');
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

}
