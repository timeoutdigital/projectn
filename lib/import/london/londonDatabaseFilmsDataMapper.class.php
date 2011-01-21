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
   * @var Vendor
   */
  protected $vendor;
  
  /**
   * @var PDO
   */
  protected $pdo;

  /**
   * @var projectNDataMapperHelper
   */
  protected $dataMapperHelper;
  
  /**
   * @var array
   */
  protected $databaseConfig;

  /**
   * @var array
   */
  protected $params;

  /**
   *
   * @param Vendor $vendor
   * table 'review' of database 'film_convert'
   */
  public function  __construct( Vendor $vendor, $params )
  {
    // Set Defaults
    $this->vendor = $vendor;
    $this->fillConfig( $params );
    $this->preparePdo();
    $this->dataMapperHelper = new projectNDataMapperHelper($vendor);
  }
  
  public function mapMovies()
  {
    foreach( $this->getFilmsFromLondonDatabase() as $data )
    {
      $movie = $this->dataMapperHelper->getMovieRecord( $data[ 'film_id' ] );

      $movie['vendor_id']  = $this->vendor['id'];
      $movie['vendor_movie_id'] = $data[ 'film_id' ];
      $movie['name'] = $data[ 'title' ];
      //$movie['plot'] = ;
      //$movie['tag_line'] = ;
      $review = $this->getReview( $data[ 'film_id' ] );
      //echo $encoding = mb_detect_encoding($review[ 'text' ]).PHP_EOL;
      $movie['review'] = $this->cleanForIconvStrlen( $review[ 'text' ] );
      $movie['director'] = $data[ 'director' ];
      $movie['writer'] = $data[ 'screenwriter' ];
      $movie['cast'] = $data[ 'cast' ];
      $movie['age_rating'] = $data[ 'age_rating' ];
      $movie['release_date'] = $data[ 'release_date' ];
      $movie['duration'] = ( $data[ 'runtime' ] != 0 ) ? $data[ 'runtime' ] : NULL;
      //$movie['country'] = ;
      //$movie['language'] = ;
      //$movie['aspect_ratio'] = ;
      //$movie['sound_mix'] = ;
      //$movie['company'] = ;
      $movie['rating'] = is_numeric( $review[ 'rating' ] ) ? $review[ 'rating' ] : NULL;
      $movie['utf_offset'] = $this->vendor->getUtcOffset();

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

        if( isset( $data['image_id'] ) && is_numeric( $data['image_id'] ) && $data['image_id'] != 0 )
        {
            $this->addImageHelper( $movie, 'http://www.toimg.net/managed/images/' . $data[ 'image_id' ] . '/i.jpg' );
        }
      }

      $this->notifyImporter( $movie );
    }
  }
 
  /**
   * @todo Comments in method refer to USA when this is for London
   */
  protected function getFilmsFromLondonDatabase()
  { 
    //since Time Out supports at most one city in ny and chicago, it's
    //safe to query films by state.
    //Supporting a query by city is tough because there are many 'cities'
    //in Chicago, e.g. Skokie, Chicago, Wilmette, etc10119509...
    //this could go into a yml...


    $query = $this->getFindQueryWithWhere( $this->params['query'] );
    $statement = $this->pdo->prepare( $query );
    $statement->execute( );

    return $statement->fetchAll(  PDO::FETCH_ASSOC );
  }

  private function getFindQueryWithWhere( $whereClause )
  {
    $query = "
    SELECT 
      f.film_id, 
      f.title,
      f.duration runtime,
      cr.code as age_rating,
      r.release_date,
      (
        SELECT GROUP_CONCAT( DISTINCT gt.genre SEPARATOR ', ' ) as genre
        FROM genre g2
        LEFT JOIN genre_title gt ON g2.genre_id = gt.genre_id
        WHERE g2.film_id = f.film_id
      ) genre,
      f.image_id,
      (
        SELECT group_concat(DISTINCT CONCAT( p2.name_first, ' ', p2.name_second) SEPARATOR ', ' ) as name
        FROM film_map map2
        LEFT JOIN role r2  ON map2.role_id = r2.role_id
        LEFT JOIN person p2 on map2.person_id = p2.person_id
        WHERE map2.film_id = f.film_id
        AND r2.role = 'd'
      ) director,
      (
      SELECT group_concat(DISTINCT CONCAT( p2.name_first, ' ', p2.name_second) SEPARATOR ', ' ) as name
        FROM film_map map2
        LEFT JOIN role r2  ON map2.role_id = r2.role_id
        LEFT JOIN person p2 on map2.person_id = p2.person_id
        WHERE map2.film_id = f.film_id
        AND r2.role= 'cast'
      ) cast,
      (
      SELECT group_concat(DISTINCT CONCAT( p2.name_first, ' ', p2.name_second) SEPARATOR ', ' ) as name
        FROM film_map map2
        LEFT JOIN role r2  ON map2.role_id = r2.role_id
        LEFT JOIN person p2 on map2.person_id = p2.person_id
        WHERE map2.film_id = f.film_id
        AND r2.role= 'sc'
      ) screenwriter

    FROM cinema c
    JOIN listing l ON ( l.cinema_id = c.cinema_id AND l.date >= date( NOW() ) )
    JOIN film f ON ( f.film_id = l.film_id )
    LEFT JOIN film_certificates fc ON ( f.film_id = fc.film_id )
    LEFT JOIN certificates cr ON ( fc.certificate_id = cr.id )
    LEFT JOIN film_release_date r ON ( f.film_id = r.film_id AND c.country_id = r.country_id )
    WHERE %where%
    GROUP BY f.film_id
    ";
    $query = str_replace( '%where%', $whereClause, $query );
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
    $review_type_id = $this->params['review_type_id'];
    $query = "
      SELECT review_type_id , text , rating ,
      (
        CASE
         WHEN review_type_id = " . $review_type_id . " THEN 10
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

  /**
   * Set defaults
   * @param array $params
   */
  private function fillConfig( $params )
  {
      // Set Database
      $this->databaseConfig = $params[ 'database' ];
      
      // Save $params for Other variables
      $this->params = $params;
  }

  /**
   * Estabilish Database Connection
   */
  private function preparePdo()
  {
    $pdo  = new PDO(
            $this->databaseConfig[ 'dsn' ],
            $this->databaseConfig[ 'username' ],
            $this->databaseConfig[ 'password' ],
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
    );

    $this->pdo = $pdo;

    $statement = $this->pdo->prepare( "SET NAMES UTF8 " );
    if( $statement ) $statement->execute();
  }

}
?>
