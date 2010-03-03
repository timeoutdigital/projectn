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
class londonDatabaseFilmsDataMapper extends DataMapper
{
  /**
   * This integer value comes from london database:
   * film_convert.review.review_type_id
   *
   * @static int
   */
  const LONDON_REVIEW_TYPE_ID = 2;

  /**
   * This integer value comes from london database:
   * film_convert.review.review_type_id
   *
   * @static int
   */
  const NEW_YORK_REVIEW_TYPE_ID = 3;

  /**
   * This integer value comes from london database:
   * film_convert.review.review_type_id
   * 
   * @static int
   */
  const CHICAGO_REVIEW_TYPE_ID = 4;

  /**
   * @var string
   */
  protected $dsn = 'mysql:dbname=film_convert;host=80.250.104.16';

  /**
   * @var string
   */
  protected $dbUserName = 'timeout';


  /**
   * @var string
   */
  protected $dbPwd = '65dali32';

  /**
   * @var int
   */
  protected $reviewTypeId;

  /**
   * @var PDO
   */
  protected $pdo;
  
  /**
   * @var Vendor
   */
  protected $vendor;

  /**
   * @var projectNDataMapperHelper
   */
  protected $dataMapperHelper;

  /**
   *
   * @param Vendor $vendor
   * @param int $reviewTypeId corresponds to the column 'review_type_id' on
   * table 'review' of database 'film_convert'
   */
  public function  __construct( Vendor $vendor, $reviewTypeId )
  {
    $this->vendor = $vendor;
    $this->reviewTypeId = $reviewTypeId;
    $this->pdo  = new PDO( $this->dsn, $this->dbUserName, $this->dbPwd );
    $statement = $this->pdo->prepare( "SET NAMES UTF8 " );
    $statement->execute();
    $this->dataMapperHelper = new projectNDataMapperHelper($vendor);
  }
  
  public function mapMovies()
  {

    foreach( $this->getFilmsFromLondonDatabase() as $data )
    {
      
//      $movie = $this->getRecord('Movie', 'vendor_movie_id', $this->vendor['id'] );
      $movie = $this->dataMapperHelper->getMovieRecord( $data[ 'film_id' ] );

      $movie['vendor_id']  = $this->vendor['id'];
      $movie['utf_offset'] = $this->vendor->getUtcOffset();

      $movie['name'] = $data[ 'title' ];
      $movie['vendor_movie_id'] = $data[ 'film_id' ];

      $movie->addProperty('Age_rating', $data[ 'age_rating' ]);
      
      $review = $this->getReview( $data[ 'film_id' ] );

      //echo $encoding = mb_detect_encoding($review[ 'text' ]).PHP_EOL;
      $movie['review'] = $this->cleanForIconvStrlen( $review[ 'text' ] );
      $movie['rating'] = $review[ 'rating' ];

      $genres = explode( ',',$data[ 'genre' ]);
      
      foreach( $genres as $genre )
      {
        if( empty( $genre ) )
        {
          continue;
        }
         
        $movieGenre =  Doctrine::getTable('MovieGenre' )->findOneByGenre( $genre );
        if( !$movieGenre )
        {
          $movieGenre = new MovieGenre();
          $movieGenre [ 'genre' ] = $genre;
        }
       
        $movie[ 'MovieGenres' ][] = $movieGenre;
      }

      if( $data[ 'image_id' ] > 0 )
      { 
        /*@todo sort out the test and uncomment this
         *
         * try
        {
            $movie->addMediaByUrl( 'http://www.timeout.com/img/' . $data[ 'image_id' ] . '/w398/image.jpg' );
        }
        catch( Exception $e )
        {
            $this->notifyImporterOfFailure( $e );
        }*/
      }

      $this->notifyImporter( $movie );
    }
  }
 
