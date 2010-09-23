<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';

/**
 * Test for Singapore Poi Mapper
 *
 * @package test
 * @subpackage
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class singaporeMovieMapperTest extends PHPUnit_Framework_TestCase
{

    /**
     * Store Temporary File name
     * @var string
     */
    private $tmpFile;
    /**
    * Sets up the fixture, for example, opens a network connection.
    * This method is called before a test is executed.
    */
    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');

        // Setup Tmp File
        $this->tmpFile  = TO_TEST_DATA_PATH . '/singapore/new_movies_list_tmp.xml';
        $this->createTmpFile();
    }

    /**
    * Tears down the fixture, for example, closes a network connection.
    * This method is called after a test is executed.
    */
    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();

        // Remove the Files
        if(file_exists( $this->tmpFile ) )
        {
            unlink ( $this->tmpFile );
        }
    }

    public function testMapMovie()
    {
        // Get the XML
        $dataSource = new singaporeDataSource( 'movie', 'CurlMock', null, null, $this->tmpFile );
        $xml = $dataSource->getXML();

        // create Data Mapper
        $dataMapper = new singaporeMovieMapper( $xml );

        // Run Test Import
        $importer = new Importer();
        $importer->addDataMapper( $dataMapper );
        $importer->run();

        // Get all the movies
        $movies = Doctrine::getTable( 'Movie' )->findAll();
        $this->assertEquals( 3, $movies->count(), 'there Should be three movies as XML has 3 Movies Node' );

        // assert Movies Details
        $movie = $movies[0];
        $this->assertEquals( '853', $movie['vendor_movie_id']);
        $this->assertEquals( 'Haunted Changi', $movie['name']);
        $this->assertEquals( 'Set to be a huge cult hit, a young crew of local filmmakers explore', substr( $movie['review'], 0, 67) );
        $this->assertEquals( 'Andrew Lau', $movie['director']);
        $this->assertEquals( 'PG', $movie['age_rating']);
        $this->assertEquals( 'http://www.facebook.com/hauntedchangi', $movie['url']);

        // Genre
        $this->assertEquals( 1, $movie['MovieGenres']->count());
        $this->assertEquals( 'Horror', $movie['MovieGenres']['Horror']['genre']); // This is wrong! $movie['MovieGenres']['NoneCat'] == NoneCat ???

        // 2nd Movie
        $movie = $movies[1];
        $this->assertEquals( '848', $movie['vendor_movie_id']);
        $this->assertEquals( 'Cats & Dogs 2: Revenge of Kitty Galore', $movie['name']);
        $this->assertEquals( 'A James Bond', mb_substr( $movie['review'], 0, 12) );
        $this->assertEquals( 'Voices of James Marsden, Nick Nolte, Christina Applegate, Katt Williams, Bette Midler, Alec Baldwin', $movie['cast']);
        $this->assertEquals( 'Brad Peyton', $movie['director']);
        $this->assertEquals( 'G', $movie['age_rating']);
        $this->assertEquals( 'http://catsanddogsmovie.warnerbros.com/', $movie['url']);

        // Genre
        $this->assertEquals( 1, $movie['MovieGenres']->count());
        
    }

    /**
     * Create a temporary Files with updated Path in Link
     */
    private function createTmpFile()
    {
        if(file_exists( $this->tmpFile ) )
        {
            unlink ( $this->tmpFile );
        }

        // create new File
        $fileData   = file_get_contents( TO_TEST_DATA_PATH . '/singapore/new_movies_list.xml' );

        // Update Links
        $xml        = simplexml_load_string( $fileData );

        // update Path
        for( $i = 0; $i < 3; $i++ )
        {
            $xml->channel->item[$i]->link    = TO_TEST_DATA_PATH . '/singapore/' . (string) $xml->channel->item[$i]->link;
            $xml->channel->item[$i]->guid    = TO_TEST_DATA_PATH . '/singapore/' . (string) $xml->channel->item[$i]->guid;
        }


        file_put_contents( $this->tmpFile, $xml->saveXML() );
    }
}
?>
