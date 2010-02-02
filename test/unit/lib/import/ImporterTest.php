<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../bootstrap.php';

/**
 * Test class for Importer.
 * Generated by PHPUnit on 2010-01-21 at 18:43:39.
 */
class ImporterTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var Importer
   */
  protected $object;
  
  /**
   * @var Vendor
   */
  protected $vendor;

  /**
   * @var Logger
   */
  protected $poiLogger;

  /**
   * @var Logger
   */
  protected $eventLogger;

  /**
   * @var Logger
   */
  protected $movieLogger;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    $this->vendor = ProjectN_Test_Unit_Factory::get('vendor');
    $this->poiLogger = new logger( $this->vendor, logger::POI );
    $this->eventLogger = new logger( $this->vendor, logger::EVENT );
    $this->movieLogger = new logger( $this->vendor, logger::MOVIE );
    $this->object  = new Importer();
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
   * Test loggers get registered
   */
  public function testRegisteringLogger()
  {
    $returnedLoggers = $this->object->getLoggers();
    $this->assertEquals( array(), $returnedLoggers );
    
    $this->object->registerLogger( $this->poiLogger );
    
    $returnedLoggers = $this->object->getLoggers();
    $this->assertType('array', $returnedLoggers);

    $this->assertEquals( 1, count( $returnedLoggers['poi'] ) );
    $this->assertTrue( $returnedLoggers['poi'][0] instanceof logger );
    $this->assertEquals( logger::POI, $returnedLoggers['poi'][0]->getType() );
    
    $this->object->registerLogger( $this->poiLogger );
    $returnedLogger = $this->object->getLoggers();
    $this->assertEquals( 1, count( $returnedLogger['poi'] ) );

    $this->object->registerLogger( $this->eventLogger );
    $returnedLoggers = $this->object->getLoggers();
    $this->assertEquals( 1, count( $returnedLoggers['event'] ) );
    $this->assertEquals( logger::EVENT, $returnedLoggers['event'][0]->getType() );
    $this->assertEquals( 1, count( $returnedLoggers['poi'] ) );
  }

  /**
   * Test DataMapper gets added
   */
  public function testAddDataMapper()
  {
    $dataSource = new UnitTestImporterDataMapper( $this->importer );

    $returnedImportData = $this->object->getDataMappers();
    $this->assertEquals(array(), $returnedImportData);

    $this->object->addDataMapper( $dataSource );

    $returnedImportData = $this->object->getDataMappers();
    $returnedImportData = $returnedImportData[0];

    $this->assertEquals($dataSource, $returnedImportData);
  }

  /**
   * test DataMappers as run
   */
  public function testDataMappersAreRun()
  {
    
    $importer = $this->getMock('Importer', array('onRecordMapped'));
    
    $importer->addDataMapper( new UnitTestImporterDataMapper( $importer ) );
    $importer->addDataMapper( new UnitTestImporterDataMapper( $importer ) );
    
    $importer->expects( $this->exactly( 2 ) )
             ->method( 'onRecordMapped' );
    
    $importer->run();
  }


  /**
   * test mapped data is saved
   */
  public function testMappedDataIsSaved()
  {

    $importer = new Importer();

    $importer->addDataMapper( new UnitTestImporterDataMapper( $importer ) );
    $importer->addDataMapper( new UnitTestImporterDataMapper( $importer ) );

    $importer->run();
    
    $poiTable = Doctrine::getTable( 'Poi' );
    $this->assertEquals( 2, $poiTable->count() );

    $poiCategoryTable = Doctrine::getTable( 'PoiCategory' );
    $this->assertEquals( 1, $poiCategoryTable->count() );

    $poiCategory2 = ProjectN_Test_Unit_Factory::get('PoiCategory');
    $poiCategory2->save();
    
    $poi = $poiTable->findOneById( 1 );
    
    $poiCategory = $poiCategoryTable->findOneById( 1 );
    $poiCatFromDb = $poiCategory->getId();
    $poiCatFromObject = $poi->getPoiCategories()->getFirst()->getId();
    $this->assertEquals( $poiCatFromDb, $poiCatFromObject );

    $this->assertEquals( 1, $poi->getPoiCategories()->count() );
    $this->assertNotEquals( $poiCategory2['id'], $poi->getPoiCategories()->getFirst()->getId() );
  }
}

class UnitTestImporterDataMapper extends DataMapper
{
  public function mapPois()
  {
    $poi = ProjectN_Test_Unit_Factory::get('Poi');
    $poi->save();
    $this->notifyImporter( $poi );
  }
}
?>
