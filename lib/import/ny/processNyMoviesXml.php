<?php
/**
 * Class to parse NY Movie XML feeds
 *
 * @package projectn
 * @subpackage ny.import.lib
 * @author Tim Bowler <timbowler@timeout.com>
 *
 * @copyright Timeout Communications Ltd
 * @version 1.0.1
 *
 */

class processNyMoviesXml extends processXml
{

    private $_movies;
    private $_totalMovies;
    private $_poi;
    private $_occurances;

    /**
     * Constructor method
     *
     */
    public function  __construct($sourceFile)
    {
      parent::__construct($sourceFile);
    }


    /**
     * Sets the xpath to the movies and the total amount
     *
     * @param string $moviesPath The xpath to movies
     */
    public function setMovies($moviesPath)
    {
      $this->_movies = $this->xmlObj->xpath($moviesPath);
      $this->_totalMovies = count($this->_movies);
      return $this;
    }

    public function setPoi($poiPath)
    {
      $this->_poi = $this->xmlObj->xpath($poiPath);
      return $this;

    }

    public function setOccurances($occurancesPath)
    {
      $this->_occurances = $this->xmlObj->xpath($occurancesPath);
      return $this;
    }


    /**
     * Get the Movies
     *
     * @return array Array of xml objects
     */
    public function getMovies()
    {
      return $this->_movies;
    }

    public function getPoi()
    {
      return $this->_poi;
    }

    public function getOccurances()
    {
      return $this->_occurances;
    }
}
?>