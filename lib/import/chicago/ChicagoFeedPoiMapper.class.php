<?php
/**
 * Chicago Feed POI mapper
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
class ChicagoFeedPoiMapper extends ChicagoFeedBaseMapper
{
    public function mapPoi()
    {
        // Get List of POI's From Feed
        $poiNodes = $this->getXMLNodesByPath( '/body/address' );

        if( !$poiNodes || $poiNodes === null || count( $poiNodes ) <= 0 )
        {
            $this->notifyImporterOfFailure( new Exception( 'ChicagoFeedPoiMapper::mapPoi - No POI Nodes found in the Feed?' ) );
            return;
        }

        // Loop and add All POI's
        foreach( $poiNodes as $poiNode )
        {
            try
            {
                $poi = Doctrine::getTable( 'Poi' )->findOneByVendorPoiIdAndVendorId( $poiNode['id'], $this->vendor['id'] );

                if( !$poi )
                    $poi = new Poi();

                // Map Data
                $poi[ 'Vendor' ]                        = clone $this->vendor;
                $poi[ 'vendor_poi_id' ]                 = (string)  $poiNode['id'];
                $poi[ 'poi_name' ]                      = (string) $poiNode->identifier;
                $poi[ 'street' ]                        = (string) $poiNode->street;
                $poi[ 'city' ]                          = (string) $poiNode->town;
                $poi[ 'district' ]                      = (string) $poiNode->district;
                $poi[ 'country' ]                       = $poi['Vendor']['country_code_long'];
                $poi[ 'local_language' ]                = $poi['Vendor']['language'];
                $poi[ 'additional_address_details' ]    = stringTransform::concatNonBlankStrings( ', ', array( (string) $poiNode->cross_streets, (string) $poiNode->suburb ) );
                $poi[ 'url' ]                           = stringTransform::formatUrl((string) $poiNode->website);

                // Formatted Telephone number
                // $countryCodeString                      = (string) $poiNode->telephone->country_code;
                $areaCodeString                         = (string) $poiNode->telephone->area_code;
                $poi[ 'phone' ]                         = $areaCodeString . (string) $poiNode->telephone->number;

                // Set GeoCode Lookup String
                $geoCodeLookup = stringTransform::concatNonBlankStrings( ', ', array(
                                                                                    $poi[ 'poi_name' ],
                                                                                    $poi[ 'street' ],
                                                                                    (string) $poiNode->district,
                                                                                    (string) $poiNode->suburb,
                                                                                    (string) $poiNode->town,
                                                                                    (string) $poiNode->country_symbol,
                                                                                    (string) $poiNode->state,
                                                                                    ) );
                $poi->setGeoEncodeLookUpString( $geoCodeLookup );

                // More Details
                $textSystem = $this->getXMLNodesByPath( 'text_system/text', $poiNode );
                
                if( $textSystem && is_array( $textSystem ) && count( $textSystem ) > 0 )
                {
                    foreach( $textSystem as $text)
                    {
                        switch ( (string) $text->{'text_type'} )
                        {
                            case 'Venue Blurb':
                                $poi[ 'description' ] = (string) $text->content;
                                break;

                            case 'Approach Descriptions':
                                $poi[ 'public_transport_links' ] = (string) $text->content;
                                break;

                            case 'Web Keywords':
                                $poi[ 'keywords' ] = (string) $text->content;
                                break;
                        } // Switch
                    } // foreach
                }
                

                // Pricing
                if( isset( $poiNode->prices ) )
                {
                    foreach ( $poiNode->prices->children() as $price_id )
                    {
                        foreach ( $price_id->children() as $price )
                        {
                            if( $price->getName() == 'general_remark' && stringTransform::mb_trim( (string) $price ) != '' )
                            {
                                $poi->addProperty( 'price_general_remark', (string) $price );

                            } else {

                                $priceValue = ( (string) $price->value != '0.00' ) ? (string) $price->value : '';
                                $priceValueTo = ( (string) $price->value_to != '0.00' ) ? '-' . (string) $price->value_to : '';
                                $priceInfoString = stringTransform::concatNonBlankStrings( ' - ', array( (string) $price->currency, $priceValue, $priceValueTo ) ); // add Price from - to values
                                $priceInfoString = stringTransform::concatNonBlankStrings( ' ', array( (string) $price->price_type, $priceInfoString ) ); // add Money Sign
                                $poi->addProperty( 'price', trim( $priceInfoString ) );
                            }
                        } // foreach $price_id->children()
                    } // $poiNode->prices->children()
                } // Pricing

                // Category
                if ( isset( $poiNode->attributes ) )
                {
                    $poiCategoryArray = array();
                    foreach ( $poiNode->attributes->children() as $attribute )
                    {
                        $attributeNameString = (string) $attribute->name;
                        $attributeValueString = trim( (string) $attribute->value );

                        if ( 'Venue type: ' == substr( $attributeNameString, 0, 12 ) && 12 < strlen( $attributeNameString ) )
                        {
                            $categoryString = substr( $attributeNameString, 12 ); // Extract Category
                            $categoryString = stringTransform::mb_trim( $categoryString ); // Trim Category

                            if ( ! in_array( $categoryString, $poiCategoryArray) )
                            {
                                $poiCategoryArray[] = $categoryString; // add to Category LIST
                            }
                            continue;

                        } // if Venue type:

                        $attributeNameStringtmp = mb_strtolower( $attributeNameString );

                        if( $attributeNameStringtmp == "critics_choice" || $attributeNameStringtmp == "critic's picks")
                        {
                            $attributeValueString = strtolower( (string) $attribute->value );

                            // Chicago and New York seem to like to send us 'Yes' instead of 'y' every now and then.
                            $attributeValueString = substr( $attributeValueString , 0, 1 );

                            if( $attributeValueString == 'y')
                            {
                                $poi->setCriticsChoiceProperty( true );
                            } else {
                                $poi->setCriticsChoiceProperty( false );
                            }

                        } //  $attribute->name == "Critics_choice"

                        // add to property
                        $poi->addProperty( $attributeNameString, $attributeValueString );

                    } // foreach $poiNode->attributes->children()

                    // Add Category
                    $poi->addVendorCategory( $poiCategoryArray, $poi['Vendor']['id'] );
                } // if $poiNode->attributes

                // Add opening hours
                if( isset( $poiNode->opening_hours ) && $poiNode->opening_hours)
                {
                    $poi['openingtimes'] = $this->getOpeningHours( $poiNode->opening_hours );
                }

                // Save
                $this->notifyImporter( $poi );
                echo '¦';

                //Kill the object
                $poi->free();

                unset( $poi, $textSystem, $poiCategoryArray );

            }  catch ( Exception $exception)
            {
                $this->notifyImporterOfFailure( new Exception( 'ChicagoFeedPoiMapper:: Poi Exception: ' . $exception->getMessage() . ' | Vendor Poi ID: ' .$poi['vendor_poi_id'] ) );
                echo 'ChicagoFeedPoiMapper:: Poi Exception: ' . $exception->getMessage() . ' | Vendor Poi ID: ' .$poi['vendor_poi_id'] . PHP_EOL;
            }
        } // end foreach
    }

    private function getOpeningHours( SimpleXMLElement $openingHours )
    {
        if( !isset($openingHours) || !$openingHours )
            return null;

        $openingHourArray = array();
        // For each days of the week add opening hours
        if( isset ( $openingHours->day ) )
        {
            foreach( $openingHours->day as $day )
            {
                $dayArray = array();
                
                foreach( $day->children() as $dayChild )
                {
                    $dayArray[] = substr( (string) $dayChild, 0, 5 ); // Remove Seconds from Time
                } // $dayChild

                if( count($dayArray) <= 0 ) continue;
                
                $openingHourArray[] = $day['dw'] .' ' . implode( ' - ', $dayArray );
                
            } // Foreach $day
        }

        // general remarks
        $remark = stringTransform::concatNonBlankStrings(', ', array( (string )$openingHours->remark, (string )$openingHours->{'general_remark'} ) );

        if( count($openingHourArray) <= 0 && stringTransform::mb_trim( $remark ) == '' ) return null;

        $openingHourArray[] = $remark; // Add to array as Last
        
        return stringTransform::concatNonBlankStrings(', ', $openingHourArray ); // Merge and concat into string
        
    }
}

?>
