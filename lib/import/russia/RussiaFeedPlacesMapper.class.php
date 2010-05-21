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
        // Get Venue Id
        foreach( $venueElement->attributes() as $k => $v )
            if( $k == "id" ) $vendor_venue_id = (int) $v;
            
        if( !isset( $vendor_venue_id ) || !is_numeric( $vendor_venue_id ) ) break;

        $poi = $this->dataMapperHelper->getPoiRecord( $vendor_venue_id );

        // Column Mapping
        $poi['vendor_poi_id']                 = (string) $vendor_venue_id;
        $poi['review_date']                   = (string) $venueElement->review_date;
        $poi['local_language']                = $this->vendor->language;
        $poi['poi_name']                      = (string) $venueElement->name;
        $poi['house_no']                      = (string) $venueElement->house_no;
        $poi['street']                        = (string) $venueElement->street;
        $poi['city']                          = (string) $venueElement->city;
        $poi['district']                      = (string) $venueElement->district;
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
        $poi['star_rating']                   = (string) $venueElement->star_rating;
        $poi['rating']                        = (string) $venueElement->rating;
        $poi['provider']                      = (string) $venueElement->provider;
        $poi['geocode_look_up']               = stringTransform::concatNonBlankStrings(', ', array( $poi['house_no'], $poi['street'], $poi['zips'], $poi['city'] ) );
        $poi['Vendor']                        = $this->vendor;

        // Categories
        $categories = array();
        foreach( $venueElement->categories->category as $category ) $categories[] = (string) $category;
        $poi->addVendorCategory( $categories, $this->vendor->id );

        // Timeout Link
        if( (string) $venueElement->timeout_url != "" )
            $poi->addProperty( "Timeout_link", (string) (string) $venueElement->timeout_url );

        // Add First Image Only
        $medias = array();
        foreach( $venueElement->medias->media as $media ) $medias[] = (string) $media;
        if( !empty( $medias ) ) $this->addImageHelper( $poi, $medias[0] );

        $this->notifyImporter( $poi );
    }
  }

  private function fixHtmlEntities( $string )
  {
    $string = htmlspecialchars_decode( (string) $string );
    $string = htmlspecialchars_decode( $string );
    return $string;
  }
}
?>
