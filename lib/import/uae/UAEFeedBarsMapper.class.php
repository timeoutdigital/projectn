<?php
/**
 * UAE Feed Bars Mapper
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
class UAEFeedBarsMapper extends UAEFeedBaseMapper
{

    public function mapBars( )
    {
        // Process bars in XML feed
        foreach( $this->xml->item as $xmlNode )
        {
            try {

                // Get Existing POI
                $poi    = Doctrine::getTable( 'Poi' )->findOneByVendorIdAndVendorPoiId( $this->vendor_id, trim($xmlNode->id) );
                if( $poi === false )
                {
                    $poi = new Poi( );
                }

                // Map Data
                $poi['vendor_id']           = $this->vendor_id;
                $poi['vendor_poi_id']       = (string) $xmlNode->{'id'};

                $poi['poi_name']            = (string) $xmlNode->{'title'};
                $poi['description']         = (string) $xmlNode->{'description'};
                $poi['url']                 = stringTransform::formatUrl( (string) $xmlNode->{'website'} );
                $poi['phone']               = (string) $xmlNode->{'telephone'};
                $poi['review_date']         = (string) $xmlNode->{'pubDate'};
                $poi['email']               = (string) $xmlNode->{'email'};
                $poi['openingtimes']        = (string) $xmlNode->{'timings'};
                $poi['country']             = $poi['Vendor']['country_code_long'];
                $poi['local_language']      = $poi['Vendor']['language'];

                // Add Timeout Link
                if( trim((string) $xmlNode->{'link'}) != '' )
                {
                    $poi->setTimeoutLinkProperty( stringTransform::formatUrl( (string) $xmlNode->{'link'} ) );
                }

                /* Process Address
                 * Address are like: street name, District, City...
                 * Sometime we get Morethan 3 Split, so rest will go into Addittional address
                 */
                $addressArray               = explode( ',', (string) $xmlNode->{'location'} );
                if( count( $addressArray ) == 3 ) // When we found more than 3, producer will have to process the data
                {
                    $poi['street']                      = array_shift( $addressArray ); // First one is always Street
                    $poi['district']                    = array_shift( $addressArray ); // Second one is district
                    $poi['city']                        = array_shift( $addressArray ); // Last one is City
                    // Build Geocode Lookup String
                    $poi->setgeocoderLookUpString( stringTransform::concatNonBlankStrings( ',',  array($poi['street'],
                                                                                                        $poi['district'],
                                                                                                        $poi['city'] ) ) );
                } else {

                    $poi['city']                        = $poi['Vendor']['city']; // required in validation
                    $poi['additional_address_details']  = (string) $xmlNode->{'location'};
                    $poi->setgeocoderLookUpString( $poi['additional_address_details'] );
                }

                /* Process Poi Category
                 * Restaurent FeedMapper will extend this class and overide this protected function to process cuisine
                 */
                $this->addVendorCategory( $poi, $xmlNode );

                
                // #881 Catch Geocode out of vendor boundary error
                try{
                    /* Add Latitude and Longitude if Valid
                     * New Feed (as of 07 Sep 10 )from Dubai have latitude and longitude
                     */
                    $poi->applyFeedGeoCodesIfValid( (string) $xmlNode->{'latitude'},  (string) $xmlNode->{'longitude'} );
                } catch ( Exception $exception ) {
                    $this->notifyImporterOfFailure( $exception, $poi );
                }
                // Save POI
                $this->notifyImporter( $poi );
                
            } catch (Exception $exc) {
                echo 'Exception UAEFeedBarsMapper::mapBars - ' . $exc->getMessage() . PHP_EOL;
                $this->notifyImporterOfFailure( $exc );
            }
        } // foreach
    }

    /**
     * Add vendor Category to poi, Extends this class and overide in Restaurent to process Cuisine
     * @param Doctrine_Record $poi
     * @param SimpleXMLElement $xmlNode 
     */
    protected function addVendorCategory( Doctrine_Record $poi, SimpleXMLElement $xmlNode )
    {
        // Bars have Category as type
        $poi->addVendorCategory((string) $xmlNode->{'type'}, $this->vendor_id );
    }
}

?>
