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
    private $movieUrl   = 'http://www.timeoutsingapore.com/xmlapi/movies/?section=index&full&key=ffab6a24c60f562ecf705130a36c1d1e';

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
        // Get the Singapore vendor
        $vendor = Doctrine::getTable( 'Vendor' )->findOneByCity( 'singapore' );
        if( $vendor == null )
        {
            throw new Exception( 'singapore vendor not found!' );
        }

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
            // #666 Skip Hitting Singapore server when Published Date don't Change
            $url = (string) $item->link;
            $matches = array();

            // Find the ID & Model
            preg_match("/^http:\/\/.*\?(event|venue|movie)=(.*)&(?:amp;)?key=.*$/", $url, $matches); // Output ( [0] => FULL URL, [1] => model_name, [2] => ID )

            if( $matches !== null && count( $matches ) == 3 )
            {
                $itemID             = $matches[2];
                $pubDate            = date("Y-m-d H:i:s" , strtotime( (string) $item->pubDate ) );
                // switch for Model
                switch ( strtolower($matches[1]) )
                {
                    case 'venue': $model      = Doctrine::getTable( 'Poi' )->findOneByVendorIdAndVendorPoiIdAndReviewDate( $vendor['id'], $itemID, $pubDate );
                        break;
                    case 'event': $model      = Doctrine::getTable( 'Event' )->findOneByVendorIdAndVendorEventIdAndReviewDate( $vendor['id'], $itemID, $pubDate );
                        break;

                }

                // Skip if Already Exists and Not Updated Since lastime seen
                if( $model != null )
                {
                    continue; // Skip if Found!
                }
            }
            
            $detailsFeedObj = new $this->curlClass( (string) $item->link );
            $detailsFeedObj->exec();

            $nodeXML = $this->getNodeAsString( $detailsFeedObj->getResponse() );
            $nodes [] = $nodeXML ;
        }

        $dataImploded = implode( '',$nodes );
        // file_put_contents( sfConfig::get( 'sf_root_dir' ) . '/import/singapore/' . $this->type . '-' . date('dMY') . '.xml.txt', $dataImploded ); // Save for DUBUG
        // Add Root Element to this Detailed Node collection
        $xmlDataFixer = new xmlDataFixer( $dataImploded );
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