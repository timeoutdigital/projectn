<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test class for LondonAPIRestaurantsMapper.
 * Generated by PHPUnit on 2010-02-08 at 13:02:30.
 */
class LondonAPIMoviesMapperTest extends PHPUnit_Framework_TestCase
{

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    Doctrine_Manager::connection()->setAttribute(Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_ALL);

    $vendor = new Vendor();
    $vendor['city'] = 'london';
    $vendor['language'] = 'en-GB';
    $vendor->save();
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  /**
   * test movies are mapped to movies
   */
  public function testMapPoi()
  {
    $limit = 11;

    $mapper = new LondonAPIMoviesMapper();

    $importer = new Importer();
    $mapper->setLimit( $limit );
    $importer->addDataMapper( $mapper );
    $importer->run();

    $movieResults = Doctrine::getTable('Movie')->findAll();

    $this->assertEquals( $limit, $movieResults->count() );

    $movie = $movieResults[0];

    $this->assertFalse( empty( $movie[ 'vendor_id' ] ) );
    $this->assertFalse( empty( $movie[ 'vendor_movie_id' ] ), 'vendor_movie_id should not be empty' );
    $this->assertFalse( empty( $movie[ 'name' ] ),            'name should not be empty' );
    $this->assertFalse( empty( $movie[ 'plot' ] ),            'plot should not be empty' );
    //$this->assertFalse( empty( $movie[ 'review' ] ),          'review should not be empty' );
    $this->assertFalse( empty( $movie[ 'url' ] ),             'url should not be empty' );

    $this->assertGreaterThan( 0, count( $movie['MovieProperty'] ) );
  }
}