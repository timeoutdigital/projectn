<?php
/**
 *
 * singaporeDataSource
 * @package projectn
 * @subpackage singapore.import.lib
 *
 * @author Emre Basala <emrebasala@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 *
 * @version 1.0.0
 *
 *
 */
class singaporeDataSource extends baseDataSource
{
    private $curlClass; // Holds the Class for Curl, bcz, it require mocking when testing
    private $venuesUrl  = 'http://www.timeoutsingapore.com/xmlapi/venues/?section=index&full=&key=ffab6a24c60f562ecf705130a36c1d1e';
    private $eventsUrl  = 'http://www.timeoutsingapore.com/xmlapi/events/?section=index&full=&key=ffab6a24c60f562ecf705130a36c1d1e';
    private $movieUrl   = 'http://www.timeoutsingapore.com/xmlapi/xml_detail/?movie=758&key=ffab6a24c60f562ecf705130a36c1d1e';

    public function __construct( $type, $curlClass = 'Curl', $venueURL= null, $eventURL = null, $movieUrl = null )
    {
        parent::__construct( $type );

        // set Curl Class
        $this->curlClass    = ( !is_string( $curlClass ) ) ? 'Curl' : $curlClass;
        
        // Override URLs
        $this->venuesUrl    = ( is_string( $venueURL ) && trim( $venueURL ) != '' ) ? $venueURL : $this->venuesUrl;
        $this->eventsUrl    = ( is_string( $eventURL ) && trim( $eventURL ) != '' ) ? $eventURL : $this->eventsUrl;
        $this->movieUrl     = ( is_string( $movieUrl ) && trim( $movieUrl ) != '' ) ? $movieUrl : $this->movieUrl;

        // fetch XML from Feed
        $this->fetchXML();

    }

    /**
     * fetch XML feed from URL and generate SimpleXMLElement
     * @return SimpleXMLElement
     */
    protected function fetchXML()
    {
        $nodes = array();

        switch ( $this->type )
        {
            case self::TYPE_EVENT:
                $url = $this->eventsUrl;
                break;

            case self::TYPE_POI:
                $url = $this->venuesUrl;
                break;

            case self::TYPE_MOVIE:
                $url = $this->movieUrl;
                break;
        }

        // Use Curl to download the List file, which lincking to detailed individual Nodes
        $feedObj = new $this->curlClass( $url );
        $feedObj->exec();

        // Convert it to SimpleXML
        $xml = simplexml_load_string( $feedObj->getResponse() );

        // For each links, get the Detailed Nodes and Build Simple XML
        foreach ( $xml->channel->item as $item )
        {
            $detailsFeedObj = new $this->curlClass( (string) $item->link );
            $detailsFeedObj->exec();

            $nodeXML = $this->getNodeAsString( $detailsFeedObj->getResponse() );
            $nodes [] = $nodeXML ;
        }

        // Add Root Element to this Detailed Node collection
        $xmlDataFixer = new xmlDataFixer( implode( '',$nodes ) );
        $xmlDataFixer->addRootElement();
        $this->xml = $xmlDataFixer->getSimpleXML();

    }

    /**
     * returns the eventNode (<venue.... </venue>)
     *
     * @param string $xmlString
     * @return string
     */
    private function getNodeAsString( $xmlString  )
    {
        //$xmlString = str_replace( PHP_EOL, "", $xmlString );

        switch ($this->type)
        {
            case self::TYPE_EVENT :
                $pattern = '/<event>.*?<\/event>/isU';
                break;

            case self::TYPE_POI :
                $pattern = '/<venue>.*?<\/venue>/isU';
                break;

            case self::TYPE_MOVIE :
                $pattern = '/<movie>.*?<\/movie>/isU';
                break;

        }

        preg_match( $pattern, $xmlString, $matches );

        return ( count( $matches )> 0 ) ? $matches[ 0 ] : '';

    }

}