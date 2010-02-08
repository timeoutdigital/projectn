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
   * @var int
   */
  private $pageLimit;

  /**
   * @var curlImporter
   */
  private $curl;

  /**
   * @param string $url
   * @param geoEncode $geoEncoder
   */
  public function  __construct( geoEncode $geoEncoder = null )
  {
    $this->vendor = Doctrine::getTable('Vendor')->findOneByCityAndLanguage( 'london', 'en-GB' );
    $this->geoEncoder = $geoEncoder;
    $this->curl = new curlImporter();

    if( is_null( $geoEncoder ) )
    {
      $this->geoEncoder = new geoEncode();
    }
  }

  public function setPageLimit( $limit )
  {
    $this->pageLimit = $limit;
  }

  public function mapPoi()
  {
    $searchXml = $this->searchApi();

    $numPerPage = $searchXml->responseHeader->rows;
    $numResults = $searchXml->responseHeader->numFound;

    if( $this->pageLimit )
      $numResults = $this->pageLimit * $numPerPage;

    for( $offset = 0; $offset < $numResults; $offset += $numPerPage )
    {
      $searchPageXml = $this->searchApi( $offset );

      foreach( $searchPageXml->response->block->row as $row )
      {
        $xml = $this->getRestaurant( $row->uid );
        $this->doMapping( $xml->response->row );
      }
    }
  }
  
  private function searchApi( $offset = 0 )
  {
    $this->curl->pullXml( $this->searchUrl, '', array( 'type' => 'Restaurants', 'offset' => $offset ) );
    $xml = $this->curl->getXml();
    return $xml;
  }

  private function getRestaurant( $uid )
  {
    $this->curl->pullXml( $this->singleRestaurantUrl, '', array( 'uid' => $uid ) );
    return $this->curl->getXML();
  }

  private function doMapping( $restaurantXml )
  {
    $poi = new Poi();
    $poi['vendor_id']         = $this->vendor['id'];
    $poi['vendor_poi_id']     = (string) $restaurantXml->uid;
    $poi['street']            = $restaurantXml->address;
    $poi['city']              = 'London';
    $poi['country']           = 'GBR';
    $poi['poi_name']          = $restaurantXml->name;
    $poi['url']               = $restaurantXml->webUrl;
    $poi['phone']             = $restaurantXml->phone;
    $poi['zips']              = $restaurantXml->postcode;
    $poi['price_information'] = $restaurantXml->price;
    $poi['openingtimes']      = $restaurantXml->openingTimes;
    $poi['public_transport_links'] = $restaurantXml->travelInfo;
    $poi['star_rating']       = $restaurantXml->starRating;
    $poi['description']       = $restaurantXml->description;

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
