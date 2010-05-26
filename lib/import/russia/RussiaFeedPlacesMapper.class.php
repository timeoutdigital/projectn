<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage
 *
 * @author Peter Johnson <peterjohnson@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class RussiaFeedPlacesMapper extends RussiaFeedBaseMapper
{
  public function mapPlaces()
  {
    foreach( $this->xml->venue as $venueElement )
    {
        try {
            // Get Venue Id
            $vendor_venue_id = (string) $venueElement['id'];

            $poi = Doctrine::getTable( 'Poi' )->findByVendorPoiIdAndVendorLanguage( $vendor_venue_id, 'ru' );
            if( !$poi )
              $poi = new Poi();

            // Column Mapping
            $poi['vendor_poi_id']                 = (string) $vendor_venue_id;
            $poi['review_date']                   = (string) $venueElement->review_date;
            $poi['local_language']                = $this->vendor->language;
            $poi['poi_name']                      = trim( (string) $venueElement->name, " ./" );
            $poi['house_no']                      = (string) $venueElement->house_no;
            $poi['street']                        = (string) $venueElement->street;
            $poi['city']                          = (string) $venueElement->city;
            //$poi['district']                      = (string) $venueElement->district;
            $poi['country']                       = "RUS";
            $poi['additional_address_details']    = (string) $venueElement->additional_address_details;
            $poi['zips']                          = (string) $venueElement->postcode;
            $poi['longitude']                     = (string) $venueElement->long;
            $poi['latitude']                      = (string) $venueElement->lat;
            $poi['email']                         = (string) $venueElement->email;
            $poi['url']                           = (string) $venueElement->url;
            $poi['phone']                         = trim( (string) $venueElement->phone, " . " );
            $poi['phone2']                        = trim( (string) $venueElement->phone2, " . " );
            $poi['fax']                           = (string) $venueElement->fax;
            $poi['keywords']                      = (string) $venueElement->keywords;
            $poi['description']                   = $this->fixHtmlEntities( (string) $venueElement->description );
            $poi['short_description']             = $this->fixHtmlEntities( (string) $venueElement->short_description );
            $poi['public_transport_links']        = (string) $venueElement->public_transport;
            $poi['price_information']             = (string) $venueElement->price_information;
            $poi['openingtimes']                  = (string) $venueElement->opening_times;
            $starRating =  (string) $venueElement->star_rating;
            if ( !empty( $starRating ) ) 
                $poi['star_rating'] = round( $starRating );
            $rating =  (string) $venueElement->rating;
            if ( !empty( $rating ) ) 
                $poi['rating'] = round( $rating );
            $poi['provider']                      = (string) $venueElement->provider;
            $poi['geocode_look_up']               = stringTransform::concatNonBlankStrings(', ', array( $poi['house_no'], $poi['street'], $poi['zips'], $poi['city'] ) );
            $poi['Vendor']                        = $this->vendor;

            // Categories
            $categories = array();
            foreach( $venueElement->categories->category as $category ) $categories[] = (string) $category;
            $poi->addVendorCategory( $categories, $this->vendor->id );

            // Timeout Link
            if( (string) $venueElement->timeout_url != "" )
                $poi['TimeoutLinkProperty'] = (string) $venueElement->timeout_url;

            // Add Images
            $processed_medias = array();
            foreach( $venueElement->medias->media as $media )
            {
                $media_url = (string) $media;
                if( !in_array( $media_url, $processed_medias ) )
                    $this->addImageHelper( $poi, $media_url );
                $processed_medias[] = $media_url;
            }

            // Drop Accuracy, If Geo Not Set, Look Up
            $poi->setMinimumAccuracy( 6 );
            $poi->lookupAndApplyGeocodes();

            // List of City Centre Geo CoOrds
            $cityCentreGeoCoOrds = array();
            $cityCentreGeoCoOrds['tyumen']           = array( '57.1549492', '65.5156404' );
            $cityCentreGeoCoOrds['novosibirsk']      = array( '55.0392304', '82.9278181' );
            $cityCentreGeoCoOrds['krasnoyarsk']      = array( '56.0012512', '92.8855896' );
            $cityCentreGeoCoOrds['almaty']           = array( '43.2775',    '76.8958333' );
            $cityCentreGeoCoOrds['omsk']             = array( '54.9709016', '73.3937532' );

            // If Geo Still Not Set, use City Centre
            if( array_key_exists( $this->vendor->city, $cityCentreGeoCoOrds ) )
                if( !$poi['latitude'] || !$poi['longitude'] )
                {
                    $poi['latitude']  = $cityCentreGeoCoOrds[ $this->vendor->city ][ 0 ];
                    $poi['longitude'] = $cityCentreGeoCoOrds[ $this->vendor->city ][ 1 ];
                }

            
            $this->notifyImporter( $poi );
        }
        catch( Exception $exception )
        {
            $this->notifyImporterOfFailure( $exception );
        }
    }
  }
}
?>
