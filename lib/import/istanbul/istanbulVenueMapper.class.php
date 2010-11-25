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

        // poi->presave will apend review date with 00:00:00 as time when only date given, this cause update on each run... hence, 00:00:00 added during import
        $reviewDate =  ( trim( $this->clean( (string) $venueElement->review_date ) ) != ''  ) ? date("Y-m-d 00:00:00" , strtotime( $this->clean( (string) $venueElement->review_date ) ) ) : null ;

        $poi = Doctrine::getTable( 'poi' )->findOneByVendorIdAndVendorPoiId( $this->vendor['id'], $this->clean( (string) $venueElement['id'] ) );
        if( $poi === false )
        {
          $poi = new Poi();
        }
        else if( $poi['review_date'] !=null &&  $poi['review_date'] == $reviewDate ) // Check for Review date and SKIP when POI is not updated, this will help with memory issue
        {
            continue;
        }

        // Map data
        try
        {
            $poi['vendor_poi_id']                 = $this->clean( (string) $venueElement['id'] );
            $poi['review_date']                   = $reviewDate;
            $poi['local_language']                = $this->vendor['language'];
            $poi['poi_name']                      = $this->clean( (string) $venueElement->name );
            $poi['house_no']                      = $this->clean( (string) $venueElement->house_no );
            $poi['street']                        = $this->clean( (string) $venueElement->street );
            $poi['city']                          = 'Istanbul';
            $poi['district']                      = $this->clean( (string) $venueElement->district );
            $poi['country']                       = $this->clean( (string) $this->vendor['country_code_long'] );
            $poi['zips']                          = $this->clean( (string) $venueElement->postcode );

            $url = (string) $venueElement->url;
            //some of the url's has only  "http://"
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
            $description                          = preg_replace("/\n{2,}/su","\n\n",(string) $venueElement->description );
            $poi['description']                   = $this->clean( $description );
            //$poi['public_transport_links']        = $this->extractPublicTransportInfo( $venueElement );
            $poi['price_information']             = $this->clean( (string) $venueElement->price_information ); // #781 - Some of them have values like Orta (Middle), Pahalı (Expensive) etc...
            $poi['openingtimes']                  = $this->clean( (string) $venueElement->opening_times ); //#781 - As of today, data in the feed is valid!
            //$poi['star_rating']
            //$poi['rating']
            $poi['provider']                      = $this->clean( (string) $venueElement->provider );
            $poi['rating']                        = $this->roundNumberOrReturnNull((string) $venueElement->rating);
            $poi['geocode_look_up']               = stringTransform::concatNonBlankStrings(', ', array( $poi['house_no'], $poi['street'], $poi['zips'], $poi['city'] ) );
            $poi['Vendor']                        = clone $this->vendor;

            // Use Feed lat / Long
            // ISTANBUL Sending Lat/Long wrongway around... Swap lat long and it's Good as of 22 Sep 2010
            $poi->applyFeedGeoCodesIfValid( $this->clean( (string) $venueElement->long ), $this->clean( (string) $venueElement->lat ) );

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
                $this->addImageHelper( $poi, (string) $media ); //#753 addImageHelper capture Exception and notify, this don't break the Import process
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
