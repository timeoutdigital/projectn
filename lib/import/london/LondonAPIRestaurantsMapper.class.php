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
class LondonAPIRestaurantsMapper extends LondonAPIBaseMapper
{

  /**
   * Map restaurant data to Poi and notify the Importer as each Poi is mapped
   */
  public function mapPoi()
  {
    $this->crawlApi();
  }
  
  /**
   * Returns the London API URL
   * 
   * @return string
   */
  public function getDetailsUrl()
  {
    return 'http://api.timeout.com/v1/getRestaurant.xml';
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
    return 'Restaurants';
  }

  /**
   * Map $restaurantXml into a Poi object and pass to Importer
   *
   * @param SimpleXMLElement $restaurantXml
   */
  public function doMapping( SimpleXMLElement $restaurantXml )
  {
    $poi = new Poi();
    $poi['vendor_id']         = $this->vendor['id'];
    $poi['vendor_poi_id']     = (string) $restaurantXml->uid;
    $poi['street']            = (string) $restaurantXml->address;
    $poi['city']              = $this->city;
    $poi['country']           = $this->country;
    $poi['poi_name']          = (string) $restaurantXml->name;
    $poi['url']               = (string) $restaurantXml->webUrl;
    $poi['phone']             = (string) $restaurantXml->phone;
    $poi['zips']              = (string) $restaurantXml->postcode;
    $poi['price_information'] = (string) $restaurantXml->price;
    $poi['openingtimes']      = (string) $restaurantXml->openingTimes;
    $poi['public_transport_links'] = (string) $restaurantXml->travelInfo;
    $poi['star_rating']       = (int) $restaurantXml->starRating;
    $poi['description']       = (string) $restaurantXml->description;

    $this->geoEncoder->setAddress( $restaurantXml->venueAddress );

    $poi['longitude'] = $this->geoEncoder->getLongitude();
    $poi['latitude'] = $this->geoEncoder->getLatitude();

    foreach( $restaurantXml->details as $detail )
    {
      $poi->addProperty( (string) $detail['name'], (string) $detail );
    }

    $this->notifyImporter( $poi );
  }
}
?>
