<?php
/**
 * UAE Feed Base Mapper
 *
 * @package projectn
 * @subpackage
 *
 * @author Peter Johnson <peterjohnson@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */
class UAEFeedBaseMapper extends DataMapper
{
    protected $vendor_id;
    public $xml;

    public function  __construct( $vendor, $params )
    {
        $this->_validateConstructorParams( $vendor, $params );
        $this->_loadXML( $vendor, $params );
        
        $this->vendor_id = $vendor['id'];
    }

    private function _loadXML( $vendor, $params )
    {
        $curlInstance = new $params['curl']['classname']( $params['curl']['src'] );
        $curlInstance->exec();

        new FeedArchiver( $vendor, $curlInstance->getResponse(), $params['type'] );
        
        $xmlDataFixer = new xmlDataFixer( $curlInstance->getResponse() );

        $this->xml = isset( $params['curl']['xslt'] )
            ? $xmlDataFixer->getSimpleXMLUsingXSLT( file_get_contents( sfConfig::get( 'projectn_xslt_dir' ) . '/' . $params['curl']['xslt'] ) )
            : $xmlDataFixer->getSimpleXML();
    }

    private function _validateConstructorParams( $vendor, $params )
    {
        if( !( $vendor instanceof Vendor ) || !isset( $vendor[ 'id' ] ) )
        {
            throw new UAEMapperException( 'Invalid Vendor Passed to UAEFeedBaseMapper Constructor.' );
        }

        if( !isset( $params['curl']['classname'] ) || !isset( $params['curl']['src'] ) || !isset( $params['type'] ) )
        {
            throw new UAEMapperException( 'Invalid Params Passed to UAEFeedBaseMapper Constructor.' );
        }
    }
    
}

class UAEMapperException extends Exception {}