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
class IMDBMovie
{
  private $html;

  /**
   * @param string $searchResultsHtml
   * @return IMDBMovie
   */
  static public function fromSearchResultHtml( $searchResultsHtml )
  {
    return new IMDBMovie( $searchResultsHtml );
  }

  static public function fromTitleHtml( $titleHtml )
  {
    throw new Exception( 'Not yet implemented: IMDBMovie::fromTitleHtml().' );
  }

  /**
   * 
   */
  private function __construct( $html )
  {
    $this->html = $html;
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
    $title = $this->getMatch( $regex, $this->html );
    return $title;
  }

  private function getMatch($regex, $content, $index=1) 
  {
    preg_match($regex, $content, $matches);

    if( empty( $matches ) )
      return null;

    return $matches[$index];
  }
}
?>
