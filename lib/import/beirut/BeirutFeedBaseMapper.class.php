<?php
/**
 * Base mapper for Beirut import mappers
 *
 * @package projectn
 * @subpackage lib.import
 *
 * @author Rajeevan kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */
class BeirutFeedBaseMapper extends DataMapper
{
    protected $vendor;
    protected $xmlNodes;
    protected $params;

    public function  __construct( vendor $vendor, array $params )
    {
        // validate parameters and load xml
        $this->_validateParameters( $vendor, $params );
        $this->_loadXML( $params );

        $this->vendor = $vendor;
        $this->params = $params;
    }

    /**
     * Validate Parameters passed in Constructor
     * @param Vendor $vendor
     * @param array $params
     */
    private function _validateParameters( $vendor, $params )
    {
        if( !is_array( $params ) )
        {
            throw new BeirutFeedBaseMapperException( 'Invalid parameters ($params) passed to constructor, Extecting an array()' );
        }

        if( !$vendor || !isset( $vendor['id'] ) )
        {
            throw new BeirutFeedBaseMapperException( 'Invalid vendor object in Parameters' );
        }

        if( !isset( $params['curl'] ) || !isset($params['curl']['classname']) || empty( $params['curl']['classname'] ) )
        {
            throw new BeirutFeedBaseMapperException( 'Parameters missing curl:classname' );
        }

        if( !isset( $params['curl']['src']) || empty( $params['curl']['src'] ))
        {
            throw new BeirutFeedBaseMapperException( 'Parameters missing curl:src' );
        }
    }

    private function _loadXML( $params )
    {
        $curl =  new $params['curl']['classname']( $params['curl']['src'] );
        $curl->exec();

        new FeedArchiver( $this->vendor, $curl->getResponse(), $params['type']);
        $this->xmlNodes = simplexml_load_string( $curl->getResponse() );
    }

    /**
     * Apply any general cleaning required for beirut Feed here
     * @param string $string
     * @return string
     */
    protected function clean( $string )
    {
        return stringTransform::mb_trim( $string );
    }

    protected function roundNumberOrNull( $string )
    {
        if( !is_numeric( $string ) )
        {
            return null;
        }

        return intval( $string );
    }

    /**
     * Extract and add all vendor categories to Model
     * @param Doctrine_Record $model
     * @param SimpleXMLElement $xmlNode
     * @return null
     */
    protected function addVendorCategory( Doctrine_Record $model, SimpleXMLElement $xmlNode )
    {
        if( !isset( $xmlNode->categories->category ) )
        {
            return;
        }

        foreach( $xmlNode->categories->category as $category )
        {
            $categoryArray = array();

            $categoryArray[] = $this->clean( $category->name );

            if( isset( $category->children) )
            {
                foreach ( $category->children as $childCategory )
                {
                    if( $this->clean( (string)$childCategory->category->name ) != '' )
                    {
                        $categoryArray[] = $this->clean( (string)$childCategory->category->name );
                    }
                }
            } // If childrens

            $model->addVendorCategory( $categoryArray, $model['Vendor']['id'] );
        }
    }
}

class BeirutFeedBaseMapperException extends Exception {}