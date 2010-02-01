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
class LisbonFeedVenuesMapper extends DataMapper
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
      $data = array();
      $data['vendor_poi_id'] = $venueElement['placeid'];
      $data['review_date'] = '';
      $data['local_language'] = 'PTR';
      $data['poi_name'] = $venueElement['name'];
      $data['house_no'] = '';
      $data['street'] = $venueElement['address'];
      $data['city'] = 'Lisbon';
      $data['district'] = '';
      $data['country'] = 'Portugal';
      $data['additional_address_details'] = '';
      $data['zips'] = $venueElement['postcode'];
      $data['country_code'] = '';
      $data['extension'] = '';
      $data['longitude'] = 0;
      $data['latitude'] = 0;
      $data['email'] = $venueElement['genemail'];
      $data['url'] = $venueElement['url'];
      $data['phone'] = '';
      $data['phone2'] = '';
      $data['fax'] = '';
      $data['vendor_category'] = '';
      $data['keywords'] = '';
      $data['short_description'] = '';
      $data['description'] = '';
      $data['public_transport_links'] = $this->extractTransportLinkInfo($venueElement);
      $data['price_information'] = '';
      $data['openingtimes'] = '';
      $data['star_rating'] = '';
      $data['rating'] = '';
      $data['provider'] = '';
      $data['vendor_id'] = $this->vendor['id'];
      
      $this->notifyImporter( new RecordData( 'Poi', $data ) );
    }
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
}
?>
