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

    private $venueNodes = array();

    public function __construct( $type )
    {
        parent::__construct( $type );
        $this->downloadFeed();

    }

    protected function fetchVenueXml()
    {
        $feedObj = new Curl( $this->venuesUrl );
        $feedObj->exec();
        $xml = simplexml_load_string( $feedObj->getResponse() );
        foreach ($xml->channel->item as $item)
        {
            $detailsFeedObj = new Curl( (string) $item->link );
            $detailsFeedObj->exec();
            $venueNodes [] = $detailsFeedObj->getResponse();
        }

        $xmlDataFixer = new xmlDataFixer;
    }

    protected function downloadFeed()
    {
      switch ( $this->type )
      {
      	case self::TYPE_EVENT :

      		break;

      	case self::TYPE_POI :
            $this->getVenueNodes();
      		break;

      	case self::TYPE_MOVIE :
            $feedObj = new Curl( $this->moviesUrl );
      		break;

      }
       $feedObj->exec();
       $this->xml = simplexml_load_string( $feedObj->getResponse() );
    }

}