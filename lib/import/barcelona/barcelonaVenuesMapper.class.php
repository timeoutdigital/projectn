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
            $poi = Doctrine::getTable( 'Poi' )->findOneByVendorIdAndVendorPoiId( $this->vendor['id'], $this->clean( (string) $venueElement['id'] ) );
            if( $poi === false )
              $poi = new Poi();


            $poi['vendor_poi_id']                 = $this->clean( (string) $venueElement['id'] );
            $poi['review_date']                   = $this->clean( (string) $venueElement->review_date );
            $poi['local_language']                = $this->vendor['language'];
            $poi['poi_name']                      = $this->clean( (string) $venueElement->name );
            $poi['house_no']                      = $this->clean( (string) $venueElement->house_no );
            $poi['street']                        = $this->clean( (string) $venueElement->street );
            $poi['city']                          = $this->clean( (string) $venueElement->city );
            $poi['district']                      = $this->clean( (string) $venueElement->district );
            $poi['country']                       = "ESP";
            $poi['additional_address_details']    = $this->clean( (string) $venueElement->additional_address_details );
            $poi['zips']                          = $this->clean( (string) $venueElement->postcode );

            $poi->applyFeedGeoCodesIfValid( $this->clean( (string) $venueElement->lat ), $this->clean( (string) $venueElement->long ) );

            $poi['email']                         = $this->clean( (string) $venueElement->email );
            $poi['url']                           = $this->clean( (string) $venueElement->url );
            $poi['phone']                         = $this->clean( (string) $venueElement->phone );
            $poi['phone2']                        = $this->clean( (string) $venueElement->phone2 );
            $poi['fax']                           = $this->clean( (string) $venueElement->fax );
            $poi['keywords']                      = $this->clean( (string) $venueElement->keywords );
            $poi['short_description']             = $this->clean( (string) $venueElement->short_description );
            $poi['description']                   = $this->clean( (string) $venueElement->description );
            $poi['public_transport_links']        = $this->extractPublicTransportInfo( $venueElement );
            $poi['price_information']             = str_replace( PHP_EOL, '', $this->clean( (string) $venueElement->price_information ) );
            $poi['openingtimes']                  = $this->clean( (string) $venueElement->opening_times );
            //$poi['star_rating']
            //$poi['rating']
            //$poi['provider']                      = (string) $venueElement->provider;
            $poi['geocode_look_up']               = stringTransform::concatNonBlankStrings(', ', array( $poi['house_no'], $poi['street'], $poi['zips'], $poi['city'] ) );
            $poi['Vendor']                        = clone $this->vendor;

            // Categories
            $cats = $this->extractCategories( $venueElement );
            foreach( $cats as $cat ) $poi->addVendorCategory( $cat );

            // Timeout Link
            if( (string) $venueElement->timeout_url != "" )
                $poi->setTimeoutLinkProperty( $this->clean( (string) $venueElement->timeout_url ) );

            //Critics Choice
            $poi->setCriticsChoiceProperty( strtolower( $this->clean( (string) $venueElement->critics_choice ) ) == 'y' );

            //// Add First Image Only
            //$medias = array();
            //foreach( $venueElement->medias->media as $media ) $medias[] = (string) $media;
            //if( !empty( $medias ) ) $this->addImageHelper( $poi, $medias[0] );

            // #837 Remove None numeric Chars before formating (done in pre-save)
            if( $poi['phone'] != null && trim( $poi['phone'] ) != '' )
            {
                $phoneFixer = new phoneNumberFixer( $poi['phone'] );
                $phoneFixer->removeNonNumeric();
                $poi['phone'] = $phoneFixer->getPhoneNumber();
            }
            if( $poi['phone2'] != null && trim( $poi['phone2'] ) != '' )
            {
                $phoneFixer = new phoneNumberFixer( $poi['phone2'] );
                $phoneFixer->removeNonNumeric();
                $poi['phone2'] = $phoneFixer->getPhoneNumber();
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
