<?php
/**
 * Istanbul DataMapper
 *
 * @package projectn
 * @subpackage
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class istanbulBaseMapper extends DataMapper
{
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
     * @var array
     */
    protected $params;
    
    /**
    *
    * @param SimpleXMLElement $xml
    * @param geoEncode $geoEncoder
    * @param string $city
    */
    public function __construct( Vendor $vendor, $params )
    {
        $this->_validateConstructorParams( $vendor, $params );

        // Set class variable values
        $this->vendor     = $vendor;
        $this->params     = $params;
        
        $this->_loadXML( $vendor, $params );
    }

    protected function fixHtmlEntities( $string )
    {
        $string = html_entity_decode( (string) $string, ENT_QUOTES, 'UTF-8' );
        return $string;
    }

    /**
     * Loop through categories and return category + child category in an array
     * @param SimpleXMLElement $element
     * @return array
     */
    protected function extractCategories( $element )
    {
        // This is so complicated because it covers the rare event
        // where one parent category has multiple child categories.
        $categories = array();
        foreach( $element->categories->category as $category )
        {
            $categoryName = $this->clean( (string) $category->name );

            // Category has No Children
            if( count( $category->children->category ) === 0 )
            {
                $categories[] = $categoryName;
            }else
            {
                foreach( $category->children->category as $subCategory )
                {
                $categories[] = stringTransform::concatNonBlankStrings( " | ", array( $categoryName, $this->clean( (string) $subCategory->name ) ) );
                }
            }

        }
        return array_unique( $categories );
    }

    /**
     * Clean the string using mb_trim()
     * @param string $string
     * @return string
     */
    protected function clean( $string )
    {
        return stringTransform::mb_trim( $string );
    }

    /**
     * Extract Publictransport information from XML Node
     * @param SimpleXMLElement $element
     * @return string
     */
    protected function extractPublicTransportInfo( $element )
    {
        // Public Transport Links
        $publicTransportArray = array();

        foreach ( $element->public_transports as $transportElement )
            $publicTransportArray[]           = $this->clean( (string) $transportElement->public_transport );

        return stringTransform::concatNonBlankStrings( ", ", $publicTransportArray );
    }

    /**
     * Return a valid number or Null value based on string passed as params
     * @param string $string
     * @return mixed
     */
    protected function roundNumberOrReturnNull( $string )
    {
        return is_numeric( (string) $string ) ? round( (string) $string ) : null;
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

    /**
     * Validate Params passed to Constructor
     * @param Vendor $vendor
     * @param array $params
     */
    private function _validateConstructorParams( $vendor, $params )
    {
        if( !( $vendor instanceof Vendor ) || !isset( $vendor[ 'id' ] ) )
        {
            throw new IstanbulBaseMapperException( 'Invalid Vendor Passed to IstanbulBaseMapper Constructor.' );
        }

        if( !isset( $params['curl']['classname'] ) || !isset( $params['curl']['src'] ) || !isset( $params['type'] ) )
        {
            throw new IstanbulBaseMapperException( 'Invalid Params Passed to IstanbulBaseMapper Constructor.' );
        }
    }

    /**
     * Download XML File from Url and put it to protected variable $xml;
     * @param Vendor $vendor
     * @param array $params
     */
    private function _loadXML( $vendor, $params )
    {
        $curlInstance = new $params['curl']['classname']( $params['curl']['src'] );
        $curlInstance->exec();

        new FeedArchiver( $vendor, $curlInstance->getResponse(), $params['type'] );

        $this->xml = simplexml_load_string( $curlInstance->getResponse() );
    }
}

class IstanbulBaseMapperException extends Exception{ }
?>
