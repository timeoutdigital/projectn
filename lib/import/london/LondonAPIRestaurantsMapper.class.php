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
class LondonAPIRestaurantsMapper extends LondonAPIBaseMapper
{

  /**
   * @var string
   */
  private $singleRestaurantUrl = 'http://api.timeout.com/v1/getRestaurant.xml';

  /**
   * Map restaurant data to Poi and notify the Importer as each Poi is mapped
   */
  public function mapPoi()
  {
    $searchXml = $this->callApiSearch( array( 'type' => 'Restaurants', 'offset' => 0 ) );

    $numPerPage = $searchXml->responseHeader->rows;
    $numResults = $searchXml->responseHeader->numFound;

    $numResultsMapped = 0;

    for( $offset = 0; $offset < $numResults; $offset += $numPerPage )
    {
      $searchPageXml = $this->callApiSearch( array( 'type' => 'Restaurants', 'offset' => $offset ) );

      foreach( $searchPageXml->response->block->row as $row )
      {
        $xml = $this->callApiGetDetails( $row->uid );
        $this->doMapping( $xml->response->row );
        
        if( !$this->inLimit( ++$numResultsMapped ) ) return;
      }
    }
  }
  
  protected function getDetailsUrl()
  {
    return $this->singleRestaurantUrl;
  }

  /**
   * Map $restaurantXml into a Poi object and pass to Importer
   *
   * @param SimpleXMLElement $restaurantXml
   */
  private function doMapping( $restaurantXml )
  {
    $poi = new Poi();
    $poi['vendor_id']         = $this->vendor['id'];
    $poi['vendor_poi_id']     = (string) $restaurantXml->uid;
    $poi['street']            = (string) $restaurantXml->address;
    $poi['city']              = 'London';
    $poi['country']           = 'GBR';
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
