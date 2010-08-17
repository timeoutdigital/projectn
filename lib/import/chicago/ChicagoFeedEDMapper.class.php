<?php
/**
 * Chicago Feed ED (Eating & Dining ?) mapper
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
class ChicagoFeedEDMapper extends ChicagoFeedBaseMapper
{

    /**
     * BC XML feed require cleaning before pharsing as XML, this overwrite should takecare of that issue
     * @param Doctrine_Record $vendor
     * @param string $dataFileName
     * @param SimpleXMLElement $xml
     * @param geoEncode $geoEncoder
     */
    public function __construct( Doctrine_Record $vendor, $dataFileName, SimpleXMLElement $xml = null,  geoEncode $geoEncoder = null )
    {
        if( !isset($xml) && !isset($dataFileName) )
        {
            throw new Exception( 'ChicagoFeedEDMapper:: No Data File name or XML feed provided!' );
        }

        // if No XML load file and Clean before pharsing as XML
        if( !$xml )
        {
            $xml = simplexml_load_string( $this->openAndCleanData( $dataFileName ) );
        }

        parent::__construct($vendor, $xml, $geoEncoder);
    }

    public function mapED()
    {
        foreach( $this->xml->ROW as $xmlNode)
        {
            // Move to Next If Closed found
            if( stringTransform::mb_trim( (string)$xmlNode->{'closed'} ) != '' )
            {
                continue; // Don't add Closed BC info
            }

            try{
                // Get Existing POI
                $poi = Doctrine::getTable( 'Poi' )->findOneByVendorPoiIdAndVendorId( (string) $xmlNode->ID, $this->vendorID );

                if( !$poi ) // Create new POI
                    $poi = new Poi();

                // Map Data
                $poi['vendor_id']                               = $this->vendorID;
                $poi['vendor_poi_id']                           = (string) $xmlNode->ID;
                $poi['poi_name']                                = (string) $xmlNode->name;
                $poi['description']                             = $this->fixHtmlEntities( (string) $xmlNode->body );
                $poi['local_language']                          = $poi['Vendor']['language'];
                
                $poi['street']                                  = (string) $xmlNode->location;
                $poi['additional_address_details']              = (string) $xmlNode->crossstreet;
                $poi['city']                                    = (string) $xmlNode->{'city.state'};
                $poi['public_transport_links']                  = (string) $xmlNode->cta;
                $poi['zips']                                    = (string) $xmlNode->zip;
                $poi['district']                                = (string) $xmlNode->hood;
                $poi['country']                                 = $poi['Vendor']['country_code_long'];

                $poi['price_information']                       = (string) $xmlNode->prices;
                $poi['url']                                     = stringTransform::formatUrl( (string) $xmlNode->url );
                $openingTimes                                   = $this->semiColon2Comma( (string) $xmlNode->hours );

                // Add openingtimes with hours.notes
                if( isset($xmlNode->{'hours.notes'}) && trim( (string)$xmlNode->{'hours.notes'} ) != '' )
                {
                    $hoursNotes                                 = $this->nl2Comma( (string) $xmlNode->{'hours.notes'} );
                    $openingTimes                               = stringTransform::concatNonBlankStrings(' - ', array( trim($openingTimes), trim($hoursNotes) ) );
                }
                $poi['openingtimes']                            = $openingTimes;

                // phone number
                $phoneNumber                                    = (string) $xmlNode->phone;
                if( strtolower( $phoneNumber ) != 'no phone' && trim( $phoneNumber ) != '' )
                {
                    $poi['phone']                                   = stringTransform::formatPhoneNumber( (string) $xmlNode->phone, $poi['Vendor']['inernational_dial_code'] );
                }
                // geocode lookup string
                $poi[ 'geocode_look_up' ]                       = stringTransform::concatNonBlankStrings(',', array(
                                                                                                                $poi[ 'poi_name' ],
                                                                                                                $poi[ 'street' ],
                                                                                                                $poi[ 'city' ],
                                                                                                                $poi[ 'zips' ],
                                                                                                                $poi[ 'country' ],
                                                                                                                    ));
                // Add Category
                if( trim( (string)$xmlNode->category ) != '' )
                {
                    $categoryArray                              = $this->nl2Array( (string)$xmlNode->category );

                    if( is_array( $categoryArray ) && count( $categoryArray ) > 0 )
                    {
                        $poi->addVendorCategory( $categoryArray , $this->vendorID );
                    }
                }

                // Add Features to Property
                if( trim( (string) $xmlNode->features ) != '')
                {
                    $features           = $this->nl2Comma( (string) $xmlNode->features );
                    // Clean up features property, to remove the string "Cheap (entrees under $10)" #251
                    $features           = mb_ereg_replace( '\s*\(entrees under\s\$\d*\)\s*', '', $features );
                    $poi->addProperty( 'features', $features );
                }

                // meta property for cuisine
                if( isset( $xmlNode->{'cuisine.1'} ) && trim( (string)$xmlNode->{'cuisine.1'} ) != '' )
                {
                    $cuisine            = (string) $xmlNode->{'cuisine.1'};

                    if( isset( $xmlNode->{'cuisine.2'} ) && trim( (string)$xmlNode->{'cuisine.2'} ) != '' )
                    {
                        $cuisine        = stringTransform::concatNonBlankStrings(', ', array( $cuisine, (string) $xmlNode->{'cuisine.2'} ) );
                    }// end if cuisine.2

                    if( isset( $xmlNode->{'cuisine.3'} ) && trim( (string)$xmlNode->{'cuisine.3'} ) != '' )
                    {
                        $cuisine        = stringTransform::concatNonBlankStrings(', ', array( $cuisine, (string) $xmlNode->{'cuisine.3'} ) );
                    }

                    // add Cuisine to property
                    $poi->addProperty( 'cuisine', $cuisine );
                    
                } // end if cuisine.1

                // Save POI
                $this->notifyImporter( $poi );

                $poi->free( true );
                unset( $poi );
                
            } catch (Exception $exception)
            {
                $this->notifyImporterOfFailure( new Exception('ChicagoFeedEDMapper::mapED Failed to import POI/BC: ' . (string) $xmlNode->ID . ' | Exception message: ' . $exception->getMessage() ) );
            }
        }
    }
}

?>
