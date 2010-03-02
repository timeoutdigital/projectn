<?php

/**
 * Creates the Movie XML for a specified vendor. The XML is written to a file.

 * @package projectn
 * @subpackage export.lib
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd 2009
 *
 *
 */
class XMLExportMovie extends XMLExport
{
  public function __construct( $vendor, $destination )
  {
    parent::__construct($vendor, $destination, 'Movie',  'movies.xsd' );
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
    $rootTag = $this->appendRequiredElement($domDocument, 'vendor-movies');

    $rootTag->setAttribute( 'modified', $this->modifiedTimeStamp );
    $rootTag->setAttribute( 'vendor', $this->vendor->getName() );

    foreach( $movieCollection as $movie )
    {
      $movieElement = $this->appendRequiredElement($rootTag, 'movie');
      $movieElement->setAttribute( 'id', $movie['vendor_movie_id'] );
      $movieElement->setAttribute( 'modified', $this->modifiedTimeStamp );

      //movie/name
      $nameElement = $this->appendRequiredElement($movieElement, 'name', $movie['name'], XMLExport::USE_CDATA);

      //movie/version
      $versionElement = $this->appendRequiredElement($movieElement, 'version');
      $langArray = explode('-',$this->vendor['language']);
      $versionElement->setAttribute( 'lang', $langArray[0] );

      //movie/version/name
      $this->appendRequiredElement($versionElement, 'name',  $movie['name'], XMLExport::USE_CDATA);

      //movie/version/genre
      foreach( $movie['MovieGenres'] as $genre )
      {
        $this->appendRequiredElement($versionElement, 'genre', $genre['genre'], XMLExport::USE_CDATA);
      }

      //movie/version/plot
      $cleanedPlot = $this->cleanHtml( $movie['plot'] );
      $this->appendNonRequiredElement($versionElement, 'plot', $cleanedPlot, XMLExport::USE_CDATA);

      //movie/version/review
      $cleanedReview = $this->cleanHtml( $movie['review'] );
      $this->appendNonRequiredElement($versionElement, 'review', $cleanedReview, XMLExport::USE_CDATA);

      //movie/version/url
      $this->appendNonRequiredElement($versionElement, 'url', $movie['url'], XMLExport::USE_CDATA);


      //movie/version/rating
      $this->appendNonRequiredElement($versionElement, 'rating', $movie['rating'] );

      //movie/showtimes
      $showTimesElement = $this->appendRequiredElement($movieElement, 'showtimes' );

      //movie/showtimes/place
      $placeElement = $this->appendRequiredElement($showTimesElement, 'place' );
      $placeElement->setAttribute( 'place-id', $movie['Poi']['id'] );

      //movie/showtimes/place/age_rating
      $this->appendNonRequiredElement($placeElement, 'age_rating', $movie['age_rating'] );

      //movie/showtimes/place/time
      //implementation on hold

      //movie/version/media
      /*foreach( $movie[ 'MovieMedia' ] as $medium )
      {
        $mediaElement = $this->appendNonRequiredElement($versionElement, 'media', $medium['url'], XMLExport::USE_CDATA);
        if ( $mediaElement instanceof DOMElement )
        {
          $mediaElement->setAttribute( 'mime-type', $medium[ 'mime_type' ] );
        }
        //$medium->free();
      }*/
      
      foreach( $movie['MovieProperty'] as $property )
      {
        $propertyTag = $this->appendNonRequiredElement($versionElement, 'property', $property['value'], XMLExport::USE_CDATA);
        if( $propertyTag )
        $propertyTag->setAttribute( 'key', htmlspecialchars($property[ 'lookup' ]) );
      }
    }

    return $domDocument;
  }
}
?>
