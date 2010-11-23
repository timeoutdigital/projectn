<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';

/**
 *
 * Test for UAE Feed Film Mapper
 *
 * @package test
 * @subpackage uae.import.lib.unit
 *
 * @author Peter Johnson <peterjohnson@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.1.0
 *
 */

class UAEFeedFilmsMapperTest extends PHPUnit_Framework_TestCase
{
    private $vendor;

    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');
        error_reporting( E_ALL );

        $this->vendor = Doctrine::getTable('Vendor')->findOneByCity( 'dubai' );
        
        $importer = new Importer();
        $importer->addDataMapper( new UAEFeedFilmsMapper( $this->vendor, $this->_getParams() ) );
        $importer->run();
    }

    private function _getParams()
    {
        return array(
            'type' => 'movie',
            'curl'  => array(
                'classname' => 'CurlMock',
                'src' => TO_TEST_DATA_PATH . '/uae/dubai_film.xml',
                'xslt' => 'uae_films.xml'
             )
        );
    }

    /**
    * Tears down the fixture, for example, closes a network connection.
    * This method is called after a test is executed.
    */
    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    public function testMapBars()
    {
        // validate
        $movies = Doctrine::getTable( 'Movie' )->findAll();
        $this->assertEquals( 4, $movies->count() );

        $movie = $movies[0];

        // assert
        $this->assertEquals( '717', $movie['vendor_movie_id']);
        $this->assertEquals( 'The American', $movie['name']);
        $this->assertEquals( 'A roving assassin, Jack (George Clooney) has long lived in necessary and by-now-familiar solitude', $movie['plot']);
        $this->assertEquals( 'A roving assassin, Jack (George Clooney) has long lived in necessary', mb_substr($movie['review'],0,68) );
        $this->assertEquals( 'Anton Corbijn', $movie['director']);
        $this->assertEquals( '103', $movie['duration']);
        $this->assertEquals( 'http://www.theamericanthemovie.com', $movie['url']);
        $this->assertEquals( 'George Clooney, Irina Björklund, Lars Hjelm, Johan Leysen, Paolo Bonacelli, Giorgio Gobbi, Silvana Bosi, Thekla Reuten, Samuli Vauramo, Violante Placido', $movie['cast']);

        // analyse tags
        //15+,English,Drama,Thriller,3 star
        $this->assertEquals( '15+', $movie['age_rating']);
        $this->assertEquals( 'English', $movie['language']);
        $this->assertEquals( 3, $movie['rating']);

        $this->assertEquals( 2, $movie['MovieGenres']->count());
        $this->assertEquals( 'Drama', $movie['MovieGenres']['Drama']['genre']);
        $this->assertEquals( 'Thriller', $movie['MovieGenres']['Thriller']['genre']);

        // timeout link
        $this->assertEquals( 1, $movie['MovieProperty']->count());
        $this->assertEquals( 'http://www.timeoutdubai.com/films/reviews/17695-the-american', $movie['MovieProperty'][0]['value']);

        $movie = $movies[2];
        // assert
        $this->assertEquals( '716', $movie['vendor_movie_id']);
        $this->assertEquals( 'Charlie St. Cloud', $movie['name']);
        $this->assertEquals( 'It takes more than arithmetic to describe what’s wrong with Charlie St Cloud, but let’s start with a tally', $movie['plot']);
        $this->assertEquals( 'Burr Steers', $movie['director']);
        $this->assertEquals( '99', $movie['duration']);
        $this->assertEquals( 'http://www.charliestcloud.com', $movie['url']);
        $this->assertEquals( 'Zac Efron, Charlie Tahan, Amanda Crew, Augustus Prew, Donal Logue, Kim Basinger, Ray Liotta, Dave Franco, Matt Ward, Miles Chalmers, Adrian Hough', $movie['cast']);

        // analyse tags
        //TBA,English,Drama,Romance
        $this->assertEquals( 'TBA', $movie['age_rating']);
        $this->assertEquals( 'English', $movie['language']);
        $this->assertEquals( null, $movie['rating']);

        $this->assertEquals( 2, $movie['MovieGenres']->count());
        $this->assertEquals( 'Drama', $movie['MovieGenres']['Drama']['genre']);
        $this->assertEquals( 'Romance', $movie['MovieGenres']['Romance']['genre']);

        // timeout link
        $this->assertEquals( 1, $movie['MovieProperty']->count());
        $this->assertEquals( 'Timeout_link', $movie['MovieProperty'][0]['lookup']);


    }
}