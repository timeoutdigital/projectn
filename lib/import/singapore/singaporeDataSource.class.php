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
    private $downloadURL;

    public function __construct( $type, $url, $curlClass = 'Curl' )
    {
        parent::__construct( $type );

        if( empty($url) )
        {
            throw new Exception('No Download URL provided?');
        }
        
        // set Curl Class
        $this->curlClass    = ( !is_string( $curlClass ) ) ? 'Curl' : $curlClass;
        
        // Override URLs
        $this->downloadURL  = $url;

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

        // Use Curl to download the List file, which lincking to detailed individual Nodes
        $feedObj = new $this->curlClass( $this->downloadURL );
        $feedObj->exec();

        // Fix UTF8 Encoding Issue
        $xmlDataFixer = new xmlDataFixer( $feedObj->getResponse() );
        $xmlDataFixer->encodeUTF8(); //#667 Fix Failing import
        //
        // Convert it to SimpleXML
        $xml = $xmlDataFixer->getSimpleXML();

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
                $model              = null; // Create null model for movie!
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
                    unset( $model );
                    continue; // Skip if Found!
                }
            }
            
            $detailsFeedObj = new $this->curlClass( (string) $item->link );
            $detailsFeedObj->exec();

            $nodeXML = $this->getNodeAsString( $detailsFeedObj->getResponse() );
            $nodes [] = $nodeXML ;
        }

        $dataImploded = implode( '',$nodes );
        // Add Root Element to this Detailed Node collection
        $xmlDataFixer = new xmlDataFixer( $dataImploded );
        $xmlDataFixer->addRootElement();
        $xmlDataFixer->encodeUTF8(); //#667 Fix Failing import
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