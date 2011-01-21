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
class bucharestVenueMapper extends bucharestBaseMapper
{
  public function mapVenues()
  {
    foreach( $this->xmlNodes as $venueElement )
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
            
            // Bucharest sending us Geocode is wrong way around for some of the venues
            // and right way in some, hence we decided to use the new vendor method isWithinBoundaries() to
            // verify that this geocode is within boundaries or swap lat/long
            if( $poi[ 'latitude' ] !== null 
                && $poi[ 'longitude' ] !== null 
                && !$this->vendor->isWithinBoundaries( $poi['latitude'], $poi['longitude'] ) )
            {
                $tmpLat = $poi['latitude'];
                $poi['latitude'] = $poi['longitude'];
                $poi['longitude'] = $tmpLat;
                unset($tmpLat);
            }

            // Categories
            if( isset( $venueElement->categories->category ) )
            {
                $this->addVendorCategories( $poi, $venueElement );
            }

            $this->applyFeedGeoCodesHelper( $poi, $this->clean( (string) $venueElement->long ), $this->clean( (string) $venueElement->lat ) );

            $this->notifyImporter( $poi );
        }
        catch( Exception $exception )
        {
            $this->notifyImporterOfFailure( $exception, $poi );
        }
    }
  }

}
