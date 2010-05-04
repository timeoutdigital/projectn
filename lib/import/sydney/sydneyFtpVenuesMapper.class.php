<?php
/**
 * Sydney venues mapper
 *
 * @package projectn
 * @subpackage sydney.import.lib.unit
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class sydneyFtpVenuesMapper extends DataMapper
{
  /**
   * @var SimpleXMLElement
   */
  private $feed;

  /**
   * @var projectnDataMapperHelper
   */
  private $dataMapperHelper;

  /**
   * @var Vendor
   */
  private $vendor;

  /**
   * @param SimpleXMLElement $feed
   */
  public function __construct( Vendor $vendor, SimpleXMLElement $feed )
  {
    $this->feed = $feed;
    $this->vendor = $vendor;
    $this->dataMapperHelper = new projectnDataMapperHelper( $vendor );
  }

  public function mapVenues()
  {
    foreach( $this->feed->venue as $venue )
    {
      $poi = $this->dataMapperHelper->getPoiRecord( (string) $venue->VenueID );
      $poi['Vendor']          = $this->vendor;
      $poi['vendor_poi_id']   = (string) $venue->VendorID;
      $poi['latitude']        = (float)  $venue->Latitude;
      $poi['longitude']       = (float)  $venue->Longitude;
      $poi['street']          = (string) $venue->address;
      $poi['city']            = (string) $this->vendor['city'];
      $poi['country']         = (string) $this->vendor['country_code'];
      $poi['geocode_look_up'] = (string) $this->extractGeocodeLookUp( $venue );
      $poi->save();
    }
  }

  private function extractGeocodeLookUp( SimpleXMLElement $venue )
  {
    $fields = array(
      $venue->Name,
      $venue->address,
      $venue->suburb,
      $venue->postcode,
    );

    return stringTransform::concatNonBlankStrings( ', ', $fields );
  }
}
