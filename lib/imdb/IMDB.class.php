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
    private $searchTerms;
    private $response;

    static public function searchForMovieByTitle( $title )
    {
      $url    = 'http://www.imdb.com/find';
      $params = array( 
        's' => 'all', 
        'q' => $title
      );

      $curl = new Curl( $url, $params );
      $curl->exec();

      $response = $curl->getResponse();
      $lineBreaks = array( "\r\n", "\n", "\r" );
      $response = str_replace( $lineBreaks, '', $response );
    }

    static private function searchHasDirectMatch( $response )
    {
    }

    function __construct( $searchTerms ) 
    {
        $this->searchTerms = $searchTerms;
        $imdb_content      = $this->doSearchRequest();
    }

    public function getTitle()
    {
      $non_greedy_any = '.*?';
      $regex = 
        ':'.
        '<b>Popular Titles' . $non_greedy_any . //Under Popular Titles
        '1\.'               . $non_greedy_any . //get the first film
        'href="/title/'     . $non_greedy_any . //inside a link
        '>(.*?)</a>'.
        ':'
      ;
      $title = $this->getMatch( $regex, $this->response );
      return $title;
    }

    public function getId()
    {
      $non_greedy_any = '.*?';
      $regex = 
        ':'.
        '<b>Popular Titles' . $non_greedy_any . //Under Popular Titles
        '1\.'               . $non_greedy_any . //get the first film
        'href="/title/(.*?)/"'.
        ':'
      ;
      $title = $this->getMatch( $regex, $this->response );
      return $title;
    }

    private function doSearchRequest()
    {
      $url    = 'http://www.imdb.com/find';
      $params = array( 
        's' => 'all', 
        'q' => $this->searchTerms
      );
      $curl = new Curl( $url, $params );
      $curl->exec();

      $response = $curl->getResponse();
      $lineBreaks = array( "\r\n", "\n", "\r" );
      $this->response = str_replace( $lineBreaks, '', $response );
    }

    private function getMatch($regex, $content, $index=1) {
        preg_match($regex, $content, $matches);

        if( empty( $matches ) )
          return null;

        return $matches[$index];
    }
}
?>
