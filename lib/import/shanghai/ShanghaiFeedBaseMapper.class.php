<?php
/**
 * Shanghai Import Base Mapper
 *
 * @package projectn
 * @subpackage
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */
class ShanghaiFeedBaseMapper extends DataMapper
{
    /**
     * Store Vendor object for Mapper use
     * @var Vendor
     */
    protected $vendor;

    /**
     * Store Array Values
     * @var array
     */
    protected $params;

    /**
     * Store Loaded SimpleXML data
     * @var SimpleXML
     */
    protected $xmlNodes;

    /**
     * Exception class to be thrown
     * @var Exception
     */
    protected $exceptionClass = 'ShanghaiBaseMapperException'; // default ShanghaiBaseMapperException
    /**
     * Shanghai Movie Mapper
     * @param Vendor $vendor
     * @param array $params
     */
    public function  __construct( Vendor $vendor, $params ) {

        if( !$vendor )
            throw new $this->exceptionClass( 'Invalid vendor object' );

        if( !is_array( $params ) || empty( $params ) )
            throw new $this->exceptionClass ( 'Invalid Parameter' );

        // Validate Params
        if( !isset( $params['datasource']['classname'] ) || empty( $params['datasource']['classname'] ) )
            throw new $this->exceptionClass ( 'Invalid datasource::classname ' );

        if( !isset( $params['datasource']['url'] ) || empty( $params['datasource']['url'] ) )
            throw new $this->exceptionClass ( 'Invalid datasource::url ' );

        if( !isset( $params['datasource']['username'] ) || empty( $params['datasource']['username'] ) )
            throw new $this->exceptionClass ( 'Invalid datasource::username ' );
        
        if( !isset( $params['datasource']['password'] ) || empty( $params['datasource']['password'] ) )
            throw new $this->exceptionClass ( 'Invalid datasource::password ' );

        // Set local variables
        $this->vendor = $vendor;
        $this->params = $params;


        $this->getXMLFeedData();
    }

    /**
     * Download the XML feed and Store it in $this->xmlNodes variable
     */
    protected function getXMLFeedData()
    {
        $formScraper = new $this->params['datasource']['classname']( $this->params['datasource']['url'] );
        
        // Get form Fields to manipulate
        $formFields = $formScraper->getFormFields();
        $formFields['Login2$UserName'] = $this->params['datasource']['username'];
        $formFields['Login2$Password'] = $this->params['datasource']['password'];
        $formFields['__EVENTTARGET'] = 'Login2$LoginButton';
        $formFields['__EVENTARGUMENT'] = '';

        // Post form with ameded Data to get XML response! hopefully
        $formScraper->doPostBack( $formFields );

        $this->xmlNodes = simplexml_load_string( $formScraper->getResponse() );
    }
}

// Base Mapper Exception
class ShanghaiBaseMapperException extends Exception
{

}