<?php
/**
 * Chicago Feed Base mapper
 *
 * @package projectn
 * @subpackage
 *
 * @author Rajeevan kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */
class ChicagoFeedBaseMapper extends DataMapper
{
    protected $params;

    /**
    * @var Vendor
    */
    protected $vendor;

    protected $vendorID;

    /**
    * @var SimpleXMLElement or Array of XML Nodes ( using xpath() )
    */
    protected $xml;

   /**
    *
    * @param Doctrine_Record $vendor
    * @param SimpleXMLElement $xml
    * @param geoEncode $geoEncoder
    */
    public function __construct( Doctrine_Record $vendor, $params )
    {

        if( !$vendor )
        {
            // Find vendor
            $vendor = Doctrine::getTable('Vendor')->findOneByCityAndLanguage( 'chicago', 'en-US' );
        }
        
        if( !isset( $vendor ) || !$vendor )
          throw new Exception( 'ChicagoFeedBaseMapper:: Vendor not found' );

        // Set data
        $this->params               = $params;
        $this->vendor               = $vendor;
        $this->vendorID             = $vendor['id'];
    }

    /**
     * Get Nodes of given Xpath in $XML
     * @param string $nodePath
     * @param SimpleXMLElement $xml
     * @return Array
     */
    protected function getXMLNodesByPath( $nodePath, SimpleXMLElement $xml = null )
    {
        if( !$xml )
            $xml = $this->xml;

        return $xml->xpath( $nodePath );
    }

    /**
     * HTML entity decode
     * @param string $string
     * @return string
     */
    protected function fixHtmlEntities( $string )
    {
        $string     = html_entity_decode( (string) $string, ENT_QUOTES, 'UTF-8' );   
        return $string;
    }

    /**
     * This is create for Chicago Bars / Clubs and Eating / Dining... Data have Weird characters that need to be cleaned before pharsing as XML
     * @return XML Element after Cleaning
     */
    protected function ftpGetDataAndCleanData( $requireCleaning = true )
    {
        $ftpClientObj = new $this->params['ftp']['classname']( $this->params['ftp']['ftp'], $this->params['ftp']['username'], $this->params['ftp']['password'], $this->vendor[ 'city' ] );

        echo 'Downloading Chicago Feed ' . PHP_EOL;
        $fileNameString = $ftpClientObj->fetchLatestFileByPattern( $this->params['ftp']['file'] );

        $contents = file_get_contents( $fileNameString );
        
        // Clean if Flagged
        if( $requireCleaning )
        {
            mb_internal_encoding("UTF-8");
            mb_regex_encoding("UTF-8");


            $contents = mb_ereg_replace( "[^\x9\xA\xD\x20-\x7E\xA0-\xFF]", "", $contents );
        }

        $this->xml = simplexml_load_string( $contents );
    }

    /**
     * Convert new Lines into Array of strings
     * @param string $string
     * @return Array
     */
    protected function nl2Array( $string )
    {
        $stringArray = explode( PHP_EOL , $string );
        
        return ( is_array( $stringArray) && count( $stringArray >= 1) ) ? $stringArray : array( $string );
    }
}
?>
