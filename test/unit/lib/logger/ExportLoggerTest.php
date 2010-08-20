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

    public function testInitExport()
    {
        ExportLogger::getInstance()->setVendor( $this->vendor )->start();
        ExportLogger::getInstance()->initExport( 'Poi' );
        ExportLogger::getInstance()->addExport( 'Event' );
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
        ExportLogger::getInstance()->addExport( 'Event' );
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
            ExportLogger::getInstance()->addExport( 'Poi' );
        }
        
        for ( $i=0; $i < 10; $i++ )
        {
            ExportLogger::getInstance()->addExport( 'Event' );
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
            ExportLogger::getInstance()->addExport( 'Poi' );
        }

        for ( $i=0; $i < 10; $i++ )
        {
            ExportLogger::getInstance()->addExport( 'Event' );
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
