<?php
/**
 * Istanbul DataMapper
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
class istanbulBaseMapper extends DataMapper
{
    /**
    * @var geoEncode
    */
    protected $geoEncoder;

    /**
    * @var Vendor
    */
    protected $vendor;

    /**
    * @var SimpleXMLElement
    */
    protected $xml;

    /**
    *
    * @param SimpleXMLElement $xml
    * @param geoEncode $geoEncoder
    * @param string $city
    */
    public function __construct( SimpleXMLElement $xml, geoEncode $geoEncoder = null )
    {        
        $this->vendor     = Doctrine::getTable( 'Vendor' )->findOneByCityAndLanguage( 'istanbul', 'tr' );

        //date_default_timezone_set( $this->vendor->time_zone );
        //setlocale( LC_ALL, array( 'ca_ES.utf8','ca_ES.utf8@valencia','ca_ES','catalan' ) );
        
        $this->geoEncoder = is_null( $geoEncoder ) ? new geoEncode() : $geoEncoder;
        $this->xml        = $xml;
    }

    protected function fixHtmlEntities( $string )
    {
        $string = html_entity_decode( (string) $string, ENT_QUOTES, 'UTF-8' );
        return $string;
    }

    protected function extractCategories( $element )
    {
        // This is so complicated because it covers the rare event
        // where one parent category has multiple child categories.
        $categories = array();
        foreach( $element->categories->category as $category )
        {
            $categoryName = $this->clean( (string) $category->name );

            // Category has No Children
            if( count( $category->children->category ) === 0 ) $categories[] = $categoryName;

            // Catgeory has Children
            else foreach( $category->children->category as $subCategory )
                $categories[] = stringTransform::concatNonBlankStrings( " | ", array( $categoryName, $this->clean( (string) $subCategory->name ) ) );
        }
        return array_unique( $categories );
    }

    protected function clean( $string )
    {
        return stringTransform::mb_trim( $string );
    }

    protected function extractPublicTransportInfo( $element )
    {
        // Public Transport Links
        $publicTransportArray = array();
        
        foreach ( $element->public_transports as $transportElement )
            $publicTransportArray[]           = $this->clean( (string) $transportElement->public_transport );

        return stringTransform::concatNonBlankStrings( ", ", $publicTransportArray );
    }

    /**
     * helper function to add images
     *
     * @param Doctrine_Record $storeObject
     * @param SimpleXMLElement | String $url
     */
    protected function addImageHelper( Doctrine_Record $storeObject, $url )
    {
        if ( (string) $url != '' )
        {
            try
            {
                $storeObject->addMediaByUrl( (string) $url );
                return true;
            }
            catch( Exception $e )
            {
                $this->notifyImporterOfFailure( $e );
            }
        }
    }
}
?>
