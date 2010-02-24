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
    //$this->poiLogger = new logger( $this->vendor, logger::POI );
    //$this->eventLogger = new logger( $this->vendor, logger::EVENT );
    //$this->movieLogger = new logger( $this->vendor, logger::MOVIE );
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
   * Test loggers are called correctly for adding records
   */
  public function testLogsRecordAddCorrectly()
  {
    $loggerTable = Doctrine::getTable( 'ImportLogger' );
    $this->assertEquals( 0, $loggerTable->count() );

    $logger = new logImport( $this->vendor );
    $logger->setType( 'poi' );
    
    $this->object->addLogger( $logger );
    $this->object->addDataMapper( new UnitTestImporterDataMapper1() );
    $this->object->run();

    $this->assertEquals( 1, $loggerTable->count() );

    $loggerRow = $loggerTable->findOneById( 1 );
    
    $this->assertEquals( 1, $loggerRow[ 'total_inserts' ], 'Total inserts' );
    $this->assertEquals( 0, $loggerRow[ 'total_updates' ], 'Total updates');
  }



  /**
   * Test loggers are called correctly for updating records
   */
  public function testLogsRecordUpdateCorrectly()
  {
    $loggerTable = Doctrine::getTable( 'ImportLogger' );
    $this->assertEquals( 0, $loggerTable->count() );

    $logger = new logImport( $this->vendor );
    $logger->setType( 'poi' );

    $this->object->addLogger( $logger );
    $this->object->addDataMapper( new UnitTestImporterDataMapper() );
    $this->object->run();

    $this->assertEquals( 1, $loggerTable->count() );

    $loggerRow = $loggerTable->findOneById( 1 );

    $this->assertEquals( 0, $loggerRow[ 'total_inserts' ], 'Total inserts' );
    $this->assertEquals( 1, $loggerRow[ 'total_updates' ], 'Total updates');
  }

  /**
   * Test DataMapper gets added
   */
  public function testAddDataMapper()
  {
    $dataSource = new UnitTestImporterDataMapper( );

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

  /**
   * If a record exists in a database and the data has changed, it should be
   * updated, not saved as new
   */
  public function testUpdatesIfRecordExists()
  {
    $importer = new Importer();
    $importer->addDataMapper( new UnitTestImporterDataMapper1( $importer ) );
    $importer->run();

    $importer2 = new Importer();
    $importer2->addDataMapper( new UnitTestImporterDataMapper2( $importer2 ) );
    $importer2->run();

    $record = Doctrine::getTable('Poi')->findOneById( 1 );
    $this->assertEquals( 1, Doctrine::getTable('Poi')->count() );
    $this->assertEquals('bar', $record['street']);
  }
}

class UnitTestImporterDataMapper extends DataMapper
{
  public function mapPois()
  {
    $poi = ProjectN_Test_Unit_Factory::add('Poi');
    $this->notifyImporter( $poi );
  }
}

//maps a new record
class UnitTestImporterDataMapper1 extends DataMapper
{
  public function mapPois()
  {
    $poi = ProjectN_Test_Unit_Factory::get('Poi', array( 'vendor_poi_id' => '99', 'street' => 'foo' ) );

    $poiProperty = new PoiProperty();
    $poiProperty['lookup'] = 'lookup';
    $poiProperty['value']  = 'value';

    $poi['PoiProperty'][] = $poiProperty;
    $this->notifyImporter( $poi );
  }
}

//maps an existing record
class UnitTestImporterDataMapper2 extends DataMapper
{
  /**
   * @var projectNDataMapperHelper
   */
  protected $dataMapperHelper;

  public function __construct()
  {
    $this->dataMapperHelper = new projectNDataMapperHelper( Doctrine::getTable('Vendor')->findOneById( 1 ) );
  }

  public function mapPois()
  {
    $poi = $this->dataMapperHelper->getPoiRecord( 99 );

    $poi['street'] = 'bar';

    $poiProperty = new PoiProperty();
    $poiProperty['lookup'] = 'lookup2';
    $poiProperty['value']  = 'value2';

    $poi['PoiProperty'][] = $poiProperty;
    $this->notifyImporter( $poi );
  }
}
?>
