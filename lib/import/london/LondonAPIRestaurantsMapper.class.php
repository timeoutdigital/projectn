<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class LondonAPIRestaurantsMapper extends DataMapper
{
  /**
   * @var string
   */
  private $searchUrl = 'http://api.timeout.com/v1/search.xml';

  /**
   * @var string
   */
  private $singleRestaurantUrl = 'http://api.timeout.com/v1/getRestaurant.xml';

  /**
   * @var geoEncode
   */
  private $geoEncoder;

  /**
   * @var Vendor
   */
  private $vendor;

  /**
   * @param string $url
   * @param geoEncode $geoEncoder
   */
  public function  __construct( geoEncode $geoEncoder = null )
  {
    $this->vendor = Doctrine::getTable('Vendor')->findOneByCityAndLanguage( 'london', 'en-GB' );
    $this->geoEncoder = $geoEncoder;

    if( is_null( $geoEncoder ) )
    {
      $this->geoEncoder = new geoEncode();
    }
  }

  public function mapPoi()
  {
    foreach( $this->getRestaurants() as $restaurant )
    {
      $poi = new Poi();
      $poi['vendor_id']         = $this->vendor['id'];
      $poi['vendor_poi_id']     = (string) $restaurant->uid;
      $poi['street']            = $restaurant->address;
      $poi['city']              = 'London';
      $poi['country']           = 'GBR';
      $poi['poi_name']          = $restaurant->name;
      $poi['url']               = $restaurant->webUrl;
      $poi['phone']             = $restaurant->phone;
      $poi['zips']              = $restaurant->postcode;
      $poi['price_information'] = $restaurant->price;
      $poi['openingtimes']      = $restaurant->openingTimes;
      $poi['public_transport_links'] = $restaurant->travelInfo;
      $poi['star_rating']       = $restaurant->starRating;
      $poi['description']       = $restaurant->description;

      $this->geoEncoder->setAddress( $restaurant->venueAddress );
      
      $poi['longitude'] = $this->geoEncoder->getLongitude();
      $poi['latitude'] = $this->geoEncoder->getLatitude();

      foreach( $restaurant->details as $detail )
      {
        $poi->addProperty( (string) $detail['name'], (string) $detail );
      }

      $this->notifyImporter( $poi );
    }
  }

  private function getRestaurants()
  {
    $searchXML         = $this->searchRestaurants();
    $restaurantDetails = $this->accumulateRestaurantDetails( $searchXML );
    return $restaurantDetails;
  }
  
  private function searchRestaurants()
  {
    $curl = new curlImporter();
    $curl->pullXml( $this->searchUrl, '', array( 'q' => 'restaurant' ) );
    $xml = $curl->getXml();
    return $xml;
  }
  
  private function accumulateRestaurantDetails( SimpleXMLElement $xml )
  {
    $restaurantDetailsXML = array();
    $curl = new curlImporter();

    foreach( $xml->response->block->row as $row )
    {
      $curl->pullXml($this->singleRestaurantUrl, '', array( 'uid' => $row->uid ) );
      $xml = $curl->getXML();
      $restaurantDetailsXML[] = $xml->response->row;
    }
    return $restaurantDetailsXML;
  }
}
?>
