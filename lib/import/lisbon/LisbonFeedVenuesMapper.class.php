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
class LisbonFeedVenuesMapper extends LisbonFeedBaseMapper
{
  
  public function mapVenues()
  {
    foreach( $this->xml->venues as $venueElement )
    {
      $poi = new Poi();
      $this->mapAvailableData($poi, $venueElement );
      
      $poi['review_date'] = '';
      $poi['local_language'] = 'PTR';
      $poi['city'] = 'Lisbon';
      $poi['district'] = '';
      $poi['country'] = 'Portugal';
      $poi['additional_address_details'] = $this->extractAddress( $venueElement );
      $poi['phone'] = '';
      $poi['phone2'] = '';
      $poi['fax'] = '';
      $poi['vendor_category'] = '';
      $poi['keywords'] = '';
      $poi['short_description'] = '';
      $poi['description'] = '';
      $poi['public_transport_links'] = $this->extractTransportLinkInfo( $venueElement );
      $poi['price_information'] = '';
      $poi['openingtimes'] = '';
      $poi['star_rating'] = '';
      $poi['rating'] = '';
      $poi['provider'] = '';
      $poi['vendor_id'] = $this->vendor['id'];

      $this->geoEncoder->setAddress( $this->getGeoEncodeData( $poi ) );
      $poi['longitude'] = $this->geoEncoder->getLongitude();
      $poi['latitude'] = $this->geoEncoder->getLatitude();
      
      $this->notifyImporter( $poi );
    }
  }

  /**
   * Return an array of mappings from xml attributes to record fields
   *
   * @return array
   */
  protected function getMap()
  {
    return array(
      'placeid'    => 'vendor_poi_id',
      'name'       => 'poi_name',
      'address'    => 'street',
      'postcode'   => 'zips',
      'genmail'    => 'email',
      'url'        => 'url',
      'buildingno' => 'house_no',
    );
  }

  /**
   * Return an array of attributes to ignore when mapping
   *
   * @return array
   */
  protected function getIgnoreMap()
  {
    return array(
      'tubeinfo',
      'businfo',
      'railinfo',
      'additional_address_details',
      'address1',
      'address2',
      'address3',
      'address4',
    );
  }

  private function extractTransportLinkInfo( SimpleXMLElement $venueElement )
  {
    $infoArray = array();

    if( !empty( $venueElement['tubeinfo'] ) )
    {
      $infoArray[] = 'Tube: ' . $venueElement['tubeinfo'];
    }

    if( !empty( $venueElement['businfo'] ) )
    {
      $infoArray[] = 'Bus: ' . $venueElement['businfo'];
    }

    if( !empty( $venueElement['railinfo'] ) )
    {
      $infoArray[] = 'Rail: ' . $venueElement['railinfo'];
    }

    return implode( ', ', $infoArray );
  }

  private function extractAddress( $venueElement )
  {
    $addressArray = array();

    if( !empty( $venueElement['address'] ) )
    {
      $addressArray[] = $venueElement['address'];
    }

    if( !empty( $venueElement['address1'] ) )
    {
      $addressArray[] = $venueElement['address1'];
    }

    if( !empty( $venueElement['address2'] ) )
    {
      $addressArray[] = $venueElement['address2'];
    }

    if( !empty( $venueElement['address3'] ) )
    {
      $addressArray[] = $venueElement['address3'];
    }

    if( !empty( $venueElement['address4'] ) )
    {
      $addressArray[] = $venueElement['address4'];
    }

    return implode( ', ', $addressArray );
  }

  /**
   * return address for geoEncoder
   */
  private function getGeoEncodeData( $poi )
  {
    $addressData = array
    (
      $poi['house_no'],
      $poi['street'],
      $poi['zips'],
      $poi['additional_address_details'],
    );
    return stringTransform::concatNonBlankStrings(', ', $addressData );
  }
}
?>
