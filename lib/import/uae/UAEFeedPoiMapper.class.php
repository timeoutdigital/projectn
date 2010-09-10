<?php
/**
 * UAE Feed Poi(Venues) Mapper
 *
 * @package projectn
 * @subpackage
 *
 * @author Rajeevan kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */
class UAEFeedPoiMapper extends UAEFeedBaseMapper
{
    public function mapPoi()
    {
        foreach($this->xml->venue as $xmlNode)
        {
            try {
                // Get Existing POI
                $poi    = Doctrine::getTable( 'Poi' )->findOneByVendorIdAndVendorPoiId( $this->vendor_id, trim($xmlNode['id']) );
                if( $poi === false )
                {
                    $poi = new Poi( );
                }

                // Map data
                $poi['vendor_id']                       = $this->vendor_id;
                $poi['vendor_poi_id']                   = (string) $xmlNode['id'];
                $poi['poi_name']                        = (string) $xmlNode->{'name'};
                $poi['phone']                           = (string) $xmlNode->{'phone'};
                $poi['email']                           = (string) $xmlNode->{'email'};
                $poi['price_information']               = (string) $xmlNode->{'prices'};
                $poi['openingtimes']                    = (string) $xmlNode->{'hours'};
                $poi['city']                            = ucwords($poi['Vendor']['city']);
                $poi['country']                         = $poi['Vendor']['country_code_long'];
                $poi['district']                        = (string) $xmlNode->{'neighbourhood'};
                $poi['street']                          = (string) $xmlNode->{'travel'};
                // $poi['public_transport_links']          = (string) $xmlNode->{'travel'};
                $poi['description']                     = (string) $xmlNode->{'description'};

                // apply Feed Geocode
                $poi->applyFeedGeoCodesIfValid( (string) $xmlNode->coordinates->{'latitude'}, (string) $xmlNode->coordinates->{'longitude'} );

                // Set geocode lookup string
                $poi->setgeocoderLookUpString( stringTransform::concatNonBlankStrings( ', ', array( $poi['street'], $poi['city'], 'United Arab Emirates') ) );

                // Timeout link
                $poi->setTimeoutLinkProperty( stringTransform::formatUrl( (string) $xmlNode->{'landing_url'} ) );

                // Category
                $poi->addVendorCategory( (string) $xmlNode->{'mobile-section'}['value'], $this->vendor_id );

                $poi->save();
                
            } catch (Exception $exc) {
                echo 'Exception UAEFeedPoiMapper::mapPoi - ' . $exc->getMessage() . PHP_EOL;
                $this->notifyImporterOfFailure( $exc);
            }
        }
    }
}