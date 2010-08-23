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
class istanbulVenueMapper extends istanbulBaseMapper
{
  public function mapVenues()
  {
    for( $i=0, $venueElement = $this->xml->venue[ 0 ]; $i<$this->xml->venue->count(); $i++, $venueElement = $this->xml->venue[ $i ] )
    {
        $poi = Doctrine::getTable( 'poi' )->findOneByVendorIdAndVendorPoiId( $this->vendor['id'], $this->clean( (string) $venueElement['id'] ) );
        if( $poi === false )
          $poi = new Poi();

        try
        {
            $poi['vendor_poi_id']                 = $this->clean( (string) $venueElement['id'] );
            $poi['review_date']                   = $this->clean( date("Y-m-d" , strtotime( (string) $venueElement->review_date  ) ) );
            $poi['local_language']                = $this->vendor['language'];
            $poi['poi_name']                      = $this->clean( (string) $venueElement->name );
            $poi['house_no']                      = $this->clean( (string) $venueElement->house_no );
            $poi['street']                        = $this->clean( (string) $venueElement->street );
            $poi['city']                          = $this->clean( (string) $this->vendor['city'] );
            $poi['district']                      = $this->clean( (string) $venueElement->district );
            $poi['country']                       = $this->clean( (string) $this->vendor['country_code_long'] );
            $poi['zips']                          = $this->clean( (string) $venueElement->postcode );

            //opening times field has extra information in Turkish. example (eng translation) : “open between 10am – 7pm. Accepts Credit cards.Non-smoking section available”
            //so we don't import opening_times
            //$poi['opening_times']                 = null;

            $url = (string) $venueElement->url;
            if( $url == 'http://' )
            {
                $url = null;
            }
            $poi['email']                         = $this->clean( (string) $venueElement->email );
            $poi['url']                           = $this->clean( $url );
            $poi['phone']                         = $this->clean( (string) $venueElement->phone );
            $poi['phone2']                        = $this->clean( (string) $venueElement->phone2 );
            $poi['fax']                           = $this->clean( (string) $venueElement->fax );
            $poi['keywords']                      = $this->clean( (string) $venueElement->keywords );
            $poi['short_description']             = $this->clean( (string) $venueElement->short_description );
            $poi['description']                   = $this->clean( (string) $venueElement->description );
            //$poi['public_transport_links']        = $this->extractPublicTransportInfo( $venueElement );
            $poi['price_information']             = $this->clean( (string) $venueElement->price_information );
            $poi['openingtimes']                  = $this->clean( (string) $venueElement->opening_times );

            $poi['rating']                        = (string) $venueElement->rating;
            $poi['geocode_look_up']               = stringTransform::concatNonBlankStrings(', ', array( $poi['house_no'], $poi['street'], $poi['zips'], $poi['city'] ) );
            $poi['Vendor']                        = clone $this->vendor;

            // Categories
            $cats = $this->extractCategories( $venueElement );

            foreach( $cats as $cat )
            {
                $poi->addVendorCategory( $cat );
            }

            // Timeout Link
            if( (string) $venueElement->timeout_url != "" )
            {
                $poi->setTimeoutLinkProperty( $this->clean( (string) $venueElement->timeout_url ) );
            }

            foreach( $venueElement->medias->media as $media )
            {
                $poi->addMediaByUrl( (string) $media );

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
