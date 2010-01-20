<?php

/**
 * Creates the Movie XML for a specified vendor. The XML is written to a file.
 *
 * @author clarence
 * 
 */
class XMLExportMovie extends XMLExport
{
  public function __construct( $vendor, $destination )
  {
    parent::__construct($vendor, $destination, 'Movie' );
  }

  /**
   * Translates a Doctrine collection of movies to xml.
   *
   * @param Doctrine_Collection $movieCollection
   * @return SimpleXMLElement
   * 
   * @todo correct version@lang use real value
   * @todo correct movie@id to use real value
   * @todo correct movie/version/name to use real value
   * @todo confirm where the place tag belongs
   */
  protected function generateXML( $movieCollection )
  {
    $rootTag =  simplexml_load_string('<vendor-movies/>');

    foreach( $movieCollection as $movie )
    {
      $movieTag = $rootTag->addChild( 'movie' );
      $movieTag->addAttribute( 'id', '187' );
      $movieTag->addAttribute( 'modified', $this->modifiedTimeStamp );

      //movie/name
      $movieTag->addChild( 'name', $movie['name'] );

      //movie/version
      $versionTag = $movieTag->addChild( 'version' );
      $versionTag->addAttribute( 'lang', 'en' );

      //movie/version/name
      $versionTag->addChild( 'name', $movie['name'] );

      //movie/version/genre
      foreach( $movie['MovieGenres'] as $genre )
      {
        $versionTag->addChild( 'genre', $genre['genre'] );
      }

      //movie/version/plot
      $versionTag->addChild( 'plot', $movie['plot'] );

      //movie/version/review
      $versionTag->addChild( 'review', $movie['review'] );

      //movie/version/url
      $versionTag->addChild( 'url', $movie['url'] );

      //movie/version/rating
      $versionTag->addChild( 'rating', $movie['rating'] );

      //movie/version/place
      $placeTag = $versionTag->addChild( 'place' );
      $placeTag->addAttribute( 'place-id', $movie['Poi']['id'] );
      
    }

    return $rootTag;
  }
}
?>
