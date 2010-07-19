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

          $longlat  = (string) $venue->location->longlat;

          if( !empty( $longlat  ) )
          {
            $longlat = explode( ',', $longlat );

            $poi->applyFeedGeoCodesIfValid( $longlat[1], $longlat[0] ) ;
          }
          $poi[ 'geocode_look_up' ]    = (string) $venue->location->lot .' ';
          $poi[ 'geocode_look_up' ]   .= (string) $venue->location->street .' ';
          $poi[ 'geocode_look_up' ]   .= (string) $venue->location->zipcode .' ';
          $poi[ 'geocode_look_up' ]   .= (string) $venue->location->state  ;

          $poi[ 'email' ]             = (string) $venue->contact_details->email;
          $poi[ 'url' ]               = (string) $venue->url;
          $poi[ 'phone' ]             = (string) $venue->contact_details->tel_no;
          $poi[ 'short_description' ] = (string) $venue->short_description;
          $poi[ 'description' ]       = (string) $venue->description;
          $poi[ 'Vendor' ]            = $this->vendor;


          $cat  = (string) $venue->categories->category;
          $cat2 = (string) $venue->categories->subCategory;

          if( !empty( $cat ) && !empty( $cat2 ) )
          {
                $poi->addVendorCategory( array( $cat, $cat2 ), $this->vendor['id'] );
          }

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
