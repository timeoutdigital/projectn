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
      $poi = $this->dataMapperHelper->getPoiRecord($venueElement['placeid']);
      $this->mapAvailableData($poi, $venueElement );

      $poi['review_date'] = '';
      $poi['local_language'] = 'pt';
      $poi['city'] = 'Lisbon';
      $poi['country'] = 'PTR';
      $poi['additional_address_details'] = $this->extractAddress( $venueElement );
      $poi['phone'] =  (string) $venueElement[ 'phone' ];
      $poi->addVendorCategory( (string) $venueElement[ 'tipo' ], $this->vendor['id'] );
      $poi['public_transport_links'] = $this->extractTransportLinkInfo( $venueElement );
      $poi['vendor_id'] = $this->vendor['id'];
      
      $poi['description']                = $this->extractAnnotation( $venueElement );
      $poi['additional_address_details'] = $this->extractAddress( $venueElement );
      $poi['phone2']                     = $this->extractPhoneNumbers( $venueElement );
      $poi['public_transport_links']     = $this->extractTransportLinkInfo( $venueElement );
      $poi['price_information']          = $this->extractPriceInfo( $venueElement );
      $poi['openingtimes']               = $this->extractTimeInfo( $venueElement );
      $poi['house_no']                   = $this->extractHouseNumberAndName( $venueElement );

      $poi->setGeoEncodeLookUpString ( $this->getGeoEncodeData( $poi ) );
 
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
      'placeid'      => 'vendor_poi_id',
      'name'         => 'poi_name',
      'address'      => 'street',
      'postcode'     => 'zips',  
      'genmail'      => 'email',
      'url'          => 'url',
      'tipo'         => 'vendor_category',
      'abbreviation' => 'short_description',
      'phone'        => 'phone',
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
      'cinemapriceinfo',
      'MusicpriceInfo',
      'dancepriceexport',
      'comedypriceexport',
      'nightlifepriceexport',
      'gaypriceexport',
      'comedytimesexport',
      'dancetimesexport',
      'nightlifetimesexport',
      'gaytimesexport',
      'gayannotation',
      'danceannotation',
      'nightlifeannotation',
      'comedyannotation',
      'comedytelexport',
      'dancetelexport',
      'nightlifetelexport',
      'gaytelexport',
      'buildingno',
      'buildingName',
      'area',
      'city',
      'cinemaheaderinfo',
      'FilmDoubleIssueText',
      'MusicheaderInfo',
      'tubestationid',
      'sortfield',
      'comedyurlexport',
      'danceurlexport',
      'nightlifeurlexport',
      'gayurlexport',
      'placelist',
    );
  }

  /**
   * Extract annotation from xml
   *
   * @param SimpleXMLElement $venueElement
   * @return string
   */
  private function extractAnnotation( $venueElement )
  {
    $annotationArray= array
    (
      $venueElement['gayannotation'],
      $venueElement['danceannotation'],
      $venueElement['nightlifeannotation'],
      $venueElement['comedyannotation'],
    );
    return stringTransform::concatNonBlankStrings(', ', $annotationArray );
  }

  /**
   * Extract price info from xml
   *
   * @param SimpleXMLElement $venueElement
   * @return string
   */
  private function extractPriceInfo( SimpleXMLElement $venueElement )
  {
    $priceArray = array
    (
      $venueElement['cinemapriceinfo'],
      $venueElement['MusicpriceInfo'],
      $venueElement['dancepriceexport'],
      $venueElement['comedypriceexport'],
      $venueElement['nightlifepriceexport'],
      $venueElement['gaypriceexport'],
    );
    return stringTransform::concatNonBlankStrings(', ', $priceArray );
  }

  /**
   * Extract time info from xml
   *
   * @param SimpleXMLElement $venueElement
   * @return string
   */
  private function extractTimeInfo( SimpleXMLElement $venueElement )
  {
    $timeArray = array
    (
      $venueElement['comedytimesexport'],
      $venueElement['dancetimesexport'],
      $venueElement['nightlifetimesexport'],
      $venueElement['gaytimesexport'],
    );
    return stringTransform::concatNonBlankStrings(', ', $timeArray );
  }

  /**
   * Extract transport info from xml
   *
   * @param SimpleXMLElement $venueElement
   * @return string
   */
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

  /**
   * Extract house number and building name from xml
   *
   * @param SimpleXMLElement $venueElement
   * @return string
   */
  private function extractHouseNumberAndName( $venueElement )
  {
    $houseArray = array
    (
      $venueElement['buildingno'],
      $venueElement['buildingName'],
    );

    return stringTransform::concatNonBlankStrings(' ', $houseArray );
  }

  /**
   * Extract address from xml
   *
   * @param SimpleXMLElement $venueElement
   * @return string
   */
  private function extractAddress( $venueElement )
  {
    $addressArray = array
    (
      $venueElement['address'],
      $venueElement['address1'],
      $venueElement['address2'],
      $venueElement['address3'],
      $venueElement['address4'],
      $venueElement['area'],
    );

    return stringTransform::concatNonBlankStrings(', ', $addressArray );
  }

  /**
   * Extract phone numbers from xml
   *
   * @param SimpleXMLElement $venueElement
   * @return string
   */
  private function extractPhoneNumbers( $venueElement )
  {
    $phoneArray = array
    (
      $venueElement['comedytelexport'],
      $venueElement['dancetelexport'],
      $venueElement['nightlifetelexport'],
      $venueElement['gaytelexport'],
    );

    return stringTransform::concatNonBlankStrings(', ', $phoneArray );
  }

  /**
   * return address for geoEncoder
   *
   * @param Poi $poi
   * @return string
   */
  private function getGeoEncodeData( $poi )
  {
    $addressData = array
    (
      $poi['house_no'],
      $poi['street'],      
    );

    return stringTransform::concatNonBlankStrings(', ', $addressData  ) . ', lisbon portugal';
  }
}
?>
