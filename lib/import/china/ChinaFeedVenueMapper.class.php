<?php
/**
 * China City Feed Venue Import Mapper
 *
 * @package projectn
 * @subpackage
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */


class ChinaFeedVenueMapper extends ChinaFeedBaseMapper
{
    public function mapVenue()
    {
        $xmlNodes = $this->xmlNodes->xpath( '//venues/venue' ); // use xpath to extract only venues

        for( $i = 0; $i < count( $xmlNodes ); $i++ )
        {
            $xmlNode = $xmlNodes[$i]; // Get the XML NODE

            try{
                // Get Existing POI
                $vpid = trim( (string)$xmlNode['id'] );

                $poi = Doctrine::getTable( 'Poi' )->findOneByVendorIdAndVendorPoiId( $this->vendor['id'], $vpid );
                if( $poi === false )
                {
                    $poi = new Poi();
                }

                // Map data
                $poi['vendor_id']                       = $this->vendor['id'];
                $poi['vendor_poi_id']                   = $vpid;
                $poi['poi_name']                        = (string) $xmlNode->name;
                $poi['short_description']               = (string) $xmlNode->short_description;
                $poi['description']                     = (string) $xmlNode->description;

                $poi['house_no']                        = (string) $xmlNode->house_no;
                $poi['street']                          = (string) $xmlNode->street;
                $poi['city']                            = (string) $xmlNode->city;
                $poi['district']                        = (string) $xmlNode->district;
                $poi['additional_address_details']      = (string) $xmlNode->additional_address_details;
                $poi['country']                         =  $poi['Vendor']['country_code_long'];
                $poi['zips']                            = (string) $xmlNode->postcode;

                $poi['price_information']               = (string) $xmlNode->price_information;
                $poi['phone']                           = (string) $xmlNode->phone;

                // use Feed Geocode (we have to reverse lat/long because china provide it the wrong way around)
                $poi->applyFeedGeoCodesIfValid( (string)$xmlNode->long, (string)$xmlNode->lat );

                // Extract Category
                if( isset( $xmlNode->categories ) )
                {
                    $this->extractCategory( $poi, $xmlNode);
                }
                
                // Add timeout Link
                if( trim( (string) $xmlNode->timeout_url ) != '' )
                {
                    $poi->setTimeoutLinkProperty( (string) $xmlNode->timeout_url );
                }

                $this->notifyImporter( $poi );

            } catch ( Exception $ex )
            {
                echo 'Exception: ' . $ex->getMessage() . PHP_EOL;
                $this->notifyImporterOfFailure( $ex, $poi );
            }

        }// for Loop
    }
}