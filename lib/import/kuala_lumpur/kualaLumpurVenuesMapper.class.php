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
        try
        {
          $poi = $this->dataMapperHelper->getPoiRecord( (string) $venue->id );

          $poi['vendor_poi_id']       = (string) $venue->id;
          $poi['poi_name']            = (string) $venue->title;
          $poi['street']              = (string) $venue->location->street;
          $poi['city']                = 'Kuala Lumpur';
          $poi['country']             = 'MYS';

          // NOTE. The element is erroneously called 'longlat' but data is provided as 'lat,long'
          $latlong  = (string) $venue->location->longlat;

          if( !empty( $latlong ) )
          {
            $latlong = explode( ',', $latlong );
            $poi->applyFeedGeoCodesIfValid( $latlong[0], $latlong[1] ) ;
          }
          
          $poi[ 'geocode_look_up' ]   = stringTransform::concatNonBlankStrings( ", ", array(
                                            (string) $venue->location->lot,
                                            (string) $venue->location->street,
                                            (string) $venue->location->zipcode,
                                            (string) $venue->location->state ));

          $poi[ 'email' ]             = (string) $venue->contact_details->email;
          $poi[ 'url' ]               = (string) $venue->url;
          $poi[ 'phone' ]             = (string) $venue->contact_details->tel_no;
          $poi[ 'short_description' ] = (string) $venue->short_description;
          $poi[ 'description' ]       = (string) $venue->description;
          $poi[ 'Vendor' ]            = $this->vendor;

          // Add vendor POi Category
          $category     = array();
          $category[]   = (string) $venue->categories->category;
          $category[]   = (string) $venue->categories->subCategory;

          $poi->addVendorCategory( $category, $this->vendor['id'] );

          try {
            $poi->addMediaByUrl( (string) $venue->medias->big_image );
          }
          catch( Exception $exception )
          {
            $this->notifyImporterOfFailure( $exception );
          }

          $this->notifyImporter( $poi );

        }
        catch( Exception $exception )
        {
            $this->notifyImporterOfFailure( $exception, $poi );
        }
    }
  }

}
