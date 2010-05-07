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

      $poi['vendor_poi_id']       = (string) $venue->id;
      $poi['poi_name']            = (string) $venue->title;
      $poi['street']              = (string) $venue->address;
      $poi['city']                = 'Kuala Lumpur';
      $poi['country']             = 'MYS';
      $poi[ 'geocode_look_up' ]   = (string) $venue->address;
      $poi[ 'latitude' ]          = $this->extractLatitude( $venue );
      $poi[ 'longitude' ]         = $this->extractLongitude( $venue );
      $poi[ 'email' ]             = $venue->contact_details->email;
      $poi[ 'url' ]               = $venue->url;
      $poi[ 'phone' ]             = $venue->contact_details->tel_no;
      $poi[ 'short_description' ] = $venue->short_description;
      $poi[ 'description' ]       = $venue->description;
      $poi[ 'Vendor' ]            = $this->vendor;

      $poi->addVendorCategory( array(
        $venue->categories->category,
        $venue->categories->subCategory,
      ), 
      $this->vendor );

      //$poi->addMediaByUrl( (string) $venue->medias->big_image );

      $this->notifyImporter( $poi );
    }
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