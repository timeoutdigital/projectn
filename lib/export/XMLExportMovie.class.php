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

    $rootTag->addAttribute( 'modified', $this->modifiedTimeStamp );
    $rootTag->addAttribute( 'vendor', $this->vendor->getName() );

    foreach( $movieCollection as $movie )
    {
      $movieTag = $rootTag->addChild( 'movie' );
      $movieTag->addAttribute( 'id', '187' );
      $movieTag->addAttribute( 'modified', $this->modifiedTimeStamp );

      //movie/name
      $movieTag->addChild( 'name', htmlspecialchars( $movie['name'] ) );

      //movie/version
      $versionTag = $movieTag->addChild( 'version' );
      $versionTag->addAttribute( 'lang', 'en' );

      //movie/version/name
      $versionTag->addChild( 'name', htmlspecialchars( $movie['name'] ) );

      //movie/version/genre
      foreach( $movie['MovieGenres'] as $genre )
      {
        $versionTag->addChild( 'genre', htmlspecialchars( $genre['genre'] ) );
      }

      //movie/version/plot
      $versionTag->addChild( 'plot', htmlspecialchars( $movie['plot'] ) );

      //movie/version/review
      $versionTag->addChild( 'review', htmlspecialchars( $movie['review'] ) );

      //movie/version/url
      $versionTag->addChild( 'url', $movie['url'] );

      //movie/version/rating
      $versionTag->addChild( 'rating', $movie['rating'] );

      //movie/version/place
      $placeTag = $versionTag->addChild( 'place' );
      $placeTag->addAttribute( 'place-id', $movie['Poi']['id'] );
      
      foreach( $movie['MovieProperty'] as $property )
      {
        $propertyTag = $versionTag->addChild( 'property', htmlspecialchars($property[ 'value' ]) );
        $propertyTag->addAttribute( 'key', htmlspecialchars($property[ 'lookup' ]) );
      }
    }

    return $rootTag;
  }
}
?>
