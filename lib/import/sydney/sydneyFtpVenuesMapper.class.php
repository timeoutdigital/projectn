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

      $poi['Vendor']            = $this->vendor;
      $poi['vendor_poi_id']     = (string) $venue->VenueID;
      $poi['latitude']          = (float)  $venue->Latitude;
      $poi['longitude']         = (float)  $venue->Longitude;
      $poi['poi_name']          = (string) $venue->Name;
      $poi['street']            = (string) $venue->Address;
      $poi['city']              = (string) $this->vendor['city'];
      $poi['zips']              = (string) $venue->PostCode;
      $poi['country']           = (string) 'AUS';
      $poi['geocode_look_up']   = (string) $this->extractGeocodeLookUp( $venue );
      $poi['description']       = (string) $venue->Description;
      $poi['phone']             = (string) $venue->Phone;
      $poi['url']               = (string) $venue->Website;
      $poi['price_information'] = (string) stringTransform::formatPriceRange( (int) $venue->PriceFrom, (int) $venue->PriceTo );
      $poi['openingtimes']      = (string) $venue->OpenTimes;
      $poi['star_rating']       = (string) $venue->Rating;
      $poi['review_date']       = (string) $this->extractDate( $venue->DateUpdated );

      $poi->addMediaByUrl(     (string) $venue->ImagePath );
      $poi->addVendorCategory( $this->extractVendorCategories( $venue ), $this->vendor );

      $poi['TimeoutLinkProperty'] = (string) $venue->TimeoutURL;

      if ( (string) $venue->Recommended == 'Recommended')
        $poi['RecommendedProperty'] = true;
      else if ( (string) $venue->Recommended == 'Critics Choice')
        $poi['CriticsChoiceProperty'] = true;

      $this->notifyImporter( $poi );
    }
  }

  private function extractDate( $dateString )
  {
    if ( empty( $dateString ) )
      return;

    // swap 29/03/2010 9:59:00 AM  to   03/29/2010 9:59:00 AM
    $dateString = preg_replace( '/([0-9]+)\/([0-9]+)\/([0-9]{4} [0-9]+\:[0-9]{2}\:[0-9]{2} [AMP]{2})/', '$2/$1/$3', $dateString );

    $date = new DateTime( $dateString );
    return $date->format( 'Y-m-d H:i:s' );
  }

  private function extractVendorCategories( SimpleXMLElement $venue )
  {
    $vendorCats = array();

    $parentCategory = (string) $venue->categories->parent_category_name;

    if( !empty( $parentCategory ) && $parentCategory != 'N/A' )
      $vendorCats[] = $parentCategory;

    foreach( $venue->categories->childrens->children_category as $childCategory )
      $vendorCats[] = (string) $childCategory;

    return $vendorCats;
  }

  private function extractGeocodeLookUp( SimpleXMLElement $venue )
  {
    $fields = array(
      $venue->Name,
      $venue->Address,
      $venue->Suburb,
      $venue->PostCode,
      'AUS',
    );

    return stringTransform::concatNonBlankStrings( ', ', $fields );
  }
}
