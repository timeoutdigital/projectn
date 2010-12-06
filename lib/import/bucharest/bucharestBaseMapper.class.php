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
        $this->_validateParameters();

        $this->vendor = $vendor;
        $this->params = $params;

        $this->_loadXML();
    }

    /**
     * Validate parameters passed in constructor
     * @param Vendor $vendor
     * @param array $params
     */
    private function _validateParameters( Vendor $vendor, array $params )
    {
        if( !$vendor || gettype( $vendor ) !== 'Vendor' )
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
}

class BucharestBaseMapperException extends Exception{}