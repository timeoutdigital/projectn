<?php
/**
 * Import all the UAE movies
 *
 * @package projectn
 * @subpackage uae.import.lib
 *
 *
 * @author Tim Bower <timbowler@timeout.com>
 *
 * @version 1.0.1
 *
 * @todo Create unit tests refs #116
 */
class ImportUaeMovies {
    //put your code here

    public $xmlObj;

    public $vendorObj;

    public $movieLoggerObj;

    public $moviePoi;

    
    /**
     * Consrtuctor 
     *
     * @param SimpleXMLElement $xmlObj
     * @param Vendor $vendorObj 
     */
    public function  __construct(SimpleXMLElement $xmlObj, Vendor $vendorObj)
    {
        $this->xmlObj = $xmlObj;
        $this->vendorObj = $vendorObj;
        $this->movieLoggerObj = new logImport($vendorObj);
        $this->movieLoggerObj->setType('movie');
    }

    /**
     * Import All of the movies
     */
    public function import()
    {

        $filmsObj = $this->xmlObj->xpath('//films');

        foreach($filmsObj[0] as $film)
        {
            $this->importMovies($film);
        }

        //Save the logger
        $this->movieLoggerObj->save();
    }

    /**
     * Import the movies
     *
     * @param SimpleXMLElement $xmlObj Movie XML node
     */
    public function importMovies(SimpleXMLElement $xmlObj)
    {
        //Get the movie object
        $movieObj                       = $this->getMovie($xmlObj);

        $isNew = $movieObj->isNew();

        $movieObj['vendor_movie_id']    = (int) $xmlObj['id'];
        $movieObj['name']               = (string) $xmlObj->{'name'};
        $movieObj['plot']               = (string) $xmlObj->{'description'};
        $movieObj['review']             = (string) $xmlObj->{'full_review'};
        $movieObj['url']                = (string) $xmlObj->{'website'};
        $movieObj['Vendor']             = $this->vendorObj;
        $movieObj['utf_offset']         = $this->vendorObj->getUtcOffset();



        //$url = (string) $xmlObj->{'website'};
        //$url = stringTransform::formatUrl($url);
        //echo $url;
        /**
         * @todo Add the following to a test
         *
         * "G,English,Action,Comedy,1 star"
         * "PG15,English,Comedy,Romance"
         * "18+,English,Action,Drama,4 star"
         * "18+,English,Crime,Drama,Thriller,2 star"
         * "PG15,English,Action,Adventure,Drama,Sci-Fi,Thriller,2 star"
         *
         */
        $tagsArray = explode(',', (string) $xmlObj->{'tags'});
        $movieObj['age_rating']         = $tagsArray[0];

        //Add movie rating
        $this->setRating($tagsArray, $movieObj);
       
        //Add the movie Genres
        $this->addGenres($tagsArray, $movieObj);

        //Log changed fields if any?
        $logChangedFields = $movieObj->getModified();

        //Add the language
        if(strtolower($tagsArray[1]) == 'english' || strtolower($tagsArray[1]) == 'arabic')
        {
            $movieObj->addProperty('Language', $tagsArray[1]);
        }

        //Save the object
        try
        {
            $movieObj->save();
        }
        catch(Exception $e)
        {
            $log =  "Error processing Movie: \n Vendor = ". $this->vendorObj['city']." \n type = Movies \n vendor_poi_id = ".$xmlObj['id']. " \n";
            $this->movieLoggerObj->addError($e, $movieObj, $log);
        }

        //Count the item
       ( $isNew ) ? $this->movieLoggerObj->countNewInsert() : $this->movieLoggerObj->addChange( 'update', $logChangedFields );

    }


    /**
     * set the movie rating
     *
     * @param array $tagsArray
     * @param Movie $movieObj
     */
    public function setRating($tagsArray, $movieObj)
    {
         $rating = end($tagsArray) ;

        if(preg_match('/(?<digit>\d+) (?<name>\w+)/', $rating))
        {
            $ratingArray = explode(' ', $rating);

            if(count($ratingArray) > 0)
            {
                //Some rating are > 5 an we only allow up to 5
                if($ratingArray[0] > 5){
                     $movieObj['rating'] = 5;
                }
                else
                {
                     $movieObj['rating'] = $ratingArray[0];
                }
            }
        }
    }

    /**
     *   Add a genre to the movie
     *
     *  @param aray $tagsArray The array of the tags
     *  @param Movie The movie object
     *
    */
    public function addGenres($tagsArray, Movie $movieObj)
    {
         //Add all other tags from tags node
        for($i=2; $i < (count($tagsArray)-1); $i++ )
        {
           $movieObj->addGenre($tagsArray[$i]);
        }
    }


    /**
     * Get a movie object
     *
     * @param SimpleXMLElement $xmlObj
     */
    public function getMovie(SimpleXMLElement $xmlObj)
    {
        $movieObj =  Doctrine::getTable('Movie')->findOneByVendorMovieIdAndVendorId((int) $xmlObj['id'], $this->vendorObj['id']);

        if(!$movieObj)
        {
            $movieObj = new Movie();
        }

        return $movieObj;
    }
}
?>
