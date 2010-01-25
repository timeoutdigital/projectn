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
   * @param DOMDocument $domDocument
   * @param Doctrine_Collection $movieCollection
   * @return SimpleXMLElement
   * 
   * @todo correct version@lang use real value
   * @todo correct movie@id to use real value
   * @todo correct movie/version/name to use real value
   * @todo confirm where the place tag belongs
   */
  protected function mapDataToDOMDocument($movieCollection, $domDocument)
  {
    $rootTag =  $domDocument->appendChild( new DOMElement( 'vendor-movies' ));

    $rootTag->setAttribute( 'modified', $this->modifiedTimeStamp );
    $rootTag->setAttribute( 'vendor', $this->vendor->getName() );

    foreach( $movieCollection as $movie )
    {
      $movieElement = $rootTag->appendChild( new DOMElement( 'movie' ) );
      $movieElement->setAttribute( 'id', '187' );
      $movieElement->setAttribute( 'modified', $this->modifiedTimeStamp );

      //movie/name
      $nameElement = $movieElement->appendChild( new DOMElement( 'name' ) );
      $nameElement->appendChild( $domDocument->createCDATASection( $movie['name'] ) );

      //movie/version
      $versionElement = $movieElement->appendChild( new DOMElement( 'version' ) );
      $versionElement->setAttribute( 'lang', 'en' );

      //movie/version/name
      $nameElement = $versionElement->appendChild( new DOMElement( 'name' ) );
      $nameElement->appendChild( $domDocument->createCDATASection( $movie['name'] ) );

      //movie/version/genre
      foreach( $movie['MovieGenres'] as $genre )
      {
        $genreElement = $versionElement->appendChild( new DOMElement( 'genre' ) );
        $genreElement->appendChild( $domDocument->createCDATASection( $genre['genre'] ) );
      }

      //movie/version/plot
      $plotElement = $versionElement->appendChild( new DOMElement( 'plot' ) );
      $plotElement->appendChild( $domDocument->createCDATASection( $movie['plot'] ) );

      //movie/version/review
      $reviewElement = $versionElement->appendChild( new DOMElement( 'review' ));
      $reviewElement->appendChild( $domDocument->createCDATASection( $movie['review'] ) );

      //movie/version/url
      $urlElement = $versionElement->appendChild( new DOMElement( 'url' ) );
      $urlElement->appendChild( $domDocument->createCDATASection( $movie['url'] ) );

      //movie/version/rating
      $versionElement->appendChild( new DOMElement( 'rating', $movie['rating'] ) );

      //movie/version/place
      $placeTag = $versionElement->appendChild( new DOMElement( 'place' ) );
      $placeTag->setAttribute( 'place-id', $movie['Poi']['id'] );
      
      foreach( $movie['MovieProperty'] as $property )
      {
        $propertyTag = $versionElement->appendChild( new DOMElement( 'property' ) );//, htmlspecialchars($property[ 'value' ]) );
        $propertyTag->appendChild( $domDocument->createCDATASection( $property['value'] ) );
        $propertyTag->setAttribute( 'key', htmlspecialchars($property[ 'lookup' ]) );
      }
    }

    return $domDocument;
  }
}
?>
