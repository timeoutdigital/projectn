<?php
/**
 * UAE Feed Restaurents Mapper
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
class UAEFeedRestaurentsMapper extends UAEFeedBarsMapper
{
    /* Since this extends: UAEFeedBarsMapper, map function should be taken care of
     * Only needed to overide the addvendorCategory() to Manipulate cuisine information
     */

    /**
     * Add vendor Category to poi, Extends this class and overide in Restaurent to process Cuisine
     * @param Doctrine_Record $poi
     * @param SimpleXMLElement $xmlNode
     */
    protected function addVendorCategory( Doctrine_Record $poi, SimpleXMLElement $xmlNode )
    {
        // Add Default category
        $poi->addVendorCategory('Eating & Drinking', $this->vendor_id ); // Default Category
        
        // Process cuisine
        $cuisineArray = explode(',', $xmlNode->{'cuisine'});
        $cuisineFinal = array();
        foreach($cuisineArray as $cuisine)
        {
            $cuisineString = stringTransform::mb_trim( (string) $cuisine );
            $priceString = ": $";   // Find price information in Cuisine!

            $findPriceString = strpos( $cuisineString, $priceString );
            if( $findPriceString !== false)
            {
                // Split price information and Cusine information
                $priceSectionString = substr( $cuisineString, $findPriceString + strlen( $priceString ) -1 );
                $cuisineString = substr( $cuisineString, 0, $findPriceString );

                // Create a 'price_general_remark' property to hold the price info.
                if( stringTransform::mb_trim( $priceSectionString ) != '' && substr( $priceSectionString, 0, 1 ) == "$" )
                {
                    $poi->addProperty( 'price_general_remark', $priceSectionString );
                }
            }
            $cuisineFinal[] = stringTransform::mb_trim( $cuisineString );
        } // foreach

        // add Cuisine Information to DB
        $poi->addProperty('cuisine', stringTransform::concatNonBlankStrings(', ', $cuisineFinal) );
    }
}

?>
