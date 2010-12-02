<?php
/**
 * China City Feed Import Base Mapper
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

class ChinaFeedBaseMapper extends DataMapper
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
     * Base mapper contructor
     * @param Vendor $vendor
     * @param array $params
     */
    public function  __construct( Vendor $vendor, $params )
    {
        $this->_validateParams( $vendor, $params );
        
        // Set local variables
        $this->vendor = $vendor;
        $this->params = $params;


        $this->_loadXML();
    }

    /**
     * Validate Parameters passed in Constructor
     * @param Vendor $vendor
     * @param array $params
     */
    
    private function _validateParams( $vendor, $params )
    {
        if( !$vendor )
            throw new ChinaFeedBaseMapperException( 'Invalid vendor object' );

        if( !is_array( $params ) || empty( $params ) )
            throw new ChinaFeedBaseMapperException ( 'Invalid Parameter' );

        // Validate Params
        if( !isset( $params['datasource']['classname'] ) || empty( $params['datasource']['classname'] ) )
            throw new ChinaFeedBaseMapperException ( 'Invalid datasource::classname ' );

        if( !isset( $params['datasource']['src'] ) || empty( $params['datasource']['src'] ) )
            throw new ChinaFeedBaseMapperException ( 'Invalid datasource::src ' );

        if( !isset( $params['datasource']['xmlsrc'] ) || empty( $params['datasource']['xmlsrc'] ) )
            throw new ChinaFeedBaseMapperException ( 'Invalid datasource::xmlsrc ' );

        if( !isset( $params['datasource']['username'] ) || empty( $params['datasource']['username'] ) )
            throw new ChinaFeedBaseMapperException ( 'Invalid datasource::username ' );

        if( !isset( $params['datasource']['password'] ) || empty( $params['datasource']['password'] ) )
            throw new ChinaFeedBaseMapperException ( 'Invalid datasource::password ' );
    }
    
    /**
     * Download the XML feed and Store it in $this->xmlNodes variable
     */
    protected function _loadXML()
    {
        $formScraper = new $this->params['datasource']['classname']( $this->params['datasource']['src'] );
        $formScraper->doFormPageRequest();
        
        // Get form Fields to manipulate
        $formFields = $formScraper->getFormFields();
        $formFields['Login2$UserName'] = $this->params['datasource']['username'];
        $formFields['Login2$Password'] = $this->params['datasource']['password'];
        $formFields['__EVENTTARGET'] = 'Login2$LoginButton';
        $formFields['__EVENTARGUMENT'] = '';

        // Post form with ameded Data to get response back
        $formScraper->doPostBack( $formFields );

        // Chinese vendor respond with a link to Download their XML feed, which is static and
        // only updated when login Invoked. Hence we will be checking their Header for status 200 (which will be checked by Curl class)
        // and makesure that Last modified date == today's date

        // Now download the generated feed using static URL
        $formScraper = new $this->params['datasource']['classname']( $this->params[ 'datasource' ]['xmlsrc'] );
        $formScraper->doGetRequest();
        
        // Check modified date to confirm Login Sucess and Feed touched Today
        $modifiedDate = $formScraper->getHeaderField( 'Last-Modified' );
        if( date('Y-m-d', strtotime( $modifiedDate ) )  !== date( 'Y-m-d' ) )
        {
            throw new ChinaFeedBaseMapperException( "Feed is out-dated! Possibily login failed or feed failed to generate today. Feed last modified date: {$modifiedDate}" );
        }

        new FeedArchiver( $this->vendor, $formScraper->getResponse(), $this->params['type']);
        
        $this->xmlNodes = simplexml_load_string( $formScraper->getResponse() );
    }

    /**
     *
     * @param <type> $string
     * @return <type>
     */
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
        $date = DateTime::createFromFormat( 'H:i:s', $string );

        return ( $date === false ) ? null : $string;
    }

    protected function clean( $string , $chars = '' )
    {
        return stringTransform::mb_trim( $string, $chars );
    }

    /**
     *
     * @param Doctrine_Record $model
     * @param SimpleXMLElement $xmlNode
     */
    protected function extractCategory( $model, $xmlNode)
    {
        $categoryArray;
        foreach( $xmlNode->categories->category as $parentCategory )
        {
            $categoryArray = array(); // Reset

            // Adding parent category to Array
            $categoryArray[] = $this->clean( (string) $parentCategory->name );

            // Look for Any child category and Add them to The list
            if( isset( $parentCategory->children ) )
            foreach ( $parentCategory->children as $childCategory )
            {
                if( $this->clean( (string)$childCategory->category->name ) != '' )
                {
                    $categoryArray[] = $this->clean( (string)$childCategory->category->name );
                }
            }

            // addVendorCategory will Implode the array into | seperated Value
            // hence we add Each Top level category with its child
            $model->addVendorCategory( $categoryArray, $this->vendor['id'] );

        }
    }
    
}

class ChinaFeedBaseMapperException extends Exception{ }