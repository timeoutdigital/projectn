<?php
/**
 * Description
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
class LondonAPIBarsAndPubsMapper extends LondonAPIBaseMapper
{
  /**
   * Map restaurant data to Poi and notify the Importer as each Poi is mapped
   */
  public function mapPoi()
  {
    $this->apiCrawler->crawlApi();
  }

  /**
   * Returns the London API URL
   *
   * @return string
   */
  public function getDetailsUrl()
  {
    return 'http://api.timeout.com/v1/getBar.xml';
  }

  /**
   * Returns the API type
   *
   * See London's API Word doc by Rhodri Davis
   *
   * @return string
   */
  public function getApiType()
  {
    return 'Bars & Pubs';
  }

  /**
   * Map $barsXml into a Poi object and pass to Importer
   *
   * @param SimpleXMLElement $barsXml
   */
  public function doMapping( SimpleXMLElement $barsXml )
  {
    $poi = new Poi();

    $latLong = $this->deriveLatitudeLongitude( $barsXml );

    $poi['longitude']         = $latLong['latitude'];
    $poi['latitude']          = $latLong['longitude'];
    $poi['zips']              = (string) $barsXml->postcode;
    $poi['city']              = $this->deriveCity( $barsXml, $latLong['latitude'], $latLong['longitude'] );

    $poi['vendor_id']         = $this->vendor['id'];
    $poi['vendor_poi_id']     = (string) $barsXml->uid;
    $poi['street']            = (string) $barsXml->address;
    $poi['country']           = $this->country;
    $poi['poi_name']          = (string) $barsXml->name;
    $poi['url']               = (string) $barsXml->webUrl;
    $poi['phone']             = (string) $barsXml->phone;
    $poi['price_information'] = (string) $barsXml->price;
    $poi['openingtimes']      = (string) $barsXml->openingTimes;
    $poi['public_transport_links'] = (string) $barsXml->travelInfo;
    $poi['star_rating']       = (int) $barsXml->starRating;
    $poi['description']       = (string) $barsXml->description;

    foreach( $barsXml->details as $detail )
    {
      $poi->addProperty( (string) $detail['name'], (string) $detail );
    }

    $this->notifyImporter( $poi );
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
}
?>
