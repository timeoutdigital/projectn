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
class LondonAPIBarsAndPubsMapper extends LondonAPIBaseMapper
{
  /**
   * Map restaurant data to Poi and notify the Importer as each Poi is mapped
   */
  public function mapPoi()
  {
    $this->crawlApiForType( 'Bars & Pubs' );
  }

  /**
   * Returns the London API URL
   *
   * @return string
   */
  protected function getDetailsUrl()
  {
    return 'http://api.timeout.com/v1/getBar.xml';
  }

  /**
   * Map $restaurantXml into a Poi object and pass to Importer
   *
   * @param SimpleXMLElement $barsXml
   */
  protected function doMapping( SimpleXMLElement $barsXml )
  {
    $poi = new Poi();
    $poi['vendor_id']         = $this->vendor['id'];
    $poi['vendor_poi_id']     = (string) $barsXml->uid;
    $poi['street']            = (string) $barsXml->address;
    $poi['city']              = $this->city;
    $poi['country']           = $this->country;
    $poi['poi_name']          = (string) $barsXml->name;
    $poi['url']               = (string) $barsXml->webUrl;
    $poi['phone']             = (string) $barsXml->phone;
    $poi['zips']              = (string) $barsXml->postcode;
    $poi['price_information'] = (string) $barsXml->price;
    $poi['openingtimes']      = (string) $barsXml->openingTimes;
    $poi['public_transport_links'] = (string) $barsXml->travelInfo;
    $poi['star_rating']       = (int) $barsXml->starRating;
    $poi['description']       = (string) $barsXml->description;

    $this->geoEncoder->setAddress( $barsXml->venueAddress );

    $poi['longitude'] = $this->geoEncoder->getLongitude();
    $poi['latitude'] = $this->geoEncoder->getLatitude();

    foreach( $barsXml->details as $detail )
    {
      $poi->addProperty( (string) $detail['name'], (string) $detail );
    }

    $this->notifyImporter( $poi );
  }
}
?>
