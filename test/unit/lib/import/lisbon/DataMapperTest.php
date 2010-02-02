<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

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
    $importer = $this->getMock( 'Importer', array( 'onRecordMapped' ) );
    $importer->expects( $this->exactly( 4 ) )
             ->method( 'onRecordMapped' );

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
    $importer = $this->getMock( 'Importer', array( 'onRecordMapped' ) );
    $importer->expects( $this->exactly( 4 ) )
             ->method( 'onRecordMapped' );

    $dataMapper = new UnitTestOrderDataMapper();
    $importer->addDataMapper($dataMapper);

    $importer->run();
    $this->assertEquals( array( 'one', 'two', 'three', 'four' ), $dataMapper->calls );
  }
}

class UnitTestDataMapper extends DataMapper
{
  public function mapPois()
  {
    $this->notifyImporter( new RecordData('Poi') );
  }
  public function mapEvents()
  {
    $this->notifyImporter( new RecordData('Poi') );
  }
  public function mapEventOccurrences()
  {
    $this->notifyImporter( new RecordData('Poi') );
  }
  public function mapMovies()
  {
    $this->notifyImporter( new RecordData('Poi') );
  }
}

class UnitTestOrderDataMapper extends DataMapper
{
  public $calls = array();

  public function mapOne()
  {
    $this->calls[] = 'one';
    $this->notifyImporter( new RecordData('Poi') );
  }
  public function mapTwo()
  {
    $this->calls[] = 'two';
    $this->notifyImporter( new RecordData('Poi') );
  }
  public function mapThree()
  {
    $this->calls[] = 'three';
    $this->notifyImporter( new RecordData('Poi') );
  }
  public function mapFour()
  {
    $this->calls[] = 'four';
    $this->notifyImporter( new RecordData('Poi') );
  }
}
?>
