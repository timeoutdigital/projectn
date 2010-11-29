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
    /**
     * String format Contents of XML File for processing
     * @var string
     */
    private $xmlStringData;

    /**
     * Tag name for preg_replace_callback in  htmlEntitiesTag
     * @var string
     */
    private $preg_tag_name;

    /**
     * Requires XML String
     * @param string $fileData
     */
    public function  __construct( /* string */ $xmlString ) {

        if( !is_string( $xmlString ) )
        {
            throw new Exception( 'xmlDataFixer::__construct Invalid file data, it should be a string.' );
        }

        $this->xmlStringData = $xmlString;
        $this->trimXmlStringData();
    }

    public function trimXmlStringData()
    {
        $this->xmlStringData = stringTransform::mb_trim( $this->xmlStringData );
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
     * Filter the MS Word html Tags
     * (font|span|del|ins|w:|link|meta|xml|style)
     * @param string $string
     * @return string
     */
    private function filterMSWordHtmlTags( $string )
    {
        $string = mb_ereg_replace("<(/)?(font|span|del|ins|w:|o:|link|meta|xml|style)[^>]*>", "", $string );
        $string = mb_ereg_replace( '<!(?:--[\s\S]*?--\s*)?>\s*', "", $string ); // Removes the <!-- Comments -->

        return $string;
    }

    /**
     * This method will remove the MS Word Html tags
     * Pass in tagName to filter only specific Tag or lave empty for Entire Document
     * Also, Set haveCDATA to TRUE if this tag content is wrapped in CDATA
     * @param string $tagName
     * @param boolean $haveCDATA
     */
    public function removeMSWordHtmlTags( $tagName = '', $haveCDATA = true)
    {
        if( is_string($tagName) && trim($tagName) != '' )
        {
            // Preg_match tags and Clean the contents for Word HTML Tags
            $this->preg_match_tag( 'preg_callback_msword', $tagName, $haveCDATA );
        }else{
            // Else Filter Entire Document!
            $this->xmlStringData = $this->filterMSWordHtmlTags( $this->xmlStringData );
        }
    }

    /**
     * This function will Search and Replace the contents inside given tag with htmlentities encoded contents +
     * wrap with CDATA
     * @param string $tagName
     * @param boolean $haveCDATA
     */
    public function htmlEntitiesTag( $tagName, $haveCDATA = false)
    {
        $this->preg_match_tag( 'preg_callback_htmlEntities', $tagName, $haveCDATA );
    }

    /**
     * Match tags and Execute the Callback
     * @param string $callback
     * @param string $tagName
     * @param boolean $haveCDATA
     */
    private function preg_match_tag( $callback, $tagName, $haveCDATA = false )
    {
        $cdata_start = ( $haveCDATA ) ? '<\!\[CDATA\[' : '';
        $cdata_end = ( $haveCDATA ) ? '\]\]>' : '';
        $this->preg_tag_name = $tagName; 
        // Match and Process Matches
        $this->xmlStringData = preg_replace_callback('#<'.$tagName.'>'.$cdata_start.'(.*)'.$cdata_end.'</'.$tagName.'>#iUs', array( &$this, $callback) , $this->xmlStringData );
        
        unset( $this->preg_tag_name , $cdata_start, $cdata_end );
    }

    /**
     * matched will be passed here when used with preg_replace_callback in htmlEntitiesTag()
     * @param array $input
     * @return string
     */
    private function preg_callback_htmlEntities( $input )
    {
        if( count( $input ) < 2 )
            return '<'.$this->preg_tag_name.'><![CDATA[]]></'.$this->preg_tag_name.'>';
        
        return sprintf('<'.$this->preg_tag_name.'><![CDATA[%s]]></'.$this->preg_tag_name.'>', htmlentities( $input[1] ) );
    }

    /**
     * matched will be cleaned for MS HTML tags
     * @param array $input
     * @return string
     */
    private function preg_callback_msword( $input )
    {
        if( count( $input ) < 2 )
            return '<'.$this->preg_tag_name.'><![CDATA[]]></'.$this->preg_tag_name.'>';

        return sprintf('<'.$this->preg_tag_name.'><![CDATA[%s]]></'.$this->preg_tag_name.'>', $this->filterMSWordHtmlTags( $input[1] ) );
    }

   
}
?>
