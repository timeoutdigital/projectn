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

    public function testImportExportedItems()
    {
        $this->assertEquals( 0, Doctrine::getTable( 'ExportedItem' )->count() );
        $this->assertEquals( 0, Doctrine::getTable( 'ExportedItemHistory' )->count() );

        $export_date_path = TO_TEST_DATA_PATH . '/import_exported/export_20110105';

        $importExported = new importExportedItems( $export_date_path );
        $importExported->import();
        
    }
}