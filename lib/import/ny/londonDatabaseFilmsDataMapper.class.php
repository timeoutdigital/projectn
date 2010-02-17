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
   * @var PDO
   */
  protected $pdo;
  
  /**
   * @var Vendor
   */
  protected $vendor;

  public function  __construct( Vendor $vendor )
  {
    $this->vendor = $vendor;
    $this->pdo  = new PDO( $this->dsn, $this->dbUserName, $this->dbPwd );
  }
  
  public function mapMovies()
  {

    foreach( $this->getFilmsFromLondonDatabase() as $data )
    {
      
      $movie = $this->getRecord('Movie', 'vendor_movie_id', $this->vendor['id'] );

      $movie['vendor_id']  = $this->vendor['id'];
      $movie['utf_offset'] = 0;//@todo use vendor->getUtcOffset( --- );

      $movie['name'] = $data[ 'title' ];
      $movie['vendor_movie_id'] = $data[ 'film_id' ];
      $movie['age_rating'] = $data[ 'age_rating' ];
      
      $review = $this->getReview( $data[ 'film_id' ] );
      
      $movie['review'] = $review[ 'text' ];
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
        $image_id = $data[ 'image_id' ];
        $movieMedia = new MovieMedia();

        $movieMedia[ 'mime_type' ] = 'image/jpeg';
        $movieMedia[ 'media_url' ] = 'http://www.timeout.com/img/' . $image_id . '/w398/image.jpg';
        $movie[ 'MovieMedia' ][] = $movieMedia;
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
    $vendorToStateMap = array( 'ny' => '%NY', 'chicago' => '%IL' );

    if( !key_exists( $this->vendor->city ,$vendorToStateMap ) )
    {
      throw new Exception( $this->vendor['city'] . ' vendor is not supported.' );
    }

    $state = $vendorToStateMap[ $this->vendor[ 'city' ] ];
     
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
    
    $statement = $this->pdo->prepare( $query );
    $statement->execute( array( $state ) );

    return $statement->fetchAll(  PDO::FETCH_ASSOC );
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
      SELECT review_type_id , text , rating
      (
        CASE
         WHEN review_type_id = 3 THEN 10
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
}
?>