  /**
   *
   */
  protected function getFilmsFromLondonDatabase()
  { 
    //since Time Out supports at most one city in ny and chicago, it's
    //safe to query films by state.
    //Supporting a query by city is tough because there are many 'cities'
    //in Chicago, e.g. Skokie, Chicago, Wilmette, etc10119509...
    //this could go into a yml...

    //@todo clean this crap up
    $vendorToStateCountryMap = array( 'ny' => '%NY', 'chicago' => '%IL', 'london' => '930' );

    if( !key_exists( $this->vendor->city ,$vendorToStateCountryMap ) )
    {
      throw new Exception( $this->vendor['city'] . ' vendor is not supported.' );
    }

    $stateCountry = $vendorToStateCountryMap[ $this->vendor[ 'city' ] ];
    
    $query = ( is_numeric( $stateCountry ) ) ? $this->getFindByCountryQuery() : $this->getFindByStateQuery();

    $statement = $this->pdo->prepare( $query );
    $statement->execute( array( $stateCountry ) );

    return $statement->fetchAll(  PDO::FETCH_ASSOC );
  }
  
  private function getFindByStateQuery()
  {
    $query = "
      SELECT f.*,cr.code as age_rating, group_concat(DISTINCT gt.genre SEPARATOR ',' ) as genre , f.image_id
        FROM cinema c
        JOIN listing l ON l.cinema_id = c.cinema_id and date >= date( NOW() )
        JOIN film f ON f.film_id = l.film_id
        JOIN film_certificates fc ON f.film_id = fc.film_id
        JOIN certificates cr ON fc.certificate_id = cr.id
        LEFT JOIN genre g ON g.film_id = f.film_id
        LEFT JOIN genre_title gt ON g.genre_id = gt.genre_id
        WHERE c.country_id = '931' AND  city_state LIKE ?
        GROUP BY film_id
    ";
    return $query;
  }

  private function getFindByCountryQuery()
  {
    $query = "
      SELECT f.*,cr.code as age_rating, group_concat(DISTINCT gt.genre SEPARATOR ',' ) as genre , f.image_id
        FROM cinema c
        JOIN listing l ON l.cinema_id = c.cinema_id and date >= date( NOW() )
        JOIN film f ON f.film_id = l.film_id
        JOIN film_certificates fc ON f.film_id = fc.film_id
        JOIN certificates cr ON fc.certificate_id = cr.id
        LEFT JOIN genre g ON g.film_id = f.film_id
        LEFT JOIN genre_title gt ON g.genre_id = gt.genre_id
        WHERE c.country_id = ?
        GROUP BY film_id
    ";
    return $query;
  }

  /**
   * returns the review text for the given filmId
   * review_type_id = 4  // Time Out Chicago
   * review_type_id = 3  // Time Out New York
   * review_type_id = 2  // Time Out London
   * review_type_id = 1  // Time Out Film Guide
   * review_type_id = 5  // Time Out Online
   * review_type_id = 7  // Synopsis US
   * review_type_id = 8  // Short Review - NY
   */
  protected function getReview( $filmId )
  {
    $query = "
      SELECT review_type_id , text , rating ,
      (
        CASE
         WHEN review_type_id = " . $this->reviewTypeId . " THEN 10
         WHEN review_type_id = 2 THEN 9
         WHEN review_type_id = 1 THEN 8
         WHEN review_type_id = 5 THEN 7
         WHEN review_type_id = 7 THEN 6
         WHEN review_type_id = 8 THEN 5
        END
      ) AS review_index
       FROM review
       WHERE film_id = " .$filmId. "
       ORDER BY review_index DESC LIMIT 1";

     $statement = $this->pdo->prepare( $query );

     $statement->execute();

     return $statement->fetch( PDO::FETCH_ASSOC );
  }

  /**
   * A dirty and dispicable hack that takes characters that iconv_strlen() chokes on
   *
   * @return string
   */
  protected function cleanForIconvStrlen( $string )
  {
    $string = preg_replace("/[^\x9\xA\xD\x20-\x7F]/", "", $string );
    return $string;
  }
}
?>
