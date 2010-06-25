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
    for( $i=0, $venueElement = $this->xml->venue[ 0 ]; $i<$this->xml->venue->count(); $i++, $venueElement = $this->xml->venue[ $i ] )
    {
        try {
            // Get Venue Id
            $vendor_venue_id = (string) $venueElement['id'];
            
            $poi = Doctrine::getTable( 'Poi' )->findByVendorPoiIdAndVendorLanguage( $vendor_venue_id, 'ru' );
            if( !$poi )
              $poi = new Poi();

            $geocoder = new yandexGeocoder();
            $geocoder->setApiKey( 'ABIbCUwBAAAAQ2mQUwIA1oFXn_CffhQeYwZpC0CqL97RDwgAAAAAAAAAAAAu2D1hnUJ_hl3vURvlovEOBDueTQ==' );
            $poi->setGeoEncoder( $geocoder );

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
            $poi['phone']                         = trim( (string) $venueElement->phone, " ." );
            $poi['phone2']                        = trim( (string) $venueElement->phone2, " ." );
            $poi['fax']                           = (string) $venueElement->fax;
            $poi['keywords']                      = (string) $venueElement->keywords;
            $poi['description']                   = $this->fixHtmlEntities( (string) $venueElement->description );
            $poi['short_description']             = $this->fixHtmlEntities( (string) $venueElement->short_description );
            $poi['public_transport_links']        = (string) $venueElement->public_transport;
            $poi['price_information']             = (string) $venueElement->price_information;
            $poi['openingtimes']                  = (string) $venueElement->opening_times;
            $poi['star_rating']                   = $this->roundNumberOrReturnNull( (string) $venueElement->star_rating );
            $poi['rating']                        = $this->roundNumberOrReturnNull( (string) $venueElement->rating );
            $poi['provider']                      = (string) $venueElement->provider;
            $poi['geocode_look_up']               = stringTransform::concatNonBlankStrings(', ', array( $poi['house_no'], $poi['street'], $poi['zips'], $poi['city'] ) );
            $poi['Vendor']                        = clone $this->vendor;

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

            $this->notifyImporter( $poi );
        }
        catch( Exception $exception )
        {
            $this->notifyImporterOfFailure( $exception );
        }
        
        unset( $poi, $venueElement, $vendor_venue_id, $categories, $processed_medias );
    }
  }
}
?>
