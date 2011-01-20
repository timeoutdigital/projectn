<?php

class kualaLumpurVenuesMapper extends kualaLumpurBaseMapper
{
 
  public function mapVenues()
  {

    foreach( $this->xmlNodes->venueDetails as $venue )
    {
        try
        {
          $poi = $this->dataMapperHelper->getPoiRecord( (string) $venue->id );

          $poi['vendor_poi_id']       = (string) $venue->id;
          $poi['poi_name']            = (string) $venue->title;
          $poi['street']              = (string) $venue->location->street;
          $poi['city']                = 'Kuala Lumpur';
          $poi['country']             = 'MYS';

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

          // NOTE. The element is erroneously called 'longlat' but data is provided as 'lat,long'
          $latlong  = (string) $venue->location->longlat;

          if( !empty( $latlong ) )
          {
              // #881 Catch Geocode out of vendor boundary error
              try{
                  $latlong = explode( ',', $latlong );
                  $poi->applyFeedGeoCodesIfValid( $latlong[0], $latlong[1] ) ;
              } catch ( Exception $exception ) {
                  $this->notifyImporterOfFailure( $exception, $poi );
              }
          }
          
            

          $this->notifyImporter( $poi );

        }
        catch( Exception $exception )
        {
            $this->notifyImporterOfFailure( $exception, isset( $poi ) ? $poi : null );
        }
    }
  }

}
