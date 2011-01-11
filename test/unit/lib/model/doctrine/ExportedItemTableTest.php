<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';


/**
 * ExportedItemTable test
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

class ExportedItemTableTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');
    }

    public function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    public function testsSaveRecordImportFirst2Only()
    {
        // Load POI XML
        $xmlExportPoi = simplexml_load_file( TO_TEST_DATA_PATH . '/model/exported_poi_sample.xml' );
        $xmlNode = $xmlExportPoi->entry[0];
        Doctrine::getTable( 'ExportedItem' )->saveRecord( $xmlNode, 'poi', 1 );
        $this->assertEquals( 1 , Doctrine::getTable( 'ExportedItem' )->count(), "There should be 1 record added to Database");
        $this->assertEquals( 1 , Doctrine::getTable( 'ExportedItemHistory' )->count(), "Each record should have 1 minimum History");

        $xmlNode = $xmlExportPoi->entry[1];
        Doctrine::getTable( 'ExportedItem' )->saveRecord( $xmlNode, 'poi', 1 );
        $this->assertEquals( 1 , Doctrine::getTable( 'ExportedItem' )->count(), "Since this is repeating, there should only have 1 record");
        $this->assertEquals( 2 , Doctrine::getTable( 'ExportedItemHistory' )->count(), "UI category has been changed, There should be 2 Record in History");
        
        $modifiedRecord = Doctrine::getTable( 'ExportedItemHistory' )->find(1);
        $this->assertEquals( '0' , $modifiedRecord['value'], "Last vaue should 0 as Nightout don't exists");
        $modifiedRecord = Doctrine::getTable( 'ExportedItemHistory' )->find(2);
        $this->assertEquals( '2' , $modifiedRecord['value'], "Last vaue should 2 for Eating & Drinking");
    }

    public function testsSaveRecordImportAll()
    {
        // Load POI XML
        $xmlExportPoi = simplexml_load_file( TO_TEST_DATA_PATH . '/model/exported_poi_sample.xml' );
        
        foreach( $xmlExportPoi->entry as $xmlNode)
        {
            Doctrine::getTable( 'ExportedItem' )->saveRecord( $xmlNode, 'poi', 1 );
        }

        // assert
        $this->assertEquals( 5 , Doctrine::getTable( 'ExportedItem' )->count(), "There should be 4 record added to Database");
        $this->assertEquals( 6 , Doctrine::getTable( 'ExportedItemHistory' )->count(), "UI category changed 1 for 5 records, Hence there should be 6 history");
    }
    
    
    public function testsSaveRecordGetHighestValueUICategoryID()
    {
        // Load POI XML
        $xmlExportPoi = simplexml_load_file( TO_TEST_DATA_PATH . '/model/exported_poi_sample.xml' );

        foreach( $xmlExportPoi->entry as $xmlNode)
        {
            Doctrine::getTable( 'ExportedItem' )->saveRecord( $xmlNode, 'poi', 1 );
        }

        // get the LAST one to make sure that GetHighestValueUICategoryID() selected Eating and Drinking UI category
        $record = Doctrine::getTable( 'ExportedItem' )->find( 5 );
        $this->assertEquals( 2, $record['ExportedItemHistory'][0]->value );
    }

    public function testFetchByWithoutInvoice()
    {
        $this->importXMLNodes( simplexml_load_file( TO_TEST_DATA_PATH . '/model/export_poi_10_12_2010.xml') ); // Import POI for Date 10/12/2010
        $this->importXMLNodes( simplexml_load_file( TO_TEST_DATA_PATH . '/model/export_poi_15_12_2010.xml') ); // Import POI for Date 15/12/2010
        $this->importXMLNodes( simplexml_load_file( TO_TEST_DATA_PATH . '/model/export_poi_20_12_2010.xml') ); // Import POI for Date 20/12/2010

        $this->assertEquals( 3, Doctrine::getTable( 'ExportedItem' )->count() );
        $this->assertEquals( 8, Doctrine::getTable( 'ExportedItemHistory' )->count() );

        // Get all Data Without filtering Invoiceable and or by ui category ID
        $data = Doctrine::getTable( 'ExportedItem' )->fetchBy( '2010-12-10', '2010-12-20', 1, 'poi', array(), null, false );
        $this->assertEquals( 3, $data->count() );
        $this->assertEquals( 2, $data[0]['ExportedItemHistory']->count() );
        $this->assertEquals( 3, $data[1]['ExportedItemHistory']->count() );
        $this->assertEquals( 3, $data[2]['ExportedItemHistory']->count() );

        // Filter by Ui category ID within DateRange
        $data = Doctrine::getTable( 'ExportedItem' )->fetchBy( '2010-12-10', '2010-12-20', 1, 'poi', array(), 3, false );
        $this->assertEquals( 1, $data->count() );
        $this->assertEquals( 1, $data[0]['ExportedItemHistory']->count() );
    }

    public function testFetchByWithInvoice()
    {
        $this->importXMLNodes( simplexml_load_file( TO_TEST_DATA_PATH . '/model/export_poi_10_12_2010.xml') ); // Import POI for Date 10/12/2010
        $this->importXMLNodes( simplexml_load_file( TO_TEST_DATA_PATH . '/model/export_poi_15_12_2010.xml') ); // Import POI for Date 15/12/2010
        $this->importXMLNodes( simplexml_load_file( TO_TEST_DATA_PATH . '/model/export_poi_20_12_2010.xml') ); // Import POI for Date 20/12/2010

        $this->assertEquals( 3, Doctrine::getTable( 'ExportedItem' )->count() );
        $this->assertEquals( 8, Doctrine::getTable( 'ExportedItemHistory' )->count() );

        // Filter by invoiceable, but no Category
        $data = Doctrine::getTable( 'ExportedItem' )->fetchBy( '2010-12-10', '2010-12-20', 1, 'poi', array( '2'), null, true );
        $this->assertEquals( 3, $data->count() );
        $this->assertEquals( 1, $data[0]['ExportedItemHistory']->count() );
        $this->assertEquals( 2, $data[1]['ExportedItemHistory']->count(), 'This is because this category was invoiceable, changed to something else in the middle before changing back to invoiceable again');
        $this->assertEquals( 1, $data[2]['ExportedItemHistory']->count() );

        $data = Doctrine::getTable( 'ExportedItem' )->fetchBy( '2010-12-15', '2010-12-20', 1, 'poi', array( '2' ), null, true );
        $this->assertEquals( 1, $data->count(), 'There should only be 1 Invoiceable Item');

        // Filter by UI category ID and Invoiceable?
        $data = Doctrine::getTable( 'ExportedItem' )->fetchBy( '2010-12-15', '2010-12-20', 1, 'poi', array( '2' ), 3, true ); // Not invoiceable CAT ID
        $this->assertNull( $data );

        $data = Doctrine::getTable( 'ExportedItem' )->fetchBy( '2010-12-10', '2010-12-20', 1, 'poi', array( '2' ), 2, true );
        $this->assertEquals( 3, $data->count() );
        $this->assertEquals( 1, $data[0]['ExportedItemHistory']->count() );
        $this->assertEquals( 1, $data[1]['ExportedItemHistory']->count() );
        $this->assertEquals( 1, $data[2]['ExportedItemHistory']->count() );
    }

    private function importXMLNodes( $xmlNodes )
    {
        foreach( $xmlNodes->entry as $xmlNode)
        {
            Doctrine::getTable( 'ExportedItem' )->saveRecord( $xmlNode, 'poi', 1 );
        }
    }
}