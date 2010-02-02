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
  /**
   * @var SimpleXMLElement
   */
  private $xml;

  public function __construct( SimpleXMLElement $xml )
  {
    $vendor = Doctrine::getTable('Vendor')->findOneByCityAndLanguage( 'Lisbon', 'pt' );
    if( !$vendor )
    {
      throw new Exception( 'Vendor not found.' );
    }
    $this->vendor = $vendor;
    $this->xml = $xml;
  }
  
  public function mapVenues()
  {
    foreach( $this->xml->venues as $venueElement )
    {
      $poi = new Poi();
      $this->mapAvailableData($poi, $venueElement, $propertiesKey);
      
      $poi['review_date'] = '';
      $poi['local_language'] = 'PTR';
      $poi['house_no'] = $this->extractAddress( $venueElement );
      $poi['city'] = 'Lisbon';
      $poi['district'] = '';
      $poi['country'] = 'Portugal';
      $poi['additional_address_details'] = '';
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
      'placeid'  => 'vendor_poi_id',
      'name'     => 'poi_name',
      'address'  => 'street',
      'postcode' => 'zips',
      'genemail' => 'email',
      'url'      => 'url',
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

  private function extractAddress()
  {
    $addressArray = array();

    if( !empty( $venueElement['tubeinfo'] ) )
    {
      $infoArray[] = $venueElement['tubeinfo'];
    }

    if( !empty( $venueElement['businfo'] ) )
    {
      $infoArray[] = $venueElement['businfo'];
    }

    if( !empty( $venueElement['railinfo'] ) )
    {
      $infoArray[] = $venueElement['railinfo'];
    }

    return implode( ', ', $infoArray );
  }
}
?>
