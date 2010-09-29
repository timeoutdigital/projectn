<?php
class barcelonaDataSource extends baseDataSource
{
    private $venuesUrl = 'http://projectn-pro.gnuinepath.com/venues.xml';

    private $moviesUrl = 'http://projectn-pro.gnuinepath.com/movies.xml';

    private $eventsUrl = 'http://projectn-pro.gnuinepath.com/events.xml';


    public function __construct( $type  )
    {
        parent::__construct( $type );
        $this->downloadFeed();
    }

    protected function downloadFeed()
    {
      switch ( $this->type )
      {
      	case self::TYPE_EVENT :
            $feedObj = new Curl( $this->eventsUrl );
      		break;

      	case self::TYPE_POI :
            $feedObj = new Curl( $this->venuesUrl );
      		break;

      	case self::TYPE_MOVIE :
            $feedObj = new Curl( $this->moviesUrl );
      		break;

      }
       $feedObj->exec();
       $this->xml = simplexml_load_string( $feedObj->getResponse() );
    }


}

/**
 *
 *
 *
 *
 *
 *   $feedUrl = "http://projectn-pro.gnuinepath.com/events.xml  ";
 *   $feedUrl = "http://projectn-pro.gnuinepath.com/movies.xml  ";
    * "http://projectn-pro.gnuinepath.com/venues.xml
 */