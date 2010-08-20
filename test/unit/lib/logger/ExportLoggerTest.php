<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../bootstrap.php';

/**
 * Test class for ImportLogger.
 */
class ExportLoggerTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Vendor
     */
    protected $vendor;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        ProjectN_Test_Unit_Factory::createDatabases();
        $this->vendor = ProjectN_Test_Unit_Factory::get( 'Vendor' );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    /**
     * Remove duplicate metric for the same day.
     * Refs: #606
     */
    public function testNoDuplicateMetricPerDay()
    {
        ExportLogger::getInstance()->setVendor( $this->vendor )->start();
        ExportLogger::getInstance()->addExport( 'Event', 1 );
        ExportLogger::getInstance()->addError( 'Failed to save datestamp for export', 'Event', 1 );
        ExportLogger::getInstance()->end();
        ExportLogger::getInstance()->unsetSingleton();

        ExportLogger::getInstance()->setVendor( $this->vendor )->start();
        ExportLogger::getInstance()->addExport( 'Event', 1 );
        ExportLogger::getInstance()->addError( 'Failed to save datestamp for export', 'Event', 1 );
        ExportLogger::getInstance()->end();

        // Query DB
        $exportDatestampRows = Doctrine::getTable( 'LogExportDate' )->findAll();
        $exportCountRows = Doctrine::getTable( 'LogExportCount' )->findAll();
        $exportErrorRows = Doctrine::getTable( 'LogExportError' )->findAll();
        $logExportRows = Doctrine::getTable( 'LogExport' )->findAll();

        // Should only have one row in each table for today.
        $this->assertEquals( 1, $exportDatestampRows->count() );
        $this->assertEquals( 1, $exportCountRows->count() );
        $this->assertEquals( 1, $exportErrorRows->count() );

        // We should still have 2 total LogExport rows.
        $this->assertEquals( 2, $logExportRows->count() );
    }

    public function testAddDatestamp()
    {
        $startTime = time();
        
        ExportLogger::getInstance()->setVendor( $this->vendor )->start();
        ExportLogger::getInstance()->addExport( 'Event', 1 );
        ExportLogger::getInstance()->addExport( 'Event', 1 ); // Dupes allowed
        ExportLogger::getInstance()->addExport( 'Event', 2 );
        ExportLogger::getInstance()->addExport( 'Poi', 1 );
        ExportLogger::getInstance()->addExport( 'Movie', 1 );
        ExportLogger::getInstance()->end();

        $endTime = time();

        $exportDatestampRows = Doctrine::getTable( 'LogExportDate' )->findAll();
        $this->assertEquals( 5, $exportDatestampRows->count() );

        $this->assertEquals( 1, $exportDatestampRows[ 0 ][ 'record_id' ] );
        $this->assertEquals( 'Event', $exportDatestampRows[ 0 ][ 'model' ] );
        $this->assertGreaterThanOrEqual( $startTime, strtotime( $exportDatestampRows[ 0 ][ 'export_date' ] ) );
        $this->assertLessThanOrEqual   ( $endTime,   strtotime( $exportDatestampRows[ 0 ][ 'export_date' ] ) );

        $this->assertEquals( 1, $exportDatestampRows[ 1 ][ 'record_id' ] );
        $this->assertEquals( 'Event', $exportDatestampRows[ 1 ][ 'model' ] );
        $this->assertGreaterThanOrEqual( $startTime, strtotime( $exportDatestampRows[ 1 ][ 'export_date' ] ) );
        $this->assertLessThanOrEqual   ( $endTime,   strtotime( $exportDatestampRows[ 1 ][ 'export_date' ] ) );

        $this->assertEquals( 2, $exportDatestampRows[ 2 ][ 'record_id' ] );
        $this->assertEquals( 'Event', $exportDatestampRows[ 2 ][ 'model' ] );
        $this->assertGreaterThanOrEqual( $startTime, strtotime( $exportDatestampRows[ 2 ][ 'export_date' ] ) );
        $this->assertLessThanOrEqual   ( $endTime,   strtotime( $exportDatestampRows[ 2 ][ 'export_date' ] ) );

        $this->assertEquals( 1, $exportDatestampRows[ 3 ][ 'record_id' ] );
        $this->assertEquals( 'Poi', $exportDatestampRows[ 3 ][ 'model' ] );
        $this->assertGreaterThanOrEqual( $startTime, strtotime( $exportDatestampRows[ 3 ][ 'export_date' ] ) );
        $this->assertLessThanOrEqual   ( $endTime,   strtotime( $exportDatestampRows[ 3 ][ 'export_date' ] ) );

        $this->assertEquals( 1, $exportDatestampRows[ 4 ][ 'record_id' ] );
        $this->assertEquals( 'Movie', $exportDatestampRows[ 4 ][ 'model' ] );
        $this->assertGreaterThanOrEqual( $startTime, strtotime( $exportDatestampRows[ 4 ][ 'export_date' ] ) );
        $this->assertLessThanOrEqual   (  $endTime,  strtotime( $exportDatestampRows[ 4 ][ 'export_date' ] ) );
    }

    public function testInitExport()
    {
        ExportLogger::getInstance()->setVendor( $this->vendor )->start();
        ExportLogger::getInstance()->initExport( 'Poi' );
        ExportLogger::getInstance()->addExport( 'Event', 1 );
        ExportLogger::getInstance()->end();

        $exportLogRows = Doctrine::getTable( 'LogExport' )->findAll();

        $this->assertEquals( $exportLogRows[ 0 ][ 'LogExportCount' ][ 0 ][ 'model' ], 'Poi' );
        $this->assertEquals( $exportLogRows[ 0 ][ 'LogExportCount' ][ 0 ][ 'count' ], 0 );
        $this->assertEquals( $exportLogRows[ 0 ][ 'LogExportCount' ][ 1 ][ 'model' ], 'Event' );
        $this->assertEquals( $exportLogRows[ 0 ][ 'LogExportCount' ][ 1 ][ 'count' ], 1 );

        ProjectN_Test_Unit_Factory::destroyDatabases();
        ProjectN_Test_Unit_Factory::createDatabases();

        ExportLogger::getInstance()->setVendor( $this->vendor )->start();
        ExportLogger::getInstance()->initExport( 'Poi' );
        ExportLogger::getInstance()->initExport( 'Event' ); // Added this line, just to be sure.
        ExportLogger::getInstance()->addExport( 'Event', 1 );
        ExportLogger::getInstance()->end();

        $exportLogRows = Doctrine::getTable( 'LogExport' )->findAll();

        $this->assertEquals( $exportLogRows[ 0 ][ 'LogExportCount' ][ 0 ][ 'model' ], 'Poi' );
        $this->assertEquals( $exportLogRows[ 0 ][ 'LogExportCount' ][ 0 ][ 'count' ], 0 );
        $this->assertEquals( $exportLogRows[ 0 ][ 'LogExportCount' ][ 1 ][ 'model' ], 'Event' );
        $this->assertEquals( $exportLogRows[ 0 ][ 'LogExportCount' ][ 1 ][ 'count' ], 1 ); 
    }

    public function testAddExport()
    {
        ExportLogger::getInstance()->setVendor( $this->vendor )->start();
        
        for ( $i=0; $i < 15; $i++ )
        {
            ExportLogger::getInstance()->addExport( 'Poi', 1 );
        }
        
        for ( $i=0; $i < 10; $i++ )
        {
            ExportLogger::getInstance()->addExport( 'Event', 1 );
        }

        $exportLogRows = Doctrine::getTable( 'LogExport' )->findAll();

        $this->assertEquals( 1, $exportLogRows->count() );

        $this->assertEquals( $exportLogRows[ 0 ][ 'LogExportCount' ][ 0 ][ 'model' ], 'Poi' );
        $this->assertEquals( $exportLogRows[ 0 ][ 'LogExportCount' ][ 0 ][ 'count' ], 15 );
        $this->assertEquals( $exportLogRows[ 0 ][ 'LogExportCount' ][ 1 ][ 'model' ], 'Event' );
        $this->assertEquals( $exportLogRows[ 0 ][ 'LogExportCount' ][ 1 ][ 'count' ], 10 );

        $this->assertEquals( 'running', $exportLogRows[ 0 ][ 'status' ] );
        ExportLogger::getInstance()->end();
        $this->assertEquals( 'success', $exportLogRows[ 0 ][ 'status' ] );
    }

    public function testAddError()
    {
        ExportLogger::getInstance()->setVendor( $this->vendor )->start();

        for ( $i=0; $i < 15; $i++ )
        {
            ExportLogger::getInstance()->addError( 'test message', 'Poi', '1' );
        }

        for ( $i=0; $i < 10; $i++ )
        {
            ExportLogger::getInstance()->addError( 'test message', 'Event', '1' );
        }

        ExportLogger::getInstance()->addError( 'test message' );

        $exportLogRows = Doctrine::getTable( 'LogExport' )->findAll();

        $this->assertEquals( 1, $exportLogRows->count() );

        $this->assertEquals( 26, $exportLogRows[ 0 ][ 'LogExportError' ]->count() );

        $this->assertEquals( $exportLogRows[ 0 ][ 'LogExportError' ][ 0 ][ 'record_id' ], 1 );
        $this->assertEquals( $exportLogRows[ 0 ][ 'LogExportError' ][ 0 ][ 'model' ], 'Poi' );
        $this->assertEquals( $exportLogRows[ 0 ][ 'LogExportError' ][ 0 ][ 'log' ], 'test message' );

        $this->assertEquals( $exportLogRows[ 0 ][ 'LogExportError' ][ 15 ][ 'record_id' ], 1 );
        $this->assertEquals( $exportLogRows[ 0 ][ 'LogExportError' ][ 15 ][ 'model' ], 'Event' );
        $this->assertEquals( $exportLogRows[ 0 ][ 'LogExportError' ][ 15 ][ 'log' ], 'test message' );

        $this->assertNull( $exportLogRows[ 0 ][ 'LogExportError' ][ 25 ][ 'record_id' ] );
        $this->assertNull(  $exportLogRows[ 0 ][ 'LogExportError' ][ 25 ][ 'model' ] );
        $this->assertEquals( $exportLogRows[ 0 ][ 'LogExportError' ][ 25 ][ 'log' ], 'test message' );

        $this->assertEquals( 'running', $exportLogRows[ 0 ][ 'status' ] );
        ExportLogger::getInstance()->end();
        $this->assertEquals( 'success', $exportLogRows[ 0 ][ 'status' ] );
    }

    public function testGetTotal()
    {
        ExportLogger::getInstance()->setVendor( $this->vendor )->start();

        for ( $i=0; $i < 15; $i++ )
        {
            ExportLogger::getInstance()->addExport( 'Poi', 1 );
        }

        for ( $i=0; $i < 10; $i++ )
        {
            ExportLogger::getInstance()->addExport( 'Event', 1 );
        }
        
        ExportLogger::getInstance()->end();

        $this->assertEquals( 25,  ExportLogger::getInstance()->getTotal() );
        $this->assertEquals( 15,  ExportLogger::getInstance()->getTotal( 'Poi' ) );
        $this->assertEquals( 10,  ExportLogger::getInstance()->getTotal( 'Event' ) );
    }

    public function testGetTotalError()
    {
        ExportLogger::getInstance()->setVendor( $this->vendor )->start();

        for ( $i=0; $i < 15; $i++ )
        {
            ExportLogger::getInstance()->addError( 'test message', 'Poi', '1' );
        }

        for ( $i=0; $i < 10; $i++ )
        {
            ExportLogger::getInstance()->addError( 'test message', 'Event', '1' );
        }

        ExportLogger::getInstance()->end();

        $this->assertEquals( 25,  ExportLogger::getInstance()->getTotalError() );
        $this->assertEquals( 15,  ExportLogger::getInstance()->getTotalError( 'Poi' ) );
        $this->assertEquals( 10,  ExportLogger::getInstance()->getTotalError( 'Event' ) );
    }

}
?>
