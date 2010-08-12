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
    /**
    * @var geoEncode
    */
    protected $geoEncoder;

    /**
    * @var Vendor
    */
    protected $vendor;

    protected $vendorID;

    /**
    * @var SimpleXMLElement
    */
    protected $xml;

   /**
    *
    * @param Doctrine_Record $vendor
    * @param SimpleXMLElement $xml
    * @param geoEncode $geoEncoder
    */
    public function __construct( Doctrine_Record $vendor, SimpleXMLElement $xml, geoEncode $geoEncoder = null)
    {

        if( !$vendor )
        {
            // Find vendor
            $vendor = Doctrine::getTable('Vendor')->findOneByCityAndLanguage( 'chicago', 'en-US' );
        }
        
        if( !isset( $vendor ) || !$vendor )
          throw new Exception( 'ChicagoFeedBaseMapper:: Vendor not found' );

        // Set data
        $this->geoEncoder           = is_null( $geoEncoder ) ? new geoEncode() : $geoEncoder;
        $this->vendor               = $vendor;
        $this->xml                  = $xml;
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
     * @param String $fileName
     * @return String File contents after Cleaning
     */
    protected function openAndCleanData( $fileName )
    {
        mb_internal_encoding("UTF-8");
        mb_regex_encoding("UTF-8");
        
        $contents = file_get_contents( $fileName );
        
        return mb_ereg_replace( "[^\x9\xA\xD\x20-\x7E\xA0-\xFF]", "", $contents );
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

    /**
     * Implode New lines into Comma seperated string
     * @param string $string
     * @return string
     */
    protected function nl2Comma( $string )
    {
        $stringArray     = $this->nl2Array( $string );

        return implode(', ', $stringArray );
    }

    /**
     * Search / Replace SemiColon (;) with Comma (,)
     * @param string $string
     * @return string
     */
    protected function semiColon2Comma( $string )
    {
        return mb_ereg_replace(';', ',', $string );
    }
}
?>
