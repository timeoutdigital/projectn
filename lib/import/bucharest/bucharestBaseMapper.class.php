<?php
/**
 * Bucharest Base mapper
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Rajeevan kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class bucharestBaseMapper extends DataMapper
{
    /**
     * Holds vendor object
     * @var Vendor
     */
    protected $vendor;
    /**
     * Holding Parameter array from constructor
     * @var array
     */
    protected $params;
    /**
     * Holds XML nodes for this mapper
     * @var SimpleXMLElement
     */
    protected $xmlNodes;

    /**
     * Base mapper for Bucharest
     * @param Vendor $vendor
     * @param array $params
     */
    public function  __construct( Vendor $vendor, array $params )
    {
        $this->_validateParameters( $vendor, $params );

        $this->vendor = $vendor;
        $this->params = $params;

        $this->_loadXML( $vendor, $params );
    }

    /**
     * Validate parameters passed in constructor
     * @param Vendor $vendor
     * @param array $params
     */
    private function _validateParameters( Vendor $vendor, array $params )
    {
        if( !$vendor || ($vendor instanceof Vendor) === false )
        {
            throw new BucharestBaseMapperException( "Invalid Vendor object passed in parameter");
        }

        if( !is_array( $params ) || empty( $params ) )
        {
            throw new BucharestBaseMapperException( "Invalid params, params should be array of parameters");
        }

        // This validation is written based on assumption that Feed will be provided over HTTP
        if( !isset( $params['curl'] ) || empty( $params['curl'] ))
        {
            throw new BucharestBaseMapperException( "Invalid CURL key in params['curl']");
        }
        if( !isset( $params['curl']['classname'] ) || empty($params['curl']['classname'] ) )
        {
            throw new BucharestBaseMapperException( "Invalid Curl classname in params['curl']['classname']");
        }
        if( !isset( $params['curl']['src'] ) || empty($params['curl']['src'] ) )
        {
            throw new BucharestBaseMapperException( "Invalid or missing Source UR: params['curl']['src']");
        }
    }

    /**
     * Download XML feed from URL and Load it as XML into $this->xmlNodes
     * @param Vendor $vendor
     * @param array $params
     */
    private function _loadXML( Vendor $vendor, array $params )
    {
        $curlInstance = new $this->params['curl']['classname']( $this->params['curl']['src'] );
        $curlInstance->exec();
        
        new FeedArchiver( $vendor, $curlInstance->getResponse( ), $params['type'] );
        $this->xmlNodes = simplexml_load_string( $curlInstance->getResponse( ) );
    }

    /**
     * Extract Categories from XMLNode and add them to $model
     * @param SimpleXMLElement $element
     * @return mixed
     */
    protected function addVendorCategories( $model, $element )
    {
        // This is so complicated because it covers the rare event
        // where one parent category has multiple child categories.
        foreach( $element->categories->category as $category )
        {
            $categories = array(); // reset and create categories array
            
            $categoryName = $this->clean( (string) $category->name );

            // Category has No Children
            if( count( $category->children->category ) === 0 )
            {
                $categories[] = $categoryName;
            }else
            {
                foreach( $category->children->category as $subCategory )
                {
                $categories[] = $this->clean( (string) $subCategory->name );
                }
            }

            // add the category to model, it will IMPLODE array into one PIPE separated category
            $model->addVendorCategory( $categories, $model['Vendor']['id'] );

        }
    }
}

class BucharestBaseMapperException extends Exception{}