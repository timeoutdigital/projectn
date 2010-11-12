<?php
/**
 * Beijing Import Base Mapper
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

class BeijingFeedBaseMapper extends DataMapper
{
    /**
    * @var Vendor
    */
    protected $vendor;

    /**
     * @var xmlNodes
     */
    protected $xmlNodes;

    /**
     * @var params
     */
    protected $params;

    /**
     * Create Base Mapper
     * @param PDO $pdoDB (Connected object)
     * @param geocoder $geocoderr
     */
    public function  __construct( Vendor $vendor, $params ) {

        if( !isset( $vendor ) || !$vendor )
        {
            throw new Exception( 'Vendor not found.' );
        }

        // Validate Params
        if( is_null($params) || !is_array($params) || count($params) <= 0 )
        {
            throw new Exception( 'Parameter Required' );
        }

        // @todo: create Exception Base an throw all error in one Exception
        // validate datasource
        if( !isset( $params['datasource'] ) || !is_array( $params['datasource'] ) || count( $params['datasource'] ) <= 0 )
        {
            throw new Exception( 'Parameter missing datasource configuration' );
        }

        if( !isset( $params['datasource']['classname'] )  || empty( $params['datasource']['classname'] ) )
        {
            throw new Exception( 'Parameter missing datasource ClassName' );
        }

        if( !isset( $params['datasource']['url'] )  || empty( $params['datasource']['url'] ) )
        {
            throw new Exception( 'Parameter missing datasource URL' );
        }

        if( !isset( $params['datasource']['username'] )  || empty( $params['datasource']['username'] ) )
        {
            throw new Exception( 'Parameter missing datasource username' );
        }
        
        if( !isset( $params['datasource']['password'] )  || empty( $params['datasource']['password'] ) )
        {
            throw new Exception( 'Parameter missing datasource password' );
        }

        $this->vendor       = $vendor; // Set Vendor
        $this->params       = $params;

        // Fetch the Data
        $this->getXMLFeedData();
    }

    protected function fixHtmlEntities( $string )
    {
        $string = html_entity_decode( (string) $string, ENT_QUOTES, 'UTF-8' );

        return $string;
    }

    protected function roundNumberOrReturnNull( $string )
    {
        return is_numeric( (string) $string ) ? round( (string) $string ) : null;
    }

    protected function extractTimeOrNull( $string )
    {
        $date = DateTime::createFromFormat( 'H:i', $string );

        return ( $date === false ) ? null : $string;
    }

    protected function clean( $string , $chars = '' )
    {
        return stringTransform::mb_trim( $string, $chars );
    }

    /**
     * Beijing chinese feed require authentication before you can download the Feed,
     * authentication is done through screen Scraping login page and submiting with Login value.
     * curl class is utilized to do this and the feed download page checking authentication by cookie,
     * it is important to pass a cookie file
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

    /**
     * Validate the Response after authentication, Require to Override,
     * default allways return fasle!
     * @param string $response
     * @return boolean
     */
    protected function isValidResponse( $response )
    {
        return false;
    }
}