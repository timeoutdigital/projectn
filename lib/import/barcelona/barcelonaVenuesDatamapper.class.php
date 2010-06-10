<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class barcelonaVenuesMapper extends barcelonaBaseDataMapper
{
  public function mapVenues()
  {
    foreach( $this->xml->venue as $venueElement )
    {
        try 
        {
            $poi = Doctrine::getTable( 'Poi' )->findOneByVendorIdAndVendorPoiId( $this->vendor['id'], (string) $venueElement['id'] );
            if( $poi === false )
              $poi = new Poi();

            $poi['vendor_poi_id']                 = (string) $venueElement['id'];
            $poi['review_date']                   = (string) $venueElement->review_date;
            $poi['local_language']                = $this['vendor']['language'];
            $poi['poi_name']                      = (string) $venueElement->name;
            $poi['house_no']                      = (string) $venueElement->house_no;
            $poi['street']                        = (string) $venueElement->street;
            $poi['city']                          = (string) $venueElement->city;
            $poi['district']                      = (string) $venueElement->district;
            $poi['country']                       = "ESP";
            $poi['additional_address_details']    = (string) $venueElement->additional_address_details;
            $poi['zips']                          = (string) $venueElement->postcode;
            $poi['longitude']                     = (string) $venueElement->long;
            $poi['latitude']                      = (string) $venueElement->lat;
            $poi['email']                         = (string) $venueElement->email;
            $poi['url']                           = trim( (string) $venueElement->url );
            $poi['phone']                         = trim( (string) $venueElement->phone );
            $poi['phone2']                        = trim( (string) $venueElement->phone2 );
            $poi['fax']                           = (string) $venueElement->fax;
            $poi['keywords']                      = (string) $venueElement->keywords;
            $poi['short_description']             = (string) $venueElement->short_description;
            $poi['description']                   = (string) $venueElement->description;

            foreach ( $venueElement->public_transports as $pt )
            {
                $poi['public_transport_links']      = (string) $pt->public_transport;
            }

            $poi['price_information']             = (string) $venueElement->price_information;
            $poi['openingtimes']                  = (string) $venueElement->opening_times;

            //$poi['provider']                      = (string) $venueElement->provider;
            $poi['geocode_look_up']               = 'to be added';
            $poi['Vendor']                        = $this->vendor;

            // Categories
            // Not Formalised Structure
//            foreach( $venueElement->categories->category as $category )
//              $poi->addVendorCategory( $category, $this->vendor->id );

            // Timeout Link
            if( (string) $venueElement->timeout_url != "" )
                $poi->setTimeoutLinkProperty( trim( (string) $venueElement->timeout_url ) );

            //Critics Choice
            $poi->setCriticsChoiceProperty( ( $venueElement->critics_choice == 'y' ) ? true : false );

            //// Add First Image Only
            //$medias = array();
            //foreach( $venueElement->medias->media as $media ) $medias[] = (string) $media;
            //if( !empty( $medias ) ) $this->addImageHelper( $poi, $medias[0] );
            
            $this->notifyImporter( $poi );
        }
        catch( Exception $exception )
        {
            $this->notifyImporterOfFailure( $exception );
        }
    }
  }
}
