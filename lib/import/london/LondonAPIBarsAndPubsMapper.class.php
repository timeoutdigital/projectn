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
}
?>
