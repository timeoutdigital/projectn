<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test of Barcelona Venues Mapper import.
 *
 * @package test
 * @subpackage russia.import.lib.unit
 *
 * @author Peter Johnson <peterjohnson@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class barcelonaMoviesMapperTest extends PHPUnit_Framework_TestCase
{
  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    Doctrine::loadData('data/fixtures');

    $vendor = Doctrine::getTable('Vendor')->findOneByCityAndLanguage( "barcelona", "ca" );
    $vendor->save();

    $this->vendor = $vendor;

    $this->object = new barcelonaMoviesMapper( simplexml_load_file( TO_TEST_DATA_PATH . '/barcelona/movies_trimmed.xml' ) );
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testMapMovies()
  {
    $importer = new Importer();
    $importer->addDataMapper( $this->object );
    $importer->run();

    $movies = Doctrine::getTable( 'Movie' )->findAll();
    $this->assertEquals( 8, $movies->count() );

    $movie = $movies[0];

    $this->assertEquals( 8442,  $movie['vendor_movie_id'] );
    $this->assertEquals( 'Aurora boreal', $movie['name'] );
    $this->assertEquals( "Basada en el 'best seller' d'Åsa Larsson, la pel·lícula comença a la ciutat de Kiruna amb l'aparició del cos mutilat de Viktor Strandgård, el predicador més famós de Suècia. La germana de la víctima troba el cadàver i les sospites cauen sobre ella. Desesperada, demana ajuda i es comença a investigar el cas. A Kiruna molta gent té alguna cosa a amagar i la neu no trigarà a tenyir-se de sang. M.L", $movie['plot'] );
    $this->assertEquals( '', $movie['review'] );
    $this->assertEquals( '', $movie['url'] );
    $this->assertEquals( 'Izabella Scorupco, Jakob Eklund, Mikael Persbrandt i Suzanne Reuter', $movie['cast'] );
    $this->assertEquals( 'Suèca i Finlàndia', $movie['country'] );
    $this->assertEquals( 'suec', $movie['language'] );
    $this->assertEquals( $this->vendor->getUtcOffset(), $movie['utf_offset'] );
    $this->assertEquals( $this->vendor, $movie['Vendor'] );

    $this->assertEquals( 1, count( $movie['MovieGenres'] ) );
    $this->assertTrue( isset( $movie['MovieGenres']['Thriller'] ) );
    $this->assertEquals( 'Thriller', $movie['MovieGenres']['Thriller']['genre'] );

    $this->assertEquals( 3, count( $movie['MovieProperty'] ) );

    $this->assertEquals( 'Timeout_link', $movie['MovieProperty'][0]['lookup'] );
    $this->assertEquals( 'http://www.timeout.cat/barcelona/ca/s/cinema', $movie['MovieProperty'][0]['value'] );

    $this->assertEquals( 'Original_title', $movie['MovieProperty'][1]['lookup'] );
    $this->assertEquals( 'Solstorm', $movie['MovieProperty'][1]['value'] );

    $this->assertEquals( 'Year', $movie['MovieProperty'][2]['lookup'] );
    $this->assertEquals( '2007', $movie['MovieProperty'][2]['value'] );
  }
}
?>
