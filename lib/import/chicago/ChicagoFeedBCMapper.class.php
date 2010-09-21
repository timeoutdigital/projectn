<?php
/**
 * Chicago Feed BC (bars & Clubs ?) mapper
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
class ChicagoFeedBCMapper extends ChicagoFeedBaseMapper
{

    /**
     * BC XML feed require cleaning before pharsing as XML, this overwrite should takecare of that issue
     * @param Doctrine_Record $vendor
     * @param string $dataFileName
     * @param SimpleXMLElement $xml
     * @param geoEncode $geoEncoder
     */
    public function __construct( Doctrine_Record $vendor, $dataFileName, SimpleXMLElement $xml = null,  geocoder $geoEncoder = null )
    {
        if( !isset($xml) && !isset($dataFileName) )
        {
            throw new Exception( 'ChicagoFeedBCMapper:: No Data File name or XML feed provided!' );
        }

        // if No XML load file and Clean before pharsing as XML
        if( !$xml )
        {
            $xml = simplexml_load_string( $this->openAndCleanData( $dataFileName ) );
        }

        parent::__construct($vendor, $xml, $geoEncoder);
    }

    public function mapBC()
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
                $poi = Doctrine::getTable( 'Poi' )->findOneByVendorPoiIdAndVendorId( trim ((string) $xmlNode->ID ), $this->vendorID );

                if( !$poi ) // Create new POI
                    $poi = new Poi();

                // Map Data
                $poi['vendor_id']                               = $this->vendorID;
                $poi['vendor_poi_id']                           = trim ((string) $xmlNode->ID );
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
                $poi['openingtimes']                            = $this->semiColon2Comma( (string) $xmlNode->hours );
                $poi['url']                                     = stringTransform::formatUrl( (string) $xmlNode->url );

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
                    $poi->addProperty( 'features', $this->nl2Comma( (string) $xmlNode->features ) );
                }

                // Save POI
                $this->notifyImporter( $poi );

                $poi->free( true );
                unset( $poi );
                
            } catch (Exception $exception)
            {
                $this->notifyImporterOfFailure( new Exception('ChicagoFeedBCMapper::mapBC Failed to import POI/BC: ' . (string) $xmlNode->ID . ' | Exception message: ' . $exception->getMessage() ) );
                echo 'Exception Found: ChicagoFeedBCMapper::mapBC Failed to import POI (BC): ' . (string) $xmlNode->ID . ' | Exception message: ' . $exception->getMessage() . PHP_EOL;
            }
        }
    }
}

?>
