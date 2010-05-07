<?php

class kualaLumpurVenuesMapper extends DataMapper
{
  /**
   * @var Vendor
   */
  private $vendor;

  /**
   * @var SimpleXMLElement
   */
  private $xml;

  public function __construct( Vendor $vendor, SimpleXMLElement $xml)
  {
    $this->vendor = $vendor;
    $this->xml = $xml;
    $this->dataMapperHelper = new ProjectNDataMapperHelper( $vendor );
  }

  public function mapVenues()
  {
    foreach( $this->xml->venueDetails as $venue )
    {
      $poi = $this->dataMapperHelper->getPoiRecord( (string) $venue->id );

      $poi['vendor_poi_id']     =  (string) $venue->id;
      $poi['poi_name']          =  (string) $venue->title;
      $poi['street']            =  (string) $venue->address;
      $poi['city']              =  'Kuala Lumpur';
      $poi['country']           =  'MYS';
      $poi[ 'geocode_look_up' ] =  $this->extractGeoLookup( $venue );
      $poi[ 'latitude' ]        =  $this->extractLatitude( $venue );
      $poi[ 'longitude' ]       =  $this->extractLongitude( $venue );
      $poi[ 'email' ]           =  $venue->contact_details->email;
      $poi[ 'url' ]             =  $venue->url;
      $poi[ 'phone' ]           =  $venue->contact_details->tel_no;
      $poi[ 'Vendor' ]          =  $this->vendor;

      $poi->addVendorCategory( array(
        $venue->categories->category,
        $venue->categories->subCategory,
      ), 
      $this->vendor );

      $this->notifyImporter( $poi );
    }
  }

  private function extractGeoLookup( SimpleXMLElement $venue )
  {
    return 'foo';
  }

  private function extractLatitude( SimpleXMLElement $venue )
  {
    $latlong = explode( ',', (string) $venue->location->longlat );
    return $latlong[1];
  }

  private function extractLongitude( SimpleXMLElement $venue )
  {
    $latlong = explode( ',', (string) $venue->location->longlat );
    return $latlong[0];
  }
}
