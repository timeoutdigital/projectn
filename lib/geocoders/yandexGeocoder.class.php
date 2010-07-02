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

  /**
   *
   * Example xml if no results found by Yandex
   *
   * <ymaps xmlns="http://maps.yandex.ru/ymaps/1.x" xmlns:x="http://www.yandex.ru/xscript">
   *   <GeoObjectCollection>
   *     <metaDataProperty xmlns="http://www.opengis.net/gml">
   *       <GeocoderResponseMetaData xmlns="http://maps.yandex.ru/geocoder/1.x">
   *         <request>272 6409, ул. Кабанбай батыра, 79., Алматы</request>
   *         <found>0</found>
   *         <results>10</results>
   *       </GeocoderResponseMetaData>
   *     </metaDataProperty>
   *   </GeoObjectCollection>
   * </ymaps>
   *
   *
   * Example xml if results found by Yandex
   *
   * <ymaps xmlns="http://maps.yandex.ru/ymaps/1.x" xmlns:x="http://www.yandex.ru/xscript">
   * <GeoObjectCollection>
   *   <metaDataProperty xmlns="http://www.opengis.net/gml">
   *     <GeocoderResponseMetaData xmlns="http://maps.yandex.ru/geocoder/1.x">
   *       <request>ул. Есенберлина, 191., Алматы</request>
   *       <found>1</found>
   *       <results>10</results>
   *     </GeocoderResponseMetaData>
   *     </metaDataProperty>
   *     <featureMember xmlns="http://www.opengis.net/gml">
   *       <GeoObject xmlns="http://maps.yandex.ru/ymaps/1.x">
   *         <metaDataProperty xmlns="http://www.opengis.net/gml">
   *           <GeocoderMetaData xmlns="http://maps.yandex.ru/geocoder/1.x">
   *             <kind>house</kind>
   *             <text>Казахстан, Алматы, улица Есенберлина, 191</text>
   *             <precision>exact</precision>
   *             <AddressDetails xmlns="urn:oasis:names:tc:ciq:xsdschema:xAL:2.0">
   *               <Country>
   *                 <CountryName>Казахстан</CountryName>
   *                 <Locality>
   *                   <LocalityName>Алматы</LocalityName>
   *                   <Thoroughfare>
   *                     <ThoroughfareName>улица Есенберлина</ThoroughfareName>
   *                     <Premise>
   *                       <PremiseNumber>191</PremiseNumber>
   *                     </Premise>
   *                   </Thoroughfare>
   *                 </Locality>
   *               </Country>
   *             </AddressDetails>
   *           </GeocoderMetaData>
   *         </metaDataProperty>
   *        <boundedBy xmlns="http://www.opengis.net/gml">
   *          <Envelope>
   *            <lowerCorner>76.967680 43.259884</lowerCorner>
   *            <upperCorner>76.978640 43.265884</upperCorner>
   *          </Envelope>
   *        </boundedBy>
   *        <Point xmlns="http://www.opengis.net/gml">
   *          <pos>76.973160 43.262884</pos>
   *        </Point>
   *        </GeoObject>
   *      </featureMember>
   *   </GeoObjectCollection>
   * </ymaps>
   */
  protected function processResponse( $response )
  {
    $this->accuracy = 0;

    $xml = simplexml_load_string( $response );

    $numFound = $xml->GeoObjectCollection
                    ->metaDataProperty
                    ->GeocoderResponseMetaData
                    ->found;

    if( $numFound == 0 )
      return;

    $geoObject = $xml->GeoObjectCollection
                     ->featureMember[0]
                     ->GeoObject
                     ;

    //Handle LatLong
    $latLongString = $geoObject->Point
                               ->pos
                               ;
    $latLongArray = explode( ' ', $latLongString );

    $this->latitude  = $latLongArray[ 1 ];
    $this->longitude = $latLongArray[ 0 ];

    //Handle accuracy
    $accuracy = (string) $geoObject->metaDataProperty
                                   ->GeocoderMetaData
                                   ->precision
                                   ;
    if( key_exists( $accuracy, self::$accuracyMap ) )
      $this->accuracy = self::$accuracyMap[ $accuracy ];
    else
      $this->accuracy = self::ACCURACY_UNKNOWN;
  }
}
