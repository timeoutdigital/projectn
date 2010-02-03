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
      $this->mapAvailableData($poi, $venueElement, $propertiesKey);
      
      $poi['review_date'] = '';
      $poi['local_language'] = 'PTR';
      $poi['city'] = 'Lisbon';
      $poi['district'] = '';
      $poi['country'] = 'Portugal';
      $poi['additional_address_details'] = $this->extractAddress( $venueElement );;
      $poi['longitude'] = 0;
      $poi['latitude'] = 0;
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
      'genemail'   => 'email',
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
}
?>
