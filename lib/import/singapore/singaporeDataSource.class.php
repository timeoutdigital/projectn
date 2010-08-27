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
    private $venuesUrl  = 'http://www.timeoutsingapore.com/xmlapi/venues/?section=index&full=&key=ffab6a24c60f562ecf705130a36c1d1e';
    private $eventsUrl  = 'http://www.timeoutsingapore.com/xmlapi/events/?section=index&full=&key=ffab6a24c60f562ecf705130a36c1d1e';

    private $venueNodes = array();

    public function __construct( $type )
    {
        parent::__construct( $type );
        //$this->downloadFeed();
        $this->fetchXML();

    }

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
        }

        $feedObj = new Curl( $url );

        $feedObj->exec();

        $xml = simplexml_load_string( $feedObj->getResponse() );
        $i=0;
        foreach ( $xml->channel->item as $item )
        {
            $i++;
            if( $i>1 ) continue;
            $detailsFeedObj = new Curl( (string) $item->link );

            $detailsFeedObj->exec();

            $nodeXML = $this->getNodeAsString( $detailsFeedObj->getResponse() );

            $nodes [] = $nodeXML ;
        }

        $xmlDataFixer = new xmlDataFixer( implode( '',$nodes ) );

        $xmlDataFixer->addRootElement();

        $this->xml = $xmlDataFixer->getSimpleXML();


    }


   /* protected function fetchVenuesXml()
    {
        $venueNodes = array();
        $feedObj = new Curl( $this->venuesUrl );
        $feedObj->exec();
        $xml = simplexml_load_string( $feedObj->getResponse() );

        foreach ($xml->channel->item as $item)
        {
            $detailsFeedObj = new Curl( (string) $item->link );
            $detailsFeedObj->exec();
            $venueXML = $this->getVenueNodeAsString( $detailsFeedObj->getResponse());
            $venueNodes [] = $venueXML ;
        }

        $xmlDataFixer = new xmlDataFixer( implode( '',$venueNodes ) );
        $xmlDataFixer->addRootElement();
        $this->xml = $xmlDataFixer->getSimpleXML();
    }

    protected function fetchEventsXml()
    {
        $eventNodes = array();
        $feedObj = new Curl( $this->eventsUrl );
        $feedObj->exec();
        $xml = simplexml_load_string( $feedObj->getResponse() );

        foreach ($xml->channel->item as $item)
        {
            $detailsFeedObj = new Curl( (string) $item->link );
            $detailsFeedObj->exec();
            $eventXML = $this->getEventNodeAsString( $detailsFeedObj->getResponse() );
            $eventNodes [] = $eventXML ;
        }

        $xmlDataFixer = new xmlDataFixer( implode( '',$eventNodes ) );
        $xmlDataFixer->addRootElement();
        $this->xml = $xmlDataFixer->getSimpleXML();
    }*/

  /*  protected function _fetchVenueXml()
    {

        $feedObj = new Curl(  'http://192.9.1.220/singapore/venue.xml' );
        $feedObj->exec();
        $response = $feedObj->getResponse();
        $venueNodeAsString = $this->getVenueNodeAsString( $response );

        var_dump( $venueNodeAsString );
        die("");
        foreach ($this->xml as $venue)
        {
            print_r( $venue );
        }

    }*/

   /* protected function downloadFeed()
    {
      switch ( $this->type )
      {
      	case self::TYPE_EVENT :
      	    $this->fetchEventsXml();
      		break;

      	case self::TYPE_POI :
            $this->fetchVenuesXml();
      		break;

      	case self::TYPE_MOVIE :
            $feedObj = new Curl( $this->moviesUrl );
      		break;

      }
    }*/




    /**
     * returns the eventNode (<venue.... </venue>)
     *
     * @param string $xmlString
     * @return string
     */
    private function getNodeAsString( $xmlString  )
    {
        $xmlString = str_replace( PHP_EOL, "", $xmlString );

        switch ($this->type)
        {
          	case self::TYPE_EVENT :
          	    $pattern = '/<event>.*?<\/event>/';
          		break;

          	case self::TYPE_POI :
                $pattern = '/<venue>.*?<\/venue>/';
          		break;

          	case self::TYPE_MOVIE :
                $feedObj = new Curl( $this->moviesUrl );
          		break;

        }

        preg_match( $pattern, $xmlString, $matches );

        return ( count( $matches )> 0 ) ? $matches[ 0 ] : '';

    }

}