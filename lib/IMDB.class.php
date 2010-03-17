<?php
/**
 * @author Fabian Beiner <fabianDOTbeinerATgmailDOTcom>, Clarence Lee <clarencelee@timeout.com>
 * @version 2.1alpha
 *
 * @comment Original idea by David Walsh <davidATdavidwalshDOTname>, thanks! Your blog rocks ;)
 *          I did this script in the middle of the night while being ill, no guarantee for anything!
 *
 * @license http://creativecommons.org/licenses/by-sa/3.0/de/deed.en_US
 *          Creative Commons Attribution-Share Alike 3.0 Germany
 *
 * Yay, after two days IMDB changed their layout... Great! :( Also added a fallback if cURL is missing.
 *
 */

class IMDB 
{
    private $searchTerms;
    private $response;

    function __construct( $searchTerms ) 
    {
        $this->searchTerms = $searchTerms;
        $imdb_content       = $this->doSearchRequest();

        $this->movie        = trim($this->getMatch('|<title>(.*) \((.*)\)</title>|Uis', $imdb_content));
        $this->director     = trim($this->getMatch('|<h5>Director:</h5><a href="(.*)">(.*)</a><br/>|Uis', $imdb_content, 2));
        $this->url_director = trim($this->getMatch('|<h5>Director:</h5><a href="(.*)">(.*)</a><br/>|Uis', $imdb_content));
        $this->plot         = trim($this->getMatch('|<h5>Plot:</h5>(.*) <a|Uis', $imdb_content));
        $this->release_date = trim($this->getMatch('|<h5>Release Date:</h5> (.*) \((.*)\) <a|Uis', $imdb_content));
        $this->mpaa         = trim($this->getMatch('|<h5><a href="/mpaa">MPAA</a>:</h5> (.*)</div>|Uis', $imdb_content));
        $this->run_time     = trim($this->getMatch('|Runtime:</h5>(.*) (.*)</div>|Uis',$imdb_content));
        $this->rating       = trim($this->getMatch('|<div class="meta"><b>(.*)</b>|Uis', $imdb_content));
        $this->votes        = trim($this->getMatch('|&nbsp;&nbsp;<a href="ratings" class="tn15more">(.*) votes</a>|Uis', $imdb_content));
        $this->country      = trim($this->getMatch('|<h5>Country:</h5><a href="(.*)">(.*)</a></div>|Uis', $imdb_content, 2));
        $this->url_country  = trim($this->getMatch('|<h5>Country:</h5><a href="(.*)">(.*)</a></div>|Uis', $imdb_content));
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

    public function getTitle()
    {
      $non_greedy_any = '.*?';
      $regex = ':'.
        '<b>Popular Titles' . $non_greedy_any . //Under Popular Titles
        '1\.'               . $non_greedy_any .  //get the first film
        'href="/title/'     . $non_greedy_any . //inside a link
        '>(.*?)</a>:'
      ;
      $title = $this->getMatch( $regex, $this->response, 1 );
      return $title;
    }

    private function getMatch($regex, $content, $index=1) {
        preg_match($regex, $content, $matches);

        if( empty( $matches ) )
          return null;

        return $matches[$index];
    }
}
?>
