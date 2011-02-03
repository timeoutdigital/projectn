<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../bootstrap.php';

/**
 * Test class for DataSource.
 * Generated by PHPUnit on 2010-01-28 at 10:58:00.
 */
class DataMapperTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var DataSource
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testGetMapMethods()
  {
    $this->object = new UnitTestDataMapper();
    $this->assertEquals(4, count( $this->object->getMapMethods() ) );
  }

  /**
   * @todo Implement testMapPois().
   */
  public function testMapFunctionsNotifyImporter()
  {
    $importer = new Importer;

    $this->object = new UnitTestDataMapper( );
    $this->object->setImporter( $importer );

    $this->object->mapPois();
    $this->object->mapEvents();
    $this->object->mapEventOccurrences();
    $this->object->mapMovies();
  }

  /**
   * test that the map functions are called in the order they are declared
   */
  public function testMapFunctionsOrder()
  {
    $importer = new Importer;
    $dataMapper = new UnitTestOrderDataMapper();
    $importer->addDataMapper($dataMapper);

    $importer->run();
    $this->assertEquals( array( 'one', 'two', 'three', 'four' ), $dataMapper->calls );
  }

  public function testApplyFeedGeoCodesHelperExceptionExpetcted()
  {
      $dataMapper = new UnitTestDataMapper();
      $this->setExpectedException( 'Exception' );
      $dataMapper->testApplyFeedGeoCodesHelper( null, 0, 0);
  }
  public function testApplyFeedGeoCodesHelperWrongRecordType()
  {
      $dataMapper = new UnitTestDataMapper();
      $this->setExpectedException( 'Exception' );
      $dataMapper->testApplyFeedGeoCodesHelper( new Event, 0, 0);
  }

  public function testApplyFeedGeoCodesHelperValid()
  {
      $poi = ProjectN_Test_Unit_Factory::add( 'Poi' );
      
      $dataMapper = new UnitTestDataMapper();
      $dataMapper->testApplyFeedGeoCodesHelper( $poi, null, 0 ); // null is invalid but when you cast it as (float) it will become 0
  }
}

class UnitTestDataMapper extends DataMapper
{
  public function mapPois()
  {
    $this->notifyImporter( new Poi() );
  }
  public function mapEvents()
  {
    $this->notifyImporter( new Poi() );
  }
  public function mapEventOccurrences()
  {
    $this->notifyImporter( new Poi() );
  }
  public function mapMovies()
  {
    $this->notifyImporter( new Poi() );
  }

  public function  testApplyFeedGeoCodesHelper(Poi $record, $latitude, $longitude)
  {
      $this->applyFeedGeoCodesHelper($record, $latitude, $longitude);
  }
}

class UnitTestOrderDataMapper extends DataMapper
{
  public $calls = array();

  public function mapOne()
  {
    $this->calls[] = 'one';
    $this->notifyImporter( new Poi() );
  }
  public function mapTwo()
  {
    $this->calls[] = 'two';
    $this->notifyImporter( new Poi() );
  }
  public function mapThree()
  {
    $this->calls[] = 'three';
    $this->notifyImporter( new Poi() );
  }
  public function mapFour()
  {
    $this->calls[] = 'four';
    $this->notifyImporter( new Poi() );
  }
}
?>
