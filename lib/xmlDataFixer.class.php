<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class xmlDataFixer
{
    private $xmlStringData;

    /**
     * Requires XML String
     * @param string $fileData
     */
    public function  __construct( $fileData ) {

        if( !is_string( $fileData ) )
        {
            throw new Exception( 'xmlDataFixer::__construct Invalid file data, it should raw string contents' );
        }

        $this->xmlStringData    = $fileData;
    }

    /**
     * Get SimpleXMLElement with all the Fixes
     * @return SimpleXMLElement
     */
    public function getSimpleXML()
    {
        return simplexml_load_string( $this->xmlStringData );
    }


    /**
     * Add Root element to XML String Data
     * @param string $rootElementName
     */
    public  function addRootElement( $rootElementName = 'root' )
    {
        if( !is_string( $rootElementName ) )
        {
            throw new Exception ( 'xmlDataFixer::addRootElement rootElementName should be in string' );
        }

        $rawXmlStringData       = $this->xmlStringData;

        // Match Header
        mb_ereg( '^<\?.*\?>', $this->xmlStringData, $matches );

        // remove header
        $header                 = null;
        if( is_array( $matches ) && count( $matches ) > 0 )
        {
            $rawXmlStringData   = mb_ereg_replace( preg_quote($matches[0]), '', $rawXmlStringData);
            $header             = $matches[0];
        }

        $this->xmlStringData   = sprintf( '%s <%s> %s </%s>', $header, $rootElementName, $rawXmlStringData, $rootElementName );
        
    }

    /**
     * Filter XMLString data using XSLT Template and get Results as SimpleXMLElement
     * @param string $xsltTemplate
     * @return SimpleXMLElement
     */
    public function getSimpleXMLUsingXSLT( $xsltTemplate )
    {
        $xsl = new DOMDocument();

        $xsl->loadXML( $xsltTemplate );

        $xslProcessor = new XSLTProcessor();

        $xslProcessor->importStyleSheet( $xsl );

        return new SimpleXMLElement( $xslProcessor->transformToXML( dom_import_simplexml( $this->getSimpleXML() ) ) );
    }

    /**
     * Filter all HTML encoded entities from feed data and replace with &amp;
     */
    public function removeHtmlEntiryEncoding()
    {
        //$ignore = '(laquo|raquo|nbsp|amp|lt|gt|quot|apos|#[0-9]+);'; //ndash|rsquo|lsquo|
        $ignore = '(ndash|rsquo|lsquo|laquo|raquo|nbsp|amp|lt|gt|quot|apos|#[0-9]+);';
        $this->xmlStringData = preg_replace( "/&(?!$ignore)/", '&amp;', $this->xmlStringData );
       // $this->xmlStringData = mb_ereg_replace( '\&#146;', '', $this->xmlStringData);
    }

    /**
     * Get Nodes of given Xpath in $XML/getSimpleXML()
     * @param string $nodePath
     * @param SimpleXMLElement $xml
     * @return Array
     */
    public function getXMLNodesByPath( $nodePath, SimpleXMLElement $xml = null )
    {
        if( !$xml )
            $xml = $this->getSimpleXML();

        return $xml->xpath( $nodePath );
    }

    /**
     * Encode String data to UTF-8 using utf8_encode()
     */
    public function encodeUTF8()
    {
        $this->xmlStringData = utf8_encode($this->xmlStringData);
    }

    /**
     * This method will remove the MS Word Html tags
     * (font|span|del|ins|w:|link|meta|xml|style)
     */
    public function removeMSWordHtmlTags()
    {
        $this->xmlStringData = mb_ereg_replace("<(/)?(font|span|del|ins|w:|link|meta|xml|style)[^>]*>", "", $this->xmlStringData );
    }
   
}
?>
