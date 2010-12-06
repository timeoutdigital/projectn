<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage
 *
 * @author Peter Johnson <peterjohnson@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class barcelonaBaseMapper extends DataMapper
{
    /**
    * @var geocoder
    */
    protected $geocoderr;

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
    * @param geocoder $geocoderr
    * @param string $city
    */
    public function __construct( Vendor $vendor, $params )
    {
        $this->_validateConstructorParams( $vendor, $params ); // Valdiate Params
        
        // set clas variables values
        $this->vendor     = $vendor;

        // Download the Feed
        $this->_loadXML( $vendor, $params );
    }

    /**
     * Valdiate Params
     * @param Vendor $vendor
     * @param array $params
     */
    private function _validateConstructorParams( $vendor, $params )
    {
        if( !( $vendor instanceof Vendor ) || !isset( $vendor[ 'id' ] ) )
        {
            throw new BarcelonaBaseMapperException( 'Invalid Vendor Passed to BarcelonaBaseMapper Constructor.' );
        }

        if( !isset( $params['curl']['classname'] ) || !isset( $params['curl']['src'] ) || !isset( $params['type'] ) )
        {
            throw new BarcelonaBaseMapperException( 'Invalid Params Passed to BarcelonaBaseMapper Constructor.' );
        }
    }

    private function _loadXML( $vendor, $params )
    {
        // Download
        $curlInstance = new $params['curl']['classname']( $params['curl']['src'] );
        $curlInstance->exec();

        new FeedArchiver( $vendor, $curlInstance->getResponse(), $params['type'] );
        
        // Load XML
        $this->xml = simplexml_load_string( $curlInstance->getResponse() );
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

class BarcelonaBaseMapperException extends Exception{}