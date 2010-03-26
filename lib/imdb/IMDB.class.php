<?php
/**
 * Represents an IMDB Movie
 *
 * @package projectn
 * @subpackage lib/imdb
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class IMDB 
{
    static private $noMatches = '<b>No Matches.</b>';
    static private $instance;

    /**
     * Uses IMDB search to find a movie
     * @param string $title
     * @return IMDBMovie
     */
    static public function findMovieByTitle( $title )
    {
      $resultsHtml = self::search( array( 's' => 'tt', 'q' => $title ) );

      if( self::hasMatchIn( $resultsHtml ) )
      {
        return IMDBMovie::fromSearchResultHtml( $resultsHtml );
      }
    }

    private function __construct()
    {
    }

    /**
     * @param Curl curl
     */
    static public function setCurl( Curl $curl )
    {
      self::$curl = $curl;
    }

    static private function search( $params )
    {
      $url  = 'http://www.imdb.com/find';

      $curl = new Curl( $url, $params );
      $curl->exec();

      $response   = $curl->getResponse();
      $lineBreaks = array( "\r\n", "\n", "\r" );
      $response   = str_replace( $lineBreaks, '', $response );
      return $response;
    }

    static private function hasMatchIn( $resultsHtml )
    {
      $gotNoMatchInResults = preg_match( '`'.self::$noMatches.'`', $resultsHtml );

      if( $gotNoMatchInResults )
        return false;
      else
        return true;
    }
}
?>
