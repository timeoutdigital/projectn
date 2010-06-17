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
class barcelonaVenuesMapper extends barcelonaBaseMapper
{
  public function mapVenues()
  {
    for( $i=0, $venueElement = $this->xml->venue[ 0 ]; $i<$this->xml->venue->count(); $i++, $venueElement = $this->xml->venue[ $i ] )
    {
        try 
        {
            $poi = Doctrine::getTable( 'Poi' )->findOneByVendorIdAndVendorPoiId( $this->vendor['id'], (string) $venueElement['id'] );
            if( $poi === false )
              $poi = new Poi();

            $poi['vendor_poi_id']                 = (string) $venueElement['id'];
            $poi['review_date']                   = (string) $venueElement->review_date;
            $poi['local_language']                = $this->vendor['language'];
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
            $poi['public_transport_links']        = $this->extractPublicTransportInfo( $venueElement );
            $poi['price_information']             = (string) $venueElement->price_information;
            $poi['openingtimes']                  = (string) $venueElement->opening_times;
            //$poi['provider']                      = (string) $venueElement->provider;
            $poi['geocode_look_up']               = stringTransform::concatNonBlankStrings(', ', array( $poi['house_no'], $poi['street'], $poi['zips'], $poi['city'] ) );
            $poi['Vendor']                        = clone $this->vendor;

            // Categories
            $pois->addVendorCategory( $this->extractCategories( $venueElement ), $this->vendor->id );

            // Timeout Link
            if( (string) $venueElement->timeout_url != "" )
                $poi->setTimeoutLinkProperty( trim( (string) $venueElement->timeout_url ) );

            //Critics Choice
            $poi->setCriticsChoiceProperty( strtolower( (string) $venueElement->critics_choice ) == 'y' ? true : false );

            //// Add First Image Only
            //$medias = array();
            //foreach( $venueElement->medias->media as $media ) $medias[] = (string) $media;
            //if( !empty( $medias ) ) $this->addImageHelper( $poi, $medias[0] );
            
            //$this->notifyImporter( $poi );
        }
        catch( Exception $exception )
        {
            $this->notifyImporterOfFailure( $exception );
        }
    }
  }
}
