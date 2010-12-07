<?php
/**
 * Sydney venues mapper
 *
 * @package projectn
 * @subpackage sydney.import.lib.unit
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @author Rajeevan Kumarathasan <rajeevankumarathasan.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class australiaVenuesMapper extends australiaBaseMapper
{
  public function mapVenues()
  {
    foreach( $this->feed->venue as $venue )
    {
      // get Existing POI or create NEW
      $vendor_poi_id = (string) $venue->VenueID;
      $poi = Doctrine::getTable( 'Poi' )->findOneByvendorIdAndVendorPoiId( $this->vendor['id'], $vendor_poi_id );
      if( $poi === false )
      {
          $poi = new Poi();
      }

      // Map Data
      $poi['Vendor']            = $this->vendor;
      $poi['vendor_poi_id']     = $vendor_poi_id;

      switch( $this->vendor['city'] )
      {
        /* Melbourne GeoCodes are Reversed. */
        case 'melbourne' : $poi->applyFeedGeoCodesIfValid( (float) $venue->Longitude, (float) $venue->Latitude ); break;
        default : $poi->applyFeedGeoCodesIfValid( (float) $venue->Latitude, (float) $venue->Longitude );
      }

      $poi['poi_name']          = (string) $venue->Name;
      $poi['street']            = (string) $venue->Address;
      $poi['city']              = ucfirst( (string) $this->vendor['city'] );
      $poi['zips']              = (string) $venue->PostCode;
      $poi['country']           = (string) 'AUS';
      $poi['geocode_look_up']   = (string) $this->extractGeocodeLookUp( $venue );
      $poi['description']       = (string) $venue->Description;
      $poi['phone']             = (string) $venue->Phone;
      $poi['url']               = (string) $venue->Website;
      $poi['price_information'] = (string) stringTransform::formatPriceRange( (int) $venue->PriceFrom, (int) $venue->PriceTo, '$' );
      $poi['openingtimes']      = (string) $venue->OpenTimes;
      $poi['star_rating']       = $this->extractRating( $venue );
      $poi['review_date']       = (string) $this->extractDate( (string) $venue->DateUpdated );

      //#753 addImageHelper capture Exception and notify, this don't break the Import process
      $this->addImageHelper( $poi, (string) $venue->ImagePath );
      
      $cats = $this->extractVendorCategories( $venue );
      if( count( $cats ) )
      {
        $poi->addVendorCategory( $cats, $this->vendor['id'] );
      }

      $poi['TimeoutLinkProperty'] = (string) $venue->TimeoutURL;

      if ( (string) $venue->Recommended == 'Recommended')
        $poi['RecommendedProperty'] = true;
      else if ( (string) $venue->Recommended == 'Critics Choice')
        $poi['CriticsChoiceProperty'] = true;

      $this->notifyImporter( $poi );
    }
  }

  private function extractRating( $venue )
  {
    $rating = (string) $venue->Rating;

    if( empty( $rating ) || $rating == 0 )
      $rating = null;

    if( $rating > 5 )
      $rating = 5;

    return $rating;
  }

  private function extractVendorCategories( SimpleXMLElement $venue )
  {
    $vendorCats = array();

    $parentCategory = (string) $venue->categories->parent_category_name;

    if( !empty( $parentCategory ) && $parentCategory != 'N/A' )
      $vendorCats[] = $parentCategory;

    if( isset( $venue->categories->childrens ) )
    {
        foreach( $venue->categories->childrens->children_category as $childCategory )
          if( $childCategory != 'N/A' )
            $vendorCats[] = (string) $childCategory;
    }

    return $vendorCats;
  }

  private function extractGeocodeLookUp( SimpleXMLElement $venue )
  {
    $fields = array(
      (string) $venue->Name,
      (string) $venue->Address,
      (string) $venue->Suburb,
      (string) $venue->PostCode,
      'AUS',
    );

    return stringTransform::concatNonBlankStrings( ', ', $fields );
  }
}
