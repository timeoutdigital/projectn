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
    $xsd =  sfConfig::get( 'sf_data_dir') . DIRECTORY_SEPARATOR . 'xml_schemas'. DIRECTORY_SEPARATOR . 'movie.xsd';
    parent::__construct($vendor, $destination, 'Movie', $xsd );
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
   * @todo sort out imdb properly (either log error and change schema if must
   *       field, or then stop jumping the loop
   */
  protected function mapDataToDOMDocument($movieCollection, $domDocument)
  {
    $rootTag = $this->appendRequiredElement($domDocument, 'vendor-movies');

    $rootTag->setAttribute( 'modified', $this->modifiedTimeStamp );
    $rootTag->setAttribute( 'vendor', XMLExport::VENDOR_NAME );

    foreach( $movieCollection as $movie )
    {
      if( empty( $movie[ 'imdb_id' ] ) )
      {
        ExportLogger::getInstance()->addError( 'no imdb id available', 'Movie', $movie[ 'id' ] );
        continue;
      }

      if( empty( $movie[ 'review' ] ) )
      {
        // Refs: #364, #365, #366 -- Pepper/Sefi said to export Barcelona movies without reviews
        // until reviews are available in their feed, est. August '10. Please remove when reviews are available.
        if( $movie['Vendor']['city'] != 'barcelona' )
        {
            ExportLogger::getInstance()->addError( 'no review available', 'Movie', $movie[ 'id' ] );
            continue;
        }
        else ExportLogger::getInstance()->addError( 'Warning -- Exporting Movie without review for Barcelona; as per executive order.', 'Movie', $movie[ 'id' ] );
      }

      $movieElement = $this->appendRequiredElement($rootTag, 'movie');
      $movieElement->setAttribute( 'id', $this->generateUID( $movie['id'] ) );
      $movieElement->setAttribute( 'link-id', $movie['imdb_id'] );
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
      $genreString = $this->extractGenre($movie);
      $this->appendNonRequiredElement($versionElement, 'genre', $genreString, XMLExport::USE_CDATA);

      //movie/version/tag-line
      $cleanedTagLine = $this->cleanHtml( $movie['tag_line'] );
      $this->appendNonRequiredElement($versionElement, 'tag-line', $cleanedTagLine, XMLExport::USE_CDATA);

      //movie/version/plot
      $cleanedPlot = $this->cleanHtml( $movie['plot'] );
      $this->appendNonRequiredElement($versionElement, 'plot', $cleanedPlot, XMLExport::USE_CDATA);

      //movie/version/review
      $cleanedReview = $this->cleanHtml( $movie['review'] );
      $this->appendNonRequiredElement($versionElement, 'review', $cleanedReview, XMLExport::USE_CDATA);

      //movie/version/rating
      if( $this->ratingInRangeOfOneToFiveInclusive( $movie ) )
        $this->appendNonRequiredElement($versionElement, 'rating', $movie['rating'] );

      //movie/version/director
      $this->appendNonRequiredElement($versionElement, 'director', $movie['director'], XMLExport::USE_CDATA);

      //movie/version/writer
      $this->appendNonRequiredElement($versionElement, 'writer', $movie['writer'], XMLExport::USE_CDATA);

      $actors = explode( ',', $movie['cast'] );

      if ( 1 < count( $actors ) || $actors[ 0 ] != '' )
      {
          //movie/version/cast
          $castElement = $this->appendRequiredElement($versionElement, 'cast');

          foreach( $actors as $actor )
          {
              //movie/version/cast/actor
              $actorElement = $this->appendRequiredElement($castElement, 'actor');
              //movie/version/cast/actor/actor-name
              $this->appendRequiredElement($actorElement, 'actor-name', $actor, XMLExport::USE_CDATA);
          }
      }

      //movie/additional-details
      $additionalDetailsElement = $this->appendRequiredElement($movieElement, 'additional-details');

      //movie/additional-details/website
      $this->appendNonRequiredElement($additionalDetailsElement, 'website', $movie['url'], XMLExport::USE_CDATA);

      //movie/additional-details/age-rating
      $this->appendNonRequiredElement($additionalDetailsElement, 'age-rating', $movie['age_rating'], XMLExport::USE_CDATA);

      //movie/additional-details/release-date
      //$this->appendNonRequiredElement($additionalDetailsElement, 'release-date', $movie['release_date']);
      // Removed, see ticket #262

      //movie/additional-details/duration
      $this->appendNonRequiredElement($additionalDetailsElement, 'duration', $movie['duration'], XMLExport::USE_CDATA);

      //movie/additional-details/country
      //$this->appendNonRequiredElement($additionalDetailsElement, 'country', $movie['country'], XMLExport::USE_CDATA);

      //movie/additional-details/aspect-ratio
      $this->appendNonRequiredElement($additionalDetailsElement, 'language', $movie['language'], XMLExport::USE_CDATA);

      //movie/additional-details/aspect-ratio
      $this->appendNonRequiredElement($additionalDetailsElement, 'aspect-ratio', $movie['aspect_ratio'], XMLExport::USE_CDATA);

      //movie/additional-details/sound-mix
      $this->appendNonRequiredElement($additionalDetailsElement, 'sound-mix', $movie['sound_mix'], XMLExport::USE_CDATA);
      
      //movie/additional-details/company
      $this->appendNonRequiredElement($additionalDetailsElement, 'company', $movie['company'], XMLExport::USE_CDATA);

      //check if movie/additional-details node is actually needed (has kids)
      if ( ! $additionalDetailsElement->hasChildNodes() )
      {
        $movieElement->removeChild( $additionalDetailsElement );
      }



      //movie/showtimes
     // $showTimesElement = $this->appendRequiredElement($movieElement, 'showtimes' );

      //movie/showtimes/place
    //  $placeElement = $this->appendRequiredElement($showTimesElement, 'place' );
    //  $placeElement->setAttribute( 'place-id', $movie['Poi']['id'] );


      //movie/showtimes/place/age_rating
      //$this->appendNonRequiredElement($placeElement, 'Age_rating', $movie['age_rating'] );


      //movie/showtimes/place/time
      //implementation on hold

      //movie/version/media
      foreach( $movie[ 'MovieMedia' ] as $medium )
      {
        $mediaElement = $this->appendNonRequiredElement($versionElement, 'media', $medium->getAwsUrl(), XMLExport::USE_CDATA);
        
        if ( $mediaElement instanceof DOMElement )
        {
          $mediaElement->setAttribute( 'mime-type', $medium[ 'mime_type' ] );
        }
        //$medium->free();
      }
      
      foreach( $movie['MovieProperty'] as $property )
      {
        if( isset( $property['lookup'] ) )
        {
            if( $property['lookup'] == "Critics_choice" && strtolower( $property['value'] ) != "y" )
            {
              break;
            }
            $propertyTag = $this->appendNonRequiredElement($versionElement, 'property', $property['value'], XMLExport::USE_CDATA);
            if( $propertyTag )
            {
                $propertyTag->setAttribute( 'key', htmlspecialchars($property[ 'lookup' ]) );
            }
        }
      }

      // Ui Category is Always 'Film'
      $propertyTag = $this->appendNonRequiredElement( $versionElement, 'property', 'Film', XMLExport::USE_CDATA );
      $propertyTag->setAttribute( 'key', 'UI_CATEGORY' );
      
      ExportLogger::getInstance()->addExport( 'Movie' );
    }

    return $domDocument;
  }

  /**
   * @param
   * @return string comma separated string of genres
   */
  private function extractGenre( Doctrine_Record $movie )
  {
    $genreArray = array();
    foreach( $movie['MovieGenres'] as $genre )
    {
      $genreArray[] = $genre['genre'];
    }

    $genreString = stringTransform::concatNonBlankStrings(', ', $genreArray );
    return $genreString;
  }

  private function ratingInRangeOfOneToFiveInclusive( $movie )
  {
    return ($movie[ 'rating' ] >= 1) && ($movie[ 'rating' ] <= 5);
  }
}
?>
