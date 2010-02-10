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
class LondonAPICinemasMapper extends LondonAPIBaseMapper
{
  /**
   * Map cinemas data to Poi and notify the Importer as each Poi is mapped
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
  protected function getDetailsUrl()
  {
    return 'http://api.timeout.com/v1/getCinema.xml';
  }

  /**
   * Returns the API type
   *
   * See London's API Word doc by Rhodri Davis
   *
   * @return string
   */
  protected function getApiType()
  {
    return 'Cinemas';
  }

  /**
   * Map $restaurantXml into a Poi object and pass to Importer
   *
   * @param SimpleXMLElement $cinemaXml
   */
  protected function doMapping( SimpleXMLElement $cinemaXml )
  {
    $poi = new Poi();
    $poi['vendor_id']         = $this->vendor['id'];
    $poi['vendor_poi_id']     = (string) $cinemaXml->uid;
    $poi['street']            = (string) $cinemaXml->address;
    $poi['city']              = $this->city;
    $poi['country']           = $this->country;
    $poi['poi_name']          = (string) $cinemaXml->name;
    $poi['url']               = (string) $cinemaXml->webUrl;
    $poi['phone']             = (string) $cinemaXml->phone;
    $poi['zips']              = (string) $cinemaXml->postcode;
    $poi['price_information'] = (string) $cinemaXml->price;
    $poi['openingtimes']      = (string) $cinemaXml->openingTimes;
    $poi['public_transport_links'] = (string) $cinemaXml->travelInfo;
    $poi['star_rating']       = (int) $cinemaXml->starRating;
    $poi['description']       = (string) $cinemaXml->description;

    $poi['longitude'] = (float) $cinemaXml->lng;
    $poi['latitude'] = (float) $cinemaXml->lat;

    //@todo add userRating

    $this->notifyImporter( $poi );
    $poi->free(true);
  }
}
?>
