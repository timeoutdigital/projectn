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
   * @var array
   */
  protected $databaseConfig;

  /**
   * @var array
   */
  protected $cityConfig;

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
   * table 'review' of database 'film_convert'
   */
  public function  __construct( Vendor $vendor )
  {
    $this->vendor = $vendor;
    $this->fillConfig();
    $this->preparePdo();
    $this->dataMapperHelper = new projectNDataMapperHelper($vendor);
  }
  
  public function mapMovies()
  {
    foreach( $this->getFilmsFromLondonDatabase() as $data )
    {
      $movie = $this->dataMapperHelper->getMovieRecord( $data[ 'film_id' ] );

      $movie['vendor_id']  = $this->vendor['id'];
      $movie['utf_offset'] = $this->vendor->getUtcOffset();

      $movie['name'] = $data[ 'title' ];
      $movie['vendor_movie_id'] = $data[ 'film_id' ];

      $movie->addProperty('name', $data['title'] );
      $movie->addProperty('Age_rating', $data[ 'age_rating' ]);
      $movie->addProperty('Director', $data[ 'director' ]);
      $movie->addProperty('Cast', $data[ 'cast' ]);
      $movie->addProperty('Year', $data[ 'year' ]);

      if( $data['runtime'] )
        $movie->addProperty('Runtime', $data[ 'runtime' ]);
      
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

      /*@todo sort out the test and uncomment this
      if( $data[ 'image_id' ] > 0 )
      { 
        try
        {
            $movie->addMediaByUrl( 'http://www.timeout.com/img/' . $data[ 'image_id' ] . '/w398/image.jpg' );
        }
        catch( Exception $e )
        {
            $this->notifyImporterOfFailure( $e );
        }
      }
      */

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

    $query = $this->getQueryFromConfig();
    $statement = $this->pdo->prepare( $query );

    $param = $this->getQueryParamFromConfig();
    $statement->execute( array( $param ) );

    return $statement->fetchAll(  PDO::FETCH_ASSOC );
  }
  
  private function getFindByStateQuery()
  {
    return $this->getFindQueryWithWhere( "c.country_id = '931' AND  city_state LIKE ?" );
  }

  private function getFindByCountryQuery()
  {
    return $this->getFindQueryWithWhere( "c.country_id = ?" );
  }

  private function getFindQueryWithWhere( $whereClause )
  {
    $query = "
    SELECT 
      f.film_id, 
      f.title, 
      f.year_made year,
      f.duration runtime,
      cr.code as age_rating,
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
      ) cast

    FROM cinema c
    JOIN listing l ON l.cinema_id = c.cinema_id 
      AND date >= date( NOW() )
    JOIN film f ON f.film_id = l.film_id
    JOIN film_certificates fc ON f.film_id = fc.film_id
    JOIN certificates cr ON fc.certificate_id = cr.id
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
    $review_type_id = $this->cityConfig['params']['review_type_id'];
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

  private function fillConfig()
  {
    $config = sfYaml::load( sfConfig::get( 'sf_config_dir' ) . '/londonDatabaseFilm.yml' );

    $environment = $this->getEnvironment();
    $config      = $config[ $environment ];

    $this->databaseConfig = $config[ 'database' ];
    $this->cityConfig = $config[ 'cities' ][ $this->getCityLanguage() ];
  }

  private function getEnvironment()
  {
    $environment = sfConfig::get( 'sf_environment' );
    $environment = preg_replace( '/dev|prod/', 'all', $environment );
    return $environment;
  }

  private function preparePdo()
  {
		$pdo  = new PDO(
			$this->databaseConfig[ 'dsn' ],
			$this->databaseConfig[ 'username' ],
			$this->databaseConfig[ 'password' ]
		);
    
		$this->pdo = $pdo;

    $statement = $this->pdo->prepare( "SET NAMES UTF8 " );
    if( $statement ) $statement->execute();
  }

  private function getQueryFromConfig()
  {
    $method = 'get' . ucfirst( $this->cityConfig['query'] ) . 'Query';
    return $this->$method();
  }

  private function getQueryParamFromConfig()
  {
    $cityLanguage = $this->getCityLanguage();

    $param = null;
    switch( $this->cityConfig['query'] )
    {
      case 'findByCountry':
        $param = $this->cityConfig['params']['country_id'];
        break;
      case 'findByState':
        $param = $this->cityConfig['params']['state'];
        break;
      default:
        throw new Exception( 'query "findByCountry" is not supported. Please change the Yaml config to use either "findByCountry" or "findByState".' );
    }

    return $param;
  }

  private function getCityLanguage()
  {
    $cityLanguage = $this->vendor[ 'city' ] . '_' . $this->vendor[ 'language' ];
    $cityLanguage = preg_replace( '/[^a-z]/i', '_', $cityLanguage );
    $cityLanguage = strtolower( $cityLanguage );
    return $cityLanguage;
  }
}
?>
