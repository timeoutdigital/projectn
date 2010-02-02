<?php
/**
 * Import Movies XML feed from Leo
 *
 * @package projectn
 * @subpackage ny.import.lib
 * @author Tim Bowler <timbowler@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class importNyMovies
{
  /**
   * @var SimpleXML object
   */
  private $_moviesObj;

  /**
   * @var Object Vendor
   */
  private $_vendorObj;

  /**
   * @var Object
   */
  private $_poiObj;

  /**
   * @var Object
   */
  private $_occurancesObj;

  /**
   * Store a movie logger
   *
   * @var object
   */
  private $_movieLoggerObj;


  /**
   * Store a poi logger
   *
   * @var object
   */
  private $_poiLoggerObj;

  /**
   *
   * @var <type> 
   */
  private $_currentPois;

  /**
   * @var <type>
   */
  private $_currentMovies;

  /**
   * Construct
   */
  public function  __construct( $movies, $vendorObj )
  {
    $this->_moviesObj = $movies->getMovies();
    $this->_poiObj = $movies->getPoi();
    $this->_occurancesObj = $movies->getOccurances();
    $this->_vendorObj = $vendorObj;
    $this->_currentPois = Doctrine::getTable('Poi')->getPoiByVendor($vendorObj['city']);
    $this->_movieLoggerObj = new logger($vendorObj, 'movie');
    $this->_poiLoggerObj = new logger($vendorObj, 'poi');
    $this->_currentMovies = Doctrine::getTable('Movie')->getMovieByVendor($vendorObj['city']);
 
  }

  /**
   * Insert the occurances
   */
  public function importMovies()
  {
      // Store all movies ID to lookup
      $movieFoundArray = array();

     // Start by looking over all of the occurances (Theaters)
     foreach($this->_occurancesObj as $occurance)
     {

         $occuranceDate = (string) $occurance['date'];
         $movieId = (string) $occurance['movieId'];
         $poiId = (string) $occurance['theaterId'];

         if(strcmp($prevPoiId, $poiId) != 0)
         {

          /**
           * Loop through all the poi's. Get only ones that we havn't got before
           */
           foreach($this->_poiObj as $poi)
           {
              //Get only the POI that is matched
             if($poiId == $poi['theaterId'] )
             {
                $poiObj = $this->setPoi($poi);
                break;
             }
           }

           //Set the previous ID to the current one
           $prevPoiId = $poiId;
         }//end if
            

        //Test if the movie as been found
       if(!in_array($movieId, $movieFoundArray))
       {
          $movieFoundArray[] = $movieId;

          /**
           * Get the movie for each occurance
           */
          foreach($this->_moviesObj as $movie)
          {
            if((int) $occurance['movieId'] ==  (int) $movie['movieId'])
            {
               $movieObj = $this->insertMovie($movie,$poiObj );
               $movieArray[] = $movieObj;

               break;
            }
          }
        }//end if        
     }//end outter occurance loop

     $this->_movieLoggerObj->saveStats();
     $this->_poiLoggerObj->saveStats();

     return true;
  }


  /**
   *
   * @param object Simple XML object
   * @return Object Doctrine Object
   *
   * @todo Refactor validation
   */
  public function setPoi($poi)
  {
    $poiChanged = false;
    $poiLog;

    //Check the current POIs to see if the details are the same
    foreach($this->_currentPois as $excistingPio)
    {
        if($excistingPio['vendor_poi_id'] === (string) $poi['theaterId'])
        {
            //Test the main base fields for a change
            if( $excistingPio['poi_name'] != (string) $poi->name)
            {
                $excistingPio['poi_name'] = (string) $poi->name;
                $poiLog .= "Name changed \n";
                $poiChanged = true;
            }

            if($excistingPio['street'] != (string) $poi->address->streetAddress->street)
            {
                $excistingPio['street'] = (string) $poi->address->streetAddress->street;
                $poiLog .= "Street changed \n";
                $poiChanged = true;
            }

            if($excistingPio['city'] != (string) $poi->address->city)
            {
               $excistingPio['city'] = (string) $poi->address->city;
               $poiLog .= "Address changed \n";
               $poiChanged = true;
            }

            if($excistingPio['country'] != (string) $poi->address->country)
            {
                $excistingPio['country'] = (string) $poi->address->country;
                $poiLog .= "Country changed \n";
                $poiChanged = true;
            }

            if($excistingPio['phone'] != (string) '+1'. $poi->telephone)
            {
                $excistingPio['phone'] = (string) '+1'. $poi->telephone;
                $poiLog .= "Phone changed \n";
                $poiChanged = true;
            }

            
            //Add the rest of data
            $excistingPio['vendor_poi_id'] = (string) $poi['theaterId'];
            $excistingPio['local_language'] = 'english';
            $excistingPio['vendor_id'] = $this->_vendorObj['id'];
            $excistingPio['longitude'] = (float) $poi->longitude;
            $excistingPio['latitude'] = (float) $poi->latitude;
            $excistingPio['zips'] = (string) $poi->address->postalCode;

            
            //Get and set the child category
            $categoriesArray = new Doctrine_Collection( Doctrine::getTable( 'PoiCategory' ) );
            $categoriesArray[] = Doctrine::getTable('PoiCategory')->findOneByName('theatre-music-culture');
            $excistingPio['PoiCategories'] =  $categoriesArray;

            $poiArray[] = $excistingPio;

            //If the record has changed, then re-save the object
            if($poiChanged)
            {
                $excistingPio->save();
                $poiChangeObj = new PoiChangesLog();
                $poiChangeObj['log'] = $poiLog;
                $poiChangeObj['poi_id'] = $excistingPio['id'];
                $poiChangeObj->save();

                //Count the update
                $this->_poiLoggerObj->countUpdate();
            }

            return $excistingPio;
        }//end if
     }//end foreach


     
    //Create the POI
    $poiObj = new Poi();
    $poiObj['poi_name'] = (string) $poi->name;
    $poiObj['vendor_poi_id'] = $poi['vendor_poi_id'];
    $poiObj['street'] = (string) $poi->address->streetAddress->street;
    $poiObj['city'] = (string) $poi->address->city;
    $poiObj['country'] = (string) $poi->address->country;
    $poiObj['phone'] = (string) '+1'. $poi->telephone;
    $poiObj['longitude'] = (float) $poi->longitude;
    $poiObj['latitude'] = (float) $poi->latitude;
    $poiObj['vendor_poi_id'] = (string) $poi['theaterId'];
    $poiObj['zips'] = (string) $poi->address->postalCode;
    $poiObj['vendor_id'] = $this->_vendorObj['id'];
    $poiObj['local_language'] = 'english';

    //Get and set the child category
    $categoriesArray = new Doctrine_Collection( Doctrine::getTable( 'PoiCategory' ) );
    $categoriesArray[] = Doctrine::getTable('PoiCategory')->findOneByName('theatre-music-culture');
    $poiObj['PoiCategories'] =  $categoriesArray;

    $poiArray[] = $poiObj;

    $poiObj->save();

    //Count the new insert
    $this->_poiLoggerObj->countNewInsert();

    return $poiObj;
  }


  /**
   * Insert Movies
   *
   * @param Object Simple XML object
   *
   * @return Object Doctrine
   *
   */
  public function insertMovie($movie, $poiObj)
  {


        //Check the current POIs to see if the details are the same
        foreach($this->_currentMovies as $excistingMovie)
        {
            if($excistingMovie['vendor_movie_id'] === (string) $movie['movieId'])
            {
                return $excistingMovie;
            }

        }//end foreach


        //start transaction
        $conn = Doctrine_Manager::connection();

        try {
          $conn->beginTransaction();

          $movieObj = new Movie();

          $movieObj['name'] = (string) $movie->officialTitle;
          $movieObj['vendor_id'] = (int) $this->_vendorObj->getId();
          $movieObj['review'] = (string) $movie->reviews->review->reviewText;
          $movieObj['plot'] = (string) $movie->synopsis;
          $movieObj['utf_offset'] = '-5:00';
          $movieObj['Poi'] = $poiObj;
          $movieObj['vendor_movie_id'] = (string) $movie['movieId'];

        //Try to find a url
          if($movie->officialURL)
          {
             $movieObj['url'] = (string) $movie->officialURL;
          }
          elseif ($movie->trailers->trailer->trailerURL)
          {
             $movieObj['url'] = (string) $movie->trailers->trailer->trailerURL;
          }

          //Save movie object to get PK
          $movieObj->save();

          //Get Genres
          $genreArray = new Doctrine_Collection(Doctrine::getTable('MovieGenre'));

          if($movie->genres)
          {
            foreach($movie->genres->genre as $genre)
            {
              $genreObj = $this->getGenre($genre);
              $genreArray[] = $genreObj;

            }
          }

          //Set the genres
          $movieObj->MovieGenres= $genreArray;

          //Set Any Properties
          $propertyArray = new Doctrine_Collection(Doctrine::getTable('MovieProperty'));

          for($i=0; $i< 1; $i++)
          {
            $moviePropertyObj = new MovieProperty();
            $moviePropertyObj['lookup'] = "movie-length";
            $moviePropertyObj['value'] = "$movie->runningTime";
            $moviePropertyObj['movie_id'] = $movieObj['id'];
            $propertyArray[] = $moviePropertyObj;
          }

           $movieObj['MovieProperty'] = $propertyArray;
           $movieObj->save();

           //Commit transaction
           $conn->commit();

           $this->_movieLoggerObj->countNewInsert();

           return $movieObj;
        }
        catch(Exception $e)
        {
            $conn->rollback(); // deletes all savepoints
             //echo ' problem add to log '. $e;
           // exit;
        }
  }

  
  /**
   * Get the movie genre$this->_movieLoggerObj->saveStats();
//      $thi
   *
   * @param string $name
   * @return object The Genre object
   */
  public function getGenre( $name )
  {
    $genreObj = Doctrine::getTable('MovieGenre')->getGenreByName($name);

    if($genreObj === false)
    {
      $genreObj = new MovieGenre();
      $genreObj['genre'] = (string) $name;
      
    }
     return $genreObj;
  }


  /**
   * Delete Object
   */
  public function  __destruct()
  {
//      $this->_movieLoggerObj->saveStats();
//      $this->_poiLoggerObj->saveStats();
  }
}
?>