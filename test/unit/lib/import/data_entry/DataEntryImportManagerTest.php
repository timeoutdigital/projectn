<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test of
 *
 * @package test
 * @subpackage london.import.lib.unit
 *
 * @author Emre Basala <emrebasala@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class DataEntryImportManagerTest extends PHPUnit_Framework_TestCase
{
  
  protected $vendor;

  protected function setUp()
  {
      ProjectN_Test_Unit_Factory::createDatabases();

      // Load Fixtures to create Vendors
      Doctrine::loadData('data/fixtures');
  }

  protected function tearDown()
  {
      ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testImportMovies()
  {
      // Set vendor
      $vendor = Doctrine::getTable( 'Vendor' )->findOneByCity( 'sydney' ); // fixtures Should created default vendors

      $this->assertNotNull( $vendor );  // Check vendor Exists
      
      $dataEntryImportManager = new DataEntryImportManager( 'sydney', TO_TEST_DATA_PATH . DIRECTORY_SEPARATOR . 'data_entry' . DIRECTORY_SEPARATOR );

      // Check Database for EMPTY
      $movies = Doctrine::getTable( 'Movie' )->findAll();
      $this->assertEquals( 0, $movies->count() );

      // Import Movies
      $dataEntryImportManager->importMovies();

      // Check for Import SUCESS
      $movies = Doctrine::getTable( 'Movie' )->findAll();
      $this->assertEquals( 2, $movies->count(), 'There are 2 Movies in Sydney XML' );

      // Check Values
      $movie = $movies[0];

      $this->assertEquals( 'Bright Star', $movie['name'] );
      $this->assertEquals( 'sample plot string goes there', $movie['plot'] );

      // Count MovieGenres
      $this->assertEquals( 2 , $movie['MovieGenres']->count(), 'This movie has 2 Genres' );
  }

  public function testImportMoviesValidCityNoXmlFile()
  {
      // Set vendor
      $vendor = Doctrine::getTable( 'Vendor' )->findOneByCity( 'moscow' ); // fixtures Should created default vendors

      $this->assertNotNull( $vendor );  // Check vendor Exists

      $dataEntryImportManager = new DataEntryImportManager( 'moscow', TO_TEST_DATA_PATH . DIRECTORY_SEPARATOR . 'data_entry' . DIRECTORY_SEPARATOR );

      // Check Database for EMPTY
      $movies = Doctrine::getTable( 'Movie' )->findAll();
      $this->assertEquals( 0, $movies->count() );

      // Set Expected Exception
      $this->setExpectedException( 'Exception' );
      
      // Import Movies, This should Throw FileNotFound Exception as Moscow xml not exists
      $dataEntryImportManager->importMovies();

  }

  public function testImportPois()
  {
      // Set vendor
      $vendor = Doctrine::getTable( 'Vendor' )->findOneByCity( 'sydney' ); // fixtures Should created default vendors

      $this->assertNotNull( $vendor );  // Check vendor Exists

      $dataEntryImportManager = new DataEntryImportManager( 'barcelona', TO_TEST_DATA_PATH . DIRECTORY_SEPARATOR . 'data_entry' . DIRECTORY_SEPARATOR );

      // Check Database for EMPTY
      $pois = Doctrine::getTable( 'Poi' )->findAll();
      $this->assertEquals( 0, $pois->count() );

      // Import Movies
      $dataEntryImportManager->importPois();

      // Check for Import SUCESS
      $pois = Doctrine::getTable( 'Poi' )->findAll();
      $this->assertEquals( 3, $pois->count(), 'There are 3 Pois in Barcelona XML' );

      // Check Values
      $poi = $pois[0];

      $this->assertEquals( 'Museu Arqueologia de Catalunya', $poi['poi_name'] );
      $this->assertEquals( 'Pg. Santa Madrona', $poi['street'] );

  }
}