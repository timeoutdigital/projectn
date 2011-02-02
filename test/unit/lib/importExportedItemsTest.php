<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../bootstrap.php';
/**
 * Description
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Rajeevan kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class importExportedItemsTest extends PHPUnit_Framework_TestCase
{
    /**
    * Sets up the fixture, for example, opens a network connection.
    * This method is called before a test is executed.
    */
    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');
    }

    /**
    * Tears down the fixture, for example, closes a network connection.
    * This method is called after a test is executed.
    */
    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    public function testImportExportedItemsFirstDay()
    {
        $this->assertEquals( 0, Doctrine::getTable( 'ExportedItem' )->count() );
        $this->assertEquals( 0, Doctrine::getTable( 'ExportedItemHistory' )->count() );

        $export_date_path = TO_TEST_DATA_PATH . '/import_exported/export_20110105';

        $importExported = new importExportedItems( $export_date_path );
        $importExported->import();

        // Check import count to ensure that all imported
        $this->assertEquals( 18, Doctrine::getTable( 'ExportedItem' )->count() );
        $this->assertEquals( 18, Doctrine::getTable( 'ExportedItemHistory' )->count() );
    }

    public function testImportExportedItemsSecondDay()
    {
        $this->assertEquals( 0, Doctrine::getTable( 'ExportedItem' )->count() );
        $this->assertEquals( 0, Doctrine::getTable( 'ExportedItemHistory' )->count() );

        $export_date_path = TO_TEST_DATA_PATH . '/import_exported/export_20110106';

        $importExported = new importExportedItems( $export_date_path );
        $importExported->import();

        // Check import count to ensure that all imported
        $this->assertEquals( 25, Doctrine::getTable( 'ExportedItem' )->count() );
        $this->assertEquals( 25, Doctrine::getTable( 'ExportedItemHistory' )->count() );
    }

    public function testImportExportedItemsHistory()
    {
        $this->assertEquals( 0, Doctrine::getTable( 'ExportedItem' )->count() );
        $this->assertEquals( 0, Doctrine::getTable( 'ExportedItemHistory' )->count() );

        $export_date_path_1 = TO_TEST_DATA_PATH . '/import_exported/export_20110105';
        $export_date_path_2 = TO_TEST_DATA_PATH . '/import_exported/export_20110106';

        // Import Both days to simulate history
        $importExported = new importExportedItems( $export_date_path_1 );
        $importExported->import();

        $importExported = new importExportedItems( $export_date_path_2 );
        $importExported->import();

        // Check for imported differences
        $this->assertEquals( 27, Doctrine::getTable( 'ExportedItem' )->count(), 'Should be 27 as There is only 9 New Items are added in second day. original 18 + 9 = 27');
        $this->assertEquals( 30, Doctrine::getTable( 'ExportedItemHistory' )->count(), 'Should be 30; original 18 + 9 New items + 3 Changing Category records = 30');
    }
}