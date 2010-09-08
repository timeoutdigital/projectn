<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class LisbonFeedMoviesMapper extends LisbonFeedBaseMapper
{
  public function mapMovies()
  {
    $lastTitle = '';

    foreach( $this->xml->films as $filmElement )
    {
        try{
            if( (string) $filmElement['title'] == $lastTitle )
            {
                continue;
            }
            $lastTitle = (string) $filmElement[ 'title' ];

            $movie = $this->dataMapperHelper->getMovieRecord( $filmElement['filmID'] );

            $this->mapAvailableData($movie, $filmElement );

            $movie['vendor_id']  = $this->vendor[ 'id' ];
            $movie['utf_offset'] = $this->vendor->getUtcOffset();

            $this->notifyImporter( $movie );
            $movie->free();
            
        } catch ( Exception $exception )
        {
            $this->notifyImporterOfFailure( 'Exception: LisbonFeedMoviesMapper::mapMovies - ' . $exception->getMessage() );
        }
    }
  }

  private function extractRating( $filmElement )
  {
    $ratingToUse = $filmElement[ 'ReviewToUse' ];
    return (string) $filmElement[ 'Review' . $ratingToUse . 'rating' ];
  }

  /**
   * Return an array of mappings from xml attributes to record fields
   *
   * @return array
   */
  protected function getMap()
  {
    return array(
        'filmID'        => 'vendor_movie_id',
        'title'         => 'name',
        'Review_'       => 'review',        
        'Director'      => 'director',
        'Stars'         => 'cast',
        'Runtime'       => 'duration',
        'Country'       => 'country',
        'Review1Rating' => 'rating',        
    );
  }

  /**
   * Return an array of attributes to ignore when mapping
   *
   * @return array
   */
  protected function getIgnoreMap()
  {
    return array(      
      'aka',
      'image',
      'Year',
      'Review1Reviewer',
      'Subtitles',
      'Filmkey',
      'comments',
      'wordcount',
      'fg',
      'ReleaseStatus',
      'date',
      'Review1IssueNo',
      'AtexKey',
      'Review2_',
      'Review3_',
      'Review4_',
      'Review5_',
      'Review6_',
      'Review7_',
      'Review8_',
      'Review9_',
      'Review10_',
      'RegistrationDate',
      'RunningTime',
      'BlackandWhite',
      'Directors',
      'Producers',
      'DirectorOfPhotography',
      'Composers',
      'ScreenWriters',
      'Editors',
      'ProductionDesigners',
      'ArtDirectors',
      'Cast',
      'Narrators',
      'Top100',
      'AwardsWon',
      'Awards',
      'Review2Reviewer',
      'Review2rating',
      'Review3Reviewer',
      'Review3rating',
      'Review4Reviewer',
      'Review4rating',
      'Review5Reviewer',
      'Review5rating',
      'Review6Reviewer',
      'Review6rating',
      'Review7Reviewer',
      'Review7rating',
      'Review8Reviewer',
      'Review8rating',
      'Review9Reviewer',
      'Review9rating',
      'Review10Reviewer8',
      'Review10rating8',
      'ReviewToUse',
      'FilmSort',
      'FilmEvent',
      'Highlight',
    );
  }

  /**
   * find the Poi id for the placeid field in xml
   */
  private function getPoiId( $filmElement )
  {
    $vendorPoiId = (string) $filmElement->cinemaplacelink[ 'placeid' ];
    $poi = $this->getMovieRecord( $vendorPoiId );
    if( $poi['id'] )
      return $poi['id'];
    else
      return 1;
  }
}
?>
