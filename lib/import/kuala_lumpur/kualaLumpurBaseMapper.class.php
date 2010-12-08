<?php
/**
 * Kuala Lumpur base mapper
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

class kualaLumpurBaseMapper extends DataMapper
{
    /**
     * vendor object
     * @var Vendor
     */
    protected $vendor;
    /**
     * XML Nodes
     * @var SimpleXMLElement
     */
    protected $xmlNodes;
    /**
     * Parameter $params from constructor
     * @var array
     */
    protected $params;
    /**
     * helper class
     * @var ProjectNDataMapperHelper
     */
    protected $dataMapperHelper;


    /**
     * Kuala Lumput base mapper
     * @param Vendor $vendor
     * @param array $params
     */
    public function  __construct( Vendor $vendor, array $params)
    {
        $this->_validateParameters( $vendor, $params );
        $this->_loadXML( $vendor, $params );

        $this->vendor = $vendor;
        $this->params = $params;
        $this->dataMapperHelper = new ProjectNDataMapperHelper( $vendor );

    }

    /**
     * Validate constructor parameter
     * @param Vendor $vendor
     * @param array $array
     */
    private function _validateParameters( Vendor $vendor, array $array )
    {
        if( !$vendor || ($vendor instanceof Vendor) === false )
        {
            throw new KualaLumpurBaseMapperException( "Invalid Vendor in constructor parameter" );
        }

        if( !is_array( $params ) || empty( $params ) )
        {
            throw new KualaLumpurBaseMapperException( "params parameter constructor should be an array and not empty" );
        }

        if( !isset( $params['curl'] ) || !is_array( $params['curl'] ) || empty( $params['curl'] ) )
        {
            throw new KualaLumpurBaseMapperException( "Invalid params[curl] in constructor parameter" );
        }
        if( !isset( $params['curl']['classname'] ) || !empty ( $params['curl']['classname'] ) )
        {
            throw new KualaLumpurBaseMapperException( "Invalid params[curl][classname] in constructor parameter, expecting Curl class" );
        }
        if( !isset( $params['curl']['src'] ) || !empty ( $params['curl']['src'] ) )
        {
            throw new KualaLumpurBaseMapperException( "Invalid params[curl][src] in constructor parameter, expecting valid source URL" );
        }        
    }

    /**
     * Download XML from URL and convert it as SimpleXML
     * @param Vendor $vendor
     * @param array $array
     */
    private function _loadXML( Vendor $vendor, array $array )
    {
        $curl = new $this->params['curl']['classname']( $this->params['curl']['src'] );
        $curl->exec();

        new FeedArchiver( $vendor, $curl->getResponse(), $params['type'] );
        $this->xmlNodes = simplexml_load_string( $curl->getResponse() );
    }
}

class KualaLumpurBaseMapperException extends Exception { }