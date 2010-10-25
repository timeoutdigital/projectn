<?php
/**
 * London API Base Mapper
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

  /**
   * @var string
   */
  protected $searchUrl = 'http://api.timeout.com/v1/search.xml';

  /**
   * @var string
   */
  protected $city = 'London';

  /**
   * @var string
   */
  protected $country = 'GBR';

  /**
   * @var int
   */
  protected $limit = 0;

  /**
   * @var LondonAPICrawler
   */
  protected $apiCrawler;

  /**
   * @var projectnDataMapperHelper
   */
  protected $dataMapperHelper;

  /**
   * @param LondonAPICrawler $apiCrawler
   * @param geocoder $geocoderr
   */
  public function  __construct( Doctrine_Record $vendor, $params )
  {
    $this->vendor = $vendor;

    if( is_null( $this->apiCrawler ) )
    {
      $this->apiCrawler = new LondonAPICrawler();
    }
    $this->apiCrawler->setMapper( $this );

    $this->dataMapperHelper = new projectNDataMapperHelper($this->vendor);
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

  /**
   * @todo Can this be removed? Why is it Public?
   */
  public function onException( Exception $exception, $message = null )
  {
    $this->notifyImporterOfFailure( $exception, null, $message );
  }

  protected function crawlApi()
  {
    $this->apiCrawler->crawlApi();
  }

  protected function addDetailAsProperty( $record, SimpleXMLElement $detail )
  {
    if( (string) $detail == 'Critics\' choice' || (string) $detail == 'Critic\'s choice' )
    {
      $record['CriticsChoiceProperty'] = true;
      return;
    }

    $record->addProperty( (string) $detail['name'], (string) $detail );
  }

  /**
   * do common API-POI mappings
   */
  protected function mapCommonPoiMappings(Poi $poi, SimpleXMLElement $xml )
  {
    $poi->applyFeedGeoCodesIfValid( (string) $xml->lat, (string) $xml->lng );

    $poi->lookupAndApplyGeocodes(); //Needed for Derive City Below

    $poi['zips']              = (string) $xml->postcode;
    $poi['city']              = $this->deriveCity( $poi['latitude'], $poi['longitude'], $xml, $poi );

    $poi['Vendor']            = clone $this->vendor;
    $poi['vendor_poi_id']     = (string) $xml->uid;
    //$poi['vendor_category']    = $this->getApiType();
    $poi->addVendorCategory( $this->getApiType(), $this->vendor['id'] );

    $fix = new removeCommaLondonFromEndOfString( (string) $xml->address );
    $poi['street']            = $fix->getFixedString();
    $poi['street']            = trim( $poi['street'], ", " );

    $poi['country']           = $this->country;
    $poi['poi_name']          = (string) $xml->name;
    $poi['url']               = (string) $xml->url;
    if( (string) $xml->webUrl != "" )
    {
        $poi->addProperty( "Timeout_link", (string) $xml->webUrl );
    }

    $poi['phone']             = (string) $xml->phone;
    $poi['price_information'] = (string) $xml->price;
    $poi['openingtimes']      = (string) $xml->openingTimes;
    $poi['public_transport_links'] = (string) $xml->travelInfo;
    $poi['description']       = (string) $xml->description;

    $geocoderLookUpString = stringTransform::concatNonBlankStrings( ', ', array( $poi['poi_name'], $poi['street'] , $poi['city'] , $poi['zips'], "UK" ) );

    $poi->setgeocoderLookUpString( $geocoderLookUpString );
  }

  /**
   * Uses data from xml to derive the value for city
   *
   * @return string
   */
  protected function deriveCity( $latitude, $longitude, $xml, $poi )
  {
    $city = 'London';

    if( !$this->validateLondon( $xml->postcode, $latitude, $longitude ) )
    {
      $city = $this->extractCityFromAddress( $xml->address );

      if( empty( $city ) )
      {
        $address = $this->getAddressUsingGeocode( $latitude, $longitude );
        $city = $address['SubAdministrativeArea'];
        $this->notifyImporterOfFailure( new Exception("Warning: Reverse Geocode Lookup Used on London POI, does this happen often?") );
      }
    }

    // Refs: #412, We seem to be getting postcodes on the end of the address string, where we used to get the city name.
    if( preg_match( "/[0-9]/", $city ) )
        throw new LondonAPIBaseMapperException("City Name Should Not Contain a Number. Recieved city name: '$city'.");

    return $city;
  }

  /**
   * attempt to get city from address string
   *
   * @return string
   */
  protected function extractCityFromAddress( $addressString )
  {
    $city = '';
    $addressPieces = explode( ',', $addressString );

    if( count( $addressPieces ) > 1 )
    {
      $city = array_pop( $addressPieces );
    }

    return trim( $city );
  }

  /**
   * Look up an address using latitude and longitude
   *
   * @param float $latitude
   * @param float $longitude
   *
   * @return array AdministrativeArea
   */
  protected function getAddressUsingGeocode( $latitude, $longitude )
  {
    $reverseGeocoder = new reverseGeocode($latitude, $longitude, 'uk');
    $addressesXml = $reverseGeocoder->getAddressesXml();

    $firstAddressXml = $this->extractFirstAddress( $addressesXml );

    $firstAddressDetails =  array
    (
      'AdministrativeArea'    => $this->extractAdministrativeAreaName( $firstAddressXml ),
      'SubAdministrativeArea' => $this->extractSubAdministrativeAreaName( $firstAddressXml ),
    );

    return $firstAddressDetails;
  }

  protected function extractFirstAddress( $xml )
  {
    $firstAddressXml = $xml->xpath( '/g:kml/g:Response/g:Placemark[1]/o:AddressDetails' );
    $firstAddressXml = $firstAddressXml[0];

    return $firstAddressXml;
  }

  protected function extractAdministrativeAreaName( $firstAddressXml )
  {
    $administrativeAreaName = '';

    try
    {
      $administrativeAreaName = (string) $firstAddressXml->Country
        ->AdministrativeArea
        ->AdministrativeAreaName;
    }
    catch( Exception $exception ){}

    return $administrativeAreaName;
  }

  protected function extractSubAdministrativeAreaName( $firstAddressXml )
  {
    $subAdministrativeAreaName = '';

    try
    {
      $subAdministrativeAreaName = (string) @$firstAddressXml->Country
        ->AdministrativeArea
        ->SubAdministrativeArea
        ->SubAdministrativeAreaName;
    }
    catch( Exception $exception ){}

    return $subAdministrativeAreaName;
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
    if ( preg_match( '/^[NESW][A-Z]?[0-9]+.*/', $postcode ) )
    {
      return true;
    }
    else
    {
      return false;
//      //distance in miles from center point
//      $centerPoint = round( sqrt( pow( (69.1 * ( $latitude - 51.515927 ) ), 2) + pow((53 * ( $longitude - -0.129917 ) ), 2 ) ), 1);
//
//      if ( $centerPoint < 50 )
//      {
//        return true;
//      }
//      else
//      {
//        return false;
//      }
    }
  }

  protected function getDetails( $xml )
  {
    return $xml->details->detail;
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

  /**
   * Re-write media url, to get best quality image available.
   * (instead of iphone size that gets served by default)
   * eg. http://toimg.net/managed/images/bounded/5168/w300/h317/i.jpg
   * becomes http://toimg.net/managed/images/5168/i.jpg
   */
  protected function rewriteMediaUrlToRemoveScaling( $url = "" )
  {
    $boundedString = "bounded/";
    $findBoundsStringEndPosition = strpos( $url, $boundedString ) + strlen( $boundedString );

    $baseUrl = substr( $url, 0, $findBoundsStringEndPosition - strlen( $boundedString ) );
    $mediaId = substr( $url, $findBoundsStringEndPosition, strpos( $url, "/", $findBoundsStringEndPosition ) - $findBoundsStringEndPosition );

    if( $mediaId == 0 )
    {
        $message = "London API returning media id of 0. url: '" . $url . "'";
        $this->onException( new Exception( $message ), $message );
        return false;
    }
    return( $baseUrl . $mediaId . "/i.jpg" );
  }
}

class LondonAPIBaseMapperException extends Exception {}
?>
