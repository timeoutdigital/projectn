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
      $poi = new Poi();
      $poi['vendor_poi_id'] = $venueElement['placeid'];
      $poi['review_date'] = '';
      $poi['local_language'] = 'PTR';
      $poi['poi_name'] = $venueElement['name'];
      $poi['house_no'] = '';
      $poi['street'] = $venueElement['address'];
      $poi['city'] = 'Lisbon';
      $poi['district'] = '';
      $poi['country'] = 'Portugal';
      $poi['additional_address_details'] = '';
      $poi['zips'] = $venueElement['postcode'];
      $poi['country_code'] = '';
      $poi['extension'] = '';
      $poi['longitude'] = 0;
      $poi['latitude'] = 0;
      $poi['email'] = $venueElement['genemail'];
      $poi['url'] = $venueElement['url'];
      $poi['phone'] = '';
      $poi['phone2'] = '';
      $poi['fax'] = '';
      $poi['vendor_category'] = '';
      $poi['keywords'] = '';
      $poi['short_description'] = '';
      $poi['description'] = '';
      $poi['public_transport_links'] = $this->extractTransportLinkInfo($venueElement);
      $poi['price_information'] = '';
      $poi['openingtimes'] = '';
      $poi['star_rating'] = '';
      $poi['rating'] = '';
      $poi['provider'] = '';
      $poi['vendor_id'] = $this->vendor['id'];
      $this->notifyImporter($poi);
      $poi->free(true);
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
