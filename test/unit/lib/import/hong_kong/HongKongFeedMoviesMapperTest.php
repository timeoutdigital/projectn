<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';
/**
 * Test of Hong Kong Feed Movies Mapper import.
 *
 * @package test
 * @subpackage hong_kong.import.lib.unit
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class HongKongFeedMoviesMapperTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var HongKongFeedMoviesMapper
   */
  protected $dataMapper;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    Doctrine::loadData('data/fixtures');

    // get vendor
    $vendor = Doctrine::getTable( 'Vendor' )->findOneByCity('hong kong');

    $params = array('type' => 'Movie', 'datasource' => array( 'classname' => 'CurlMock', 'url' =>  TO_TEST_DATA_PATH . '/hong_kong/hong_kong_movies_short.xml' ) );

    $importer = new Importer();
    $importer->addDataMapper( new HongKongFeedMoviesMapper( $vendor, $params ) );
    $importer->run();

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

    $movies = Doctrine::getTable('Movie')->findAll();

    // Check IMPORTED COUNT
    $this->assertEquals( 5, $movies->count() );

    $movie = $movies->getFirst();

    $this->assertEquals( 1739,  $movie['vendor_movie_id'] );
    $this->assertEquals( 'The Savages', $movie['name'] );
    $this->assertEquals( '<p>The strength of<em> The Savages</em> lies in its sensitivity to the awkward,', mb_substr( $movie['review'],0 , 79, 'UTF-8' ) );

    $this->assertEquals( '+08:00', $movie['utf_offset'] );
    $this->assertGreaterThan( 0, $movie[ 'MovieProperty' ]->count() );
    $this->assertEquals( "Timeout_link", $movie[ 'MovieProperty' ][0]['lookup'] );
    $this->assertEquals( "http://www.timeout.com.hk/film/features/1739/the-savages.html", $movie[ 'MovieProperty' ][0]['value'] );

    $this->assertGreaterThan( 0, $movie[ 'Vendor' ]->count() );

    // Test the LAST
     $movie = $movies->getLast();

    $this->assertEquals( 1741,  $movie['vendor_movie_id'] );
    $this->assertEquals( 'Over Her Dead Body', $movie['name'] );
    $this->assertEquals( '<p class="MsoNormal">Overplanning bride-to-be Kate (Longoria Parker) goes apeshit when the angel-shaped ice sculpture she ordered', mb_substr( $movie['review'],0 , 129, 'UTF-8' ) );

    $this->assertEquals( '+08:00', $movie['utf_offset'] );
    $this->assertGreaterThan( 0, $movie[ 'MovieProperty' ]->count() );
    $this->assertEquals( "Timeout_link", $movie[ 'MovieProperty' ][0]['lookup'] );
    $this->assertEquals( "http://www.timeout.com.hk/film/features/1741/over-her-dead-body.html", $movie[ 'MovieProperty' ][0]['value'] );

    $this->assertEquals( "Short_description", $movie[ 'MovieProperty' ][1]['lookup'] );
    $this->assertEquals( "1 star", mb_substr($movie[ 'MovieProperty' ][1]['value'],0,6,'UTF-8' ) );
    
    $this->assertGreaterThan( 0, $movie[ 'Vendor' ]->count() );

    // #807 - Media Images do not download anymore, they are downloaded in another task...
    
  }

}
?>
