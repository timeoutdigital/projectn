<?php
/**
 * 
 *
 * @package projectn
 * @subpackage london.import.lib
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
abstract class LondonAPIBaseMapper extends DataMapper
{

  /**
   * @var Vendor
   */
  protected $vendor;
//
//  /**
//   * @var string
//   */
//  protected $searchUrl = 'http://api.timeout.com/v1/search.xml';

  /**
   * @var string
   */
  protected $city = 'London';

  /**
   * @var string
   */
  protected $country = 'GBR';

  /**
   * @var geoEncode
   */
  protected $geoEncoder;

  /**
   * @var int
   */
  protected $limit = 0;

  /**
   * @var LondonAPICrawler
   */
  protected $apiCrawler;
  
  /**
   * @param LondonAPICrawler $apiCrawler
   * @param geoEncode $geoEncoder
   */
  public function  __construct( LondonAPICrawler $apiCrawler=null, geoEncode $geoEncoder = null )
  {
    $this->vendor = Doctrine::getTable('Vendor')
      ->findOneByCityAndLanguage( 'london', 'en-GB' );

    if( is_null( $apiCrawler ) )
    {
      $apiCrawler = new LondonAPICrawler();
    }

    $apiCrawler->setMapper( $this );
    $this->apiCrawler = $apiCrawler;
    $this->geoEncoder = $geoEncoder;

    if( is_null( $geoEncoder ) )
    {
      $this->geoEncoder = new geoEncode();
    }
  }
  
  /**
   * Limit the number of results to map
   * Set to zero (0) for no limit
   *
   * @param int $limit
   */
  public function setLimit( $limit )
  {
    $this->limit = $limit;
  }

  /**
   * Get the current result limit
   *
   * @return int
   */
  public function getLimit()
  {
    return $this->limit;
  }

  protected function crawlApi()
  {
    $this->apiCrawler->crawlApi();
  }

  /**
   * Use data from xml to derive the longitude and latitude
   *
   * @returns array
   */
  protected function deriveLatitudeLongitude( $detailsXml )
  {
    $latitude  = $detailsXml[ 'latitude' ];
    $longitude = $detailsXml[ 'longitude' ];

    if( empty( $latitude ) || empty( $longitude ) )
    {
      $this->geoEncoder->setAddress( $detailsXml->postcode );
      $latitude  = $this->geoEncoder->getLatitude();
      $longitude = $this->geoEncoder->getLongitude();
    }

    $latLong = array(
      'latitude'  => $latitude,
      'longitude' => $longitude,
    );

    return $latLong;
  }

  /**
   * Uses data from xml to derive the value for city
   *
   * @return string
   */
  protected function deriveCity( $postcode, $latitude, $longitude )
  {
    $city = 'London';

    if( !$this->validateLondon( $postcode, $latitude, $longitude ) )
    {
      $address = $this->getAddressUsingGeocode($latitude, $longitude);
      $city = $address['AdministrativeArea'];
    }

    return $city;
  }

  /**
   * Look up an address using latitude and longitude
   *$latitude
   * @return arrayAdministrativeArea
   */
  protected function getAddressUsingGeocode( $latitude, $longitude )
  {
    $reverseGeocoder = new reverseGeocode($latitude, $longitude);
    $addressesXml = $reverseGeocoder->getAddressesXml();

    $addressesXml->registerXPathNamespace( 'g', 'http://earth.google.com/kml/2.0' );
    $addressesXml->registerXPathNamespace( 'o', 'urn:oasis:names:tc:ciq:xsdschema:xAL:2.0' );

    $firstAddressXml = $addressesXml->xpath( '/g:kml/g:Response/g:Placemark[1]/o:AddressDetails' );
    $firstAddressXml = $firstAddressXml[0];

    //var_dump( $firstAddressXml ); exit();

    $firstAddressDetails =  array
      (
      'AdministrativeArea'    => $this->extractAdministrativeAreaName( $firstAddressXml ),
      'SubAdministrativeArea' => $this->extractSubAdministrativeAreaName( $firstAddressXml ),
    );
    //var_dump( $latitude . ', ' . $longitude );// exit();

    return $firstAddressDetails;
  }

  protected function extractAdministrativeAreaName( $firstAddressXml )
  {
    return (string) $firstAddressXml->Country
      ->AdministrativeArea
      ->AdministrativeAreaName;
  }

  protected function extractSubAdministrativeAreaName( $firstAddressXml )
  {
    return (string) $firstAddressXml->Country
      ->AdministrativeArea
      ->SubAdministrativeArea
      ->SubAdministrativeAreaName;
  }

  /**
   * Checks location is in London using postcode, latitude, longitude
   *
   * Function taken from london
   *
   * @return boolean
   */
  protected function validateLondon( $postcode, $latitude, $longitude )
  {
    if ( preg_match( '/^[NESW]{0,2}[0-9]+.*/', $postcode ) )
    {
      return true;
    }
    else
    {
      //distance in miles from center point
      $centerPoint = round( sqrt( pow( (69.1 * ( $latitude - 51.515927 ) ), 2) + pow((53 * ( $longitude - -0.129917 ) ), 2 ) ), 1);

      if ( $centerPoint < 50 )
      {
        return true;
      }
      else
      {
        return false;
      }
    }
  }

  /**
   * Return the URL for get the details of an API result row.
   *
   * For example, restaurant subclass would be implemented as:
   *
   * <code>
   * protected function getDetailsUrl()
   * {
   *   return 'http://api.timeout.com/v1/getRestaurant.xml'
   * }
   * </code>
   *
   * @returns string
   */
  abstract public function getDetailsUrl();

  /**
   * Return the API type
   * e.g. Restaurants, Bar & Pubs, Cinemas ...
   *
   * See London's API Word doc by Rhodri Davis
   *
   * @return string
   */
  abstract public function getApiType();

  /**
   * Do mapping of xml to poi and notify Importer here
   */
  abstract public function doMapping( SimpleXMLElement $xml );
}
?>
