<?php
/**
 * Singapore Base mapper
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

class singaporeBaseMapper extends DataMapper
{
    /**
     * Vendor object
     * @var Vendor
     */
    protected $vendor;
    /**
     * XML nodes loaded from URL
     * @var SimpleXMLElement
     */
    protected $xmlNodes;
    /**
     * $params Parameter from constructor
     * @var array
     */
    protected $params;

    /**
     * Singapore base mapper
     * @param Vendor $vendor
     * @param array $params
     */
    public function  __construct( Vendor $vendor, array $params )
    {
        $this->_validateParameters( $vendor, $params );
        $this->_loadXML( $vendor, $params );

        $this->vendor = $vendor;
        $this->params = $params;
    }

    /**
     * Validate constructor parameter
     * @param Vendor $vendor
     * @param array $params
     */
    private function _validateParameters( Vendor $vendor, array $params )
    {
        if( !$vendor || ($vendor instanceof Vendor) === false )
        {
            throw new SingaporeBaseMapperException( "Invalid Vendor in constructor parameter" );
        }

        if( !is_array( $params ) || empty( $params ) )
        {
            throw new SingaporeBaseMapperException( "params parameter in constructor should be an array and not empty" );
        }

        if( !isset( $params['datasource'] ) || !is_array( $params['datasource'] ) || empty( $params['datasource'] ) )
        {
            throw new SingaporeBaseMapperException( "Invalid params[datasource] in constructor parameter" );
        }
        if( !isset( $params['datasource']['classname'] ) || empty ( $params['datasource']['classname'] ) )
        {
            throw new SingaporeBaseMapperException( "Invalid params[datasource][classname] in constructor parameter, expecting Curl or DataSource Class class" );
        }
        if( !isset( $params['datasource']['src'] ) || empty ( $params['datasource']['src'] ) )
        {
            throw new SingaporeBaseMapperException( "Invalid params[datasource][src] in constructor parameter, expecting valid source URL" );
        }
    }

    /**
     * Download XML from URL and convert it as SimpleXML
     * @param Vendor $vendor
     * @param array $params
     */
    private function _loadXML( Vendor $vendor, array $params )
    {
        $dataSource = new $params['datasource']['classname']( $params['type'], $params['datasource']['src'] );
        $this->xmlNodes = $dataSource->getXML();
    }
}

class SingaporeBaseMapperException extends Exception { }