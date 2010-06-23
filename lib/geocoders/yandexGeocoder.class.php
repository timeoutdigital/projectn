<?php
/**
 * Gets geo data using Yandex.
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
 *
 *  $geoEncode = new yandexGeocoder();
 *  $geoEncode->setApiKey( $apiKey );
 *  $geoEncode->setAddress( $addressString );
 *
 *  //Set longitude and latitude
 *  $poiObj[ 'longitude' ] = $geoEncode->getLongitude();
 *  $poiObj[ 'latitude' ]  = $geoEncode->getLatitude();
 * </code>
 *
 * Yandex Documentation
 * @link http://code.google.com/apis/maps/documentation/services.html#Geocoding
 * @link http://code.google.com/apis/maps/documentation/reference.html#GGeoStatusCode.G_GEO_SUCCESS
 * 
 */
class yandexGeocoder extends geoEncode
{
  /**
   * An associative array that maps ymapml accuracy values to our accuracy values
   * Accuracy values taken from geocode plugin from London
   *
   * @var array
   */
  static private $accuracyMap = array(
                             'exact'  => self::ACCURACY_PREMISE,
                             'street' => self::ACCURACY_STREET,
                             'near'   => self::ACCURACY_COUNTRY,
                           );

  public function __construct( $curlClass='Curl' )
  {
    parent::__construct( $curlClass );
  }

  public function getLookupUrl()
  {
    $query = http_build_query( array(
                                  'geocode' => $this->getAddress(),
                                  'key'     => $this->getApiKey(),
                              ) );

    $url = 'http://geocode-maps.yandex.ru/1.x/?' . $query;
    return $url;
  }

  protected function apiKeyIsValid( $apiKey )
  {
    return (!is_string( $apiKey ) || strlen( $apiKey ) != 89);
  }

  protected function processResponse( $response )
  {
    $xml = simplexml_load_string( $response );
    $geoObject = $xml->GeoObjectCollection
                     ->featureMember[0]
                     ->GeoObject
                     ;

    //Handle LatLong
    $latLongString = $geoObject->Point
                               ->pos
                               ;
    $latLongArray = split( ' ', $latLongString );

    $this->latitude  = $latLongArray[ 1 ];
    $this->longitude = $latLongArray[ 0 ];

    //Handle accuracy
    $accuracy = (string) $geoObject->metaDataProperty
                                   ->GeocoderMetaData
                                   ->precision
                                   ;
    $this->accuracy = self::$accuracyMap[ $accuracy ];
  }
}
