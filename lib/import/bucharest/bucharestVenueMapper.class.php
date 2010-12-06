<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class bucharestVenueMapper extends DataMapper
{
      /**
    * @var geoEncode
    */
    protected $geoEncoder;

    /**
    * @var Vendor
    */
    protected $vendor;

    /**
    * @var SimpleXMLElement
    */
    protected $xml;

    /**
    *
    * @param SimpleXMLElement $xml
    * @param geoEncode $geoEncoder
    */
  public function __construct( SimpleXMLElement $xml, geocoder $geoEncoder = null )
  {        
      $this->vendor     = Doctrine::getTable( 'Vendor' )->findOneByCityAndLanguage( 'bucharest', 'ro' );
      
      $this->geoEncoder = is_null( $geoEncoder ) ? new googleGeocoder() : $geoEncoder;
      $this->xml        = $xml;
  }

  public function mapVenues()
  {
    for( $i=0, $venueElement = $this->xml->venue[ 0 ]; $i<$this->xml->venue->count(); $i++, $venueElement = $this->xml->venue[ $i ] )
    {
        $poi = Doctrine::getTable( 'poi' )->findOneByVendorIdAndVendorPoiId( $this->vendor['id'], $this->clean( (string) $venueElement['id'] ) );
        if( $poi === false )
        {
          $poi = new Poi();
        }
        try
        {
            $poi['vendor_poi_id']                 = $this->clean( (string) $venueElement['id'] );
            $poi['local_language']                = $this->vendor['language'];
            $poi['poi_name']                      = $this->clean( (string) $venueElement->name );
            $poi['street']                        = $this->clean( (string) $venueElement->street );
            $poi['city']                          = ucfirst( $this->clean( (string) $this->vendor['city'] ) );
            $poi['country']                       = $this->clean( (string) $this->vendor['country_code_long'] );
            $poi['zips']                          = $this->clean( (string) $venueElement->postcode );
            $poi['email']                         = $this->clean( (string) $venueElement->email );
            $poi['phone']                         = $this->clean( (string) $venueElement->phone );
            $poi['public_transport_links']        = $this->clean( (string) $venueElement->public_transport );
            $poi['short_description']             = $this->clean( (string) $venueElement->short_description );
            $poi['description']                   = $this->clean( (string) $venueElement->description );
            $poi['keywords']                      = $this->clean( (string) $venueElement->keywords );
            $poi['Vendor']                        = clone $this->vendor;

            $poi['geocode_look_up']               = stringTransform::concatNonBlankStrings(', ', array( $poi['street'], $poi['zips'], $poi['city'] ) );
            //lat long inversed, told the vendor to sort it out
            $poi->applyFeedGeoCodesIfValid( $this->clean( (string) $venueElement->long ), $this->clean( (string) $venueElement->lat ) );

            // Categories
            $cats = $this->extractCategories( $venueElement );

            foreach( $cats as $cat )
            {
                $poi->addVendorCategory( $cat );
            }

            $this->notifyImporter( $poi );
        }
        catch( Exception $exception )
        {
            $this->notifyImporterOfFailure( $exception, $poi );
        }
    }
  }

  protected function extractCategories( $element )
  {
    // This is so complicated because it covers the rare event
    // where one parent category has multiple child categories.
    // @todo this function should be moved into a parent (base) class
    $categories = array();
    foreach( $element->categories->category as $category )
    {
        $categoryName = $this->clean( (string) $category->name );

        // Category has No Children
        if( count( $category->children->category ) === 0 )
        {
            $categories[] = $categoryName;
        }else
        {
            foreach( $category->children->category as $subCategory )
            {
            $categories[] = stringTransform::concatNonBlankStrings( " | ", array( $categoryName, $this->clean( (string) $subCategory->name ) ) );
            }
        }

    }
    return array_unique( $categories );
  }
}