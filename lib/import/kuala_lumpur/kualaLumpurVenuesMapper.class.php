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
      $poi['poi_name'] = (string) $venue->title;
      $poi['vendor_poi_id'] = (string) $venue->id;
      $poi['street'] = (string) $venue->address;
      $poi['city'] = 'Kuala Lumpur';
      $poi['country'] = 'MYS';
      $poi[ 'geocode_look_up' ] = $this->extractGeoLookup( $venue );
      $poi[ 'Vendor' ] = $this->vendor;
      $this->notifyImporter( $poi );
    }
  }

  private function extractGeoLookup( SimpleXMLElement $venue )
  {
    return 'foo';
  }
}
