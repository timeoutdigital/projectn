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
   *
   * @return IMDBMovie
   */
  static public function fromTitleAndSearchResult( $title, $searchResultsHtml )
  {
    $movie = new IMDBMovie( $title, $searchResultsHtml );
    return $movie;
  }

  private function __construct( $title, $html)
  {
    $this->setTitle( $title );
    $this->setHtml( $html );
  }

  /**
   * Takes the html as a string. Other functions use the HTML to get information from, like id
   *
   * @param string $html
   */
  private function setHtml( $html )
  {
    $this->html = $html;
    $this->setPageType();
  }

  /**
   * Set the title of the movie. This is used to help find the movie in the html
   *
   * @param string $html
   */
  private function setTitle( $title )
  {
    $this->title = $title;
    $this->setPageType();
  }

  /**
   * Returns the IMDb id
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

  /**
   * Determine what page type was used to instanciate the object. Types include:
   * PAGE_TYPE_TITLE_SEARCH
   * PAGE_TYPE_TITLE
   */
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

  /**
   * Is used to extract the IMDb id from a Title Search Page.
   * 
   * A Title Search Page
   *
   * @return string
   */
  private function getIdFromTitleSearchPage()
  {
    if( !$this->html || !$this->title )
      return;

    $id = $this->tryGettingIdFromOriginalTitle();

    return $id;
  }

  /**
   * @return string
   */
  private function tryGettingIdFromOriginalTitle()
  {
    $id = null;

    $regex = sprintf( '@<a.*?href="/title/(tt[0-9]+)/".{0,200}>(%s)@', $this->title );
    preg_match( $regex, $this->html, $matches );

    if( count( $matches ) >= 2 )
      $id = $matches[1];

    return $id;
  }

  private function tryGettingIdFromAkas()
  {
    //To be implmented 
    //Results in IMDb keep their original title
    //With aka titles underneath
    //So if we search in Portuguese
    //we need to find the id using
    //the Protuguese title against the aka
    //For example see page
    //see http://www.imdb.com/find?s=tt&q=Como+Treinares+o+Teu+Drago+-+VP
    //The number 1 result has the English title and the Portuguese title in aka
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
