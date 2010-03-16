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
    function __construct($url) {
        $this->gotCurl      = extension_loaded('curl');
        $imdb_content       = $this->imdbHandler($url);
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

    function imdbHandler($input) {
        if (!$this->getMatch('|^http://(.*)$|Uis', $input)) {
            $tmpUrl = 'http://us.imdb.com/find?s=all&q='.str_replace(' ', '+', $input).'&x=0&y=0';
            if ($this->gotCurl) {
                $ch      = curl_init();
                curl_setopt_array($ch, array(CURLOPT_URL => $tmpUrl,
                                             CURLOPT_HEADER => false,
                                             CURLOPT_RETURNTRANSFER => true,
                                             CURLOPT_TIMEOUT => 10
                                            )
                                  );
                $data = curl_exec($ch);
                curl_close($ch);
            } else {
                $data = file_get_contents($tmpUrl);
            }
            $foundMatch = $this->getMatch('|<p style="margin:0 0 0.5em 0;"><b>Media from&nbsp;<a href="(.*)">(.*)</a> ((.*))</b></p>|Uis', $data);
            if ($foundMatch) {
                $this->url = 'http://www.imdb.com'.$foundMatch;
            } else {
                $this->url = '';
                return 0;
            }
        } else {
            $this->url = $input;
        }
        if ($this->gotCurl) {
            $ch      = curl_init();
            curl_setopt_array($ch, array(CURLOPT_URL => $this->url,
                                         CURLOPT_HEADER => false,
                                         CURLOPT_RETURNTRANSFER => true,
                                         CURLOPT_TIMEOUT => 10
                                        )
                              );
            $data = curl_exec($ch);
            curl_close($ch);
        } else {
            $data = file_get_contents($this->url);
        }
        return str_replace("\n",'',(string)$data);
    }

    function getMatch($regex, $content, $index=1) {
        preg_match($regex, $content, $matches);
        return $matches[(int)$index];
    }

    function showOutput() {
        if ($this->url) {
            $content.= '<h2>Film</h2><p>'.$this->movie.'</p>';
            $content.= '<h2>Director</h2><p><a href="http://www.imdb.com'.$this->url_director.'">'.$this->director.'</a></p>';
            $content.= '<h2>Plot</h2><p>'.$this->plot.'</p>';
            $content.= '<h2>Release Date</h2><p>'.$this->release_date.'</p>';
            $content.= '<h2>MPAA</h2><p>'.$this->mpaa.'</p>';
            $content.= '<h2>Run Time</h2><p>'.$this->run_time.' minutes</p>';
            $content.= '<h2>Full Details</h2><p><a href="'.$this->url.'">'.$this->url.'</a></p>';
            $content.= '<h2>Rating</h2><p>'.$this->rating.'</p>';
            $content.= '<h2>Votes</h2><p>'.$this->votes.' votes</p>';
            $content.= '<h2>Country</h2><p><a href="http://www.imdb.com'.$this->url_country.'">'.$this->country.'</a></p>';
            echo $content;
        } else {
            echo 'Sorry, nothing found! :(';
        }
    }
}
?>
