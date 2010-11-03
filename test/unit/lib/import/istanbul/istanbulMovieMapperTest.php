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
class istanbulMoviesMapperTest extends PHPUnit_Framework_TestCase
{
  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    ProjectN_Test_Unit_Factory::add( 'Vendor', array(
      'city' => 'istanbul',
      'inernational_dial_code' => '+90',
      'language' => 'tr',
      'country_code' => 'tr',
      'country_code_long' => 'TUR',
      'utf_offset' => '1',
    ) );
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
    $xml = simplexml_load_file( TO_TEST_DATA_PATH.'/istanbul/movies.xml' );
    $importer->addDataMapper( new istanbulMovieMapper( $xml ) );
    $importer->run();

    $movies = Doctrine::getTable( 'Movie' )->findAll();

    $firstMovie = $movies[0];
    $this->assertEquals( 1, $firstMovie['vendor_movie_id'] );
    $this->assertEquals( 'Gezegen 51', $firstMovie['name'] );
    $this->assertEquals( 'Buradan çok uzakta, hayatın basit, çocukların mutlu ve herkesin her şeyden memnun olduğu bir yerde geçiyor film. Hep öcü olarak akıllarda yer etmiş uzaylıların yerine insan geçiyor bu sefer. Yani Gezegen 51’e insanlar geliyor. Normalde bu tarz filmlerde dünyayı uzaylıların istila etmesine alışığız, ‘Gezegen 51’de bunun tam tersi oluyor; Gezegen 51’i dünyalılar istila ediyor. Bunun akabinde de komik olaylar vuku buluyor. Film için ilk olarak ‘Planet One’ adı uygun görülmüş ancak daha sonra ‘Planet 51’ olarak değiştirilmiş. Çok taze bir film değil bu arada ‘Gezegen 51’, yurt dışında 2009’da gösterime girmiş bir film. Memleketteki animasyon meraklıları da filme ilgi gösterecektir diye tahmin ediyoruz. Ancak daha çok çocuklara yönelik bir film olduğunu belirtelim, zaten dublajlı olarak vizyona giriyor.', $firstMovie['review'] );
    $this->assertEquals( 'http://www.sonypictures.com/sonywonder/planet51/', $firstMovie['url'] );
    $this->assertEquals( $firstMovie['Vendor']->getUtcOffset(), $firstMovie['utf_offset'] );
    $this->assertEquals( 'istanbul', $firstMovie['Vendor']['city'] );

    $this->assertEquals( 5, $movies->count() );
  }
}
