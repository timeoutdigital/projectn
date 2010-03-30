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
  const PAGE_TYPE_TITLE_SEARCH = 'TitleSearchPage';
  const PAGE_TYPE_TITLE        = 'TitlePage';

  private $html;
  private $id;
  private $title;
  private $pageType;

  /**
   * @param string $searchResultsHtml
   * @return IMDBMovie
   */
  static public function fromTitleAndSearchResult( $title, $searchResultsHtml )
  {
    $movie = new IMDBMovie();
    $movie->setTitle( $title );
    $movie->setHtml( $searchResultsHtml );
    return $movie;
  }

  /**
   * 
   */
  private function __construct()
  {
  }

  public function setHtml( $html )
  {
    $this->html = $html;
    $this->setPageType();
  }

  public function setId( $id )
  {
    $this->id = $id;
  }

  public function setTitle( $title )
  {
    $this->title = $title;
    $this->setPageType();
  }

  /**
   * 
   * @return int
   */
  public function getId()
  {
    if( !$this->getPageType() )
      return;

    $method = 'getIdFrom' . $this->getPageType();
    $id = $this->$method();

    return $id;
  }

  /**
   *
   */
  private function getMatch($regex, $content, $index=1) 
  {
    preg_match($regex, $content, $matches);

    if( empty( $matches ) )
      return null;

    return $matches[$index];
  }

  /**
   * The information for a Movie may be using an IMDB Title Search page
   * or a title page (a page about a single movie)
   * 
   * @return string
   */
  public function getPageType()
  {
    return $this->pageType;
  }

  private function setPageType()
  {
    if( !$this->title || !$this->html )
      return;

    $pageType = null;

    if( $this->titleTagContains( 'IMDb Title Search' ) )
    {
      $pageType = self::PAGE_TYPE_TITLE_SEARCH;
    }
    else if( $this->titleTagContains( $this->title ) )
    {
      $pageType = self::PAGE_TYPE_TITLE;
    }
    else if( $this->alsoKnownAsContains( $this->title ) )
    {
      $pageType = self::PAGE_TYPE_TITLE;
    }

    $this->pageType = $pageType;
  }

  private function getIdFromTitleSearchPage()
  {
    if( !$this->html || !$this->title )
      return;

    $regex = sprintf( '@<a.*?href="/title/(tt[0-9]+)/".{0,200}>(%s)@', $this->title );

    preg_match( $regex, $this->html, $matches );
    if( count( $matches ) < 2 ) echo $regex;
    $id = $matches[1];

    return $id;
  }

  private function getIdFromTitlePage()
  {
    if( !$this->html || !$this->title )
      return;

    $canonicalTagRegex = sprintf( '@<link.*?rel="canonical".*?>@', $this->title );
    preg_match( $canonicalTagRegex, $this->html, $canonicalTagMatches );
    $canonicalLinkTag = $canonicalTagMatches[0];

    $regex = sprintf( ':href=".*?(tt[0-9]+).*?":', $this->title );
    preg_match( $regex, $this->html, $matches );

    $id = $matches[1];

    return $id;
  }

  private function titleTagContains( $text )
  {
    if( empty( $text ) )
      return;

    $matches = array();
    preg_match( '@<title>.*?</title>@', $this->html, $matches );
    $titleTagContents = $matches[ 0 ];

    $found = strpos( $titleTagContents, $text ) != false;

    return $found;
  }

  private function alsoKnownAsContains( $title )
  {
    $regex = '@' .
             '<h3>Additional Details</h3>.{0,50}' .
             '<div.{0,50}' .
             '<h5>Also Known As:</h5>.{0,50}' .
             '<div.{0,50}' .
             '"('.
             $this->title .
             ')"'.
             '@';

    preg_match( $regex, $this->html, $matches );

    if( isset( $matches[1] ) && ($matches[1] == $this->title) )
      return true;
  }
}
?>
