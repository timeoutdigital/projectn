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
        $this->setUpVendorCategoryUIMapping();
    }

    public function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    public function testsSaveRecord_TwoNodesWithSameIdButDifferentData()
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

    public function testSaveRecordThatValuesAreStoredCorrectly()
    {
        // Load POI XML
        $xmlExportPoi = simplexml_load_file( TO_TEST_DATA_PATH . '/model/exported_poi_sample.xml' );
        $xmlNode = $xmlExportPoi->entry[1];

        Doctrine::getTable( 'ExportedItem' )->saveRecord( $xmlNode, 'poi', 1 );

        $exportedItem = Doctrine::getTable( 'ExportedItem' )->find(1);
        $this->assertEquals( '75552' , $exportedItem['record_id'], "Value missmatch");
        $this->assertEquals( 'poi' , $exportedItem['model'], "Value missmatch");
        $this->assertEquals( '1' , $exportedItem['vendor_id'], "Value missmatch");
        $this->assertEquals( '2010-12-14 08:23:00' , $exportedItem['created_at'], "Value missmatch");

        $exportedItemHistory = Doctrine::getTable( 'ExportedItemHistory' )->find(1);
        $this->assertEquals( '1' , $exportedItemHistory['exported_item_id'], "Value missmatch");
        $this->assertEquals( 'ui_category_id' , $exportedItemHistory['field'], "Value missmatch");
        $this->assertEquals( '2' , $exportedItemHistory['value'], "Value missmatch");
        $this->assertEquals( '2010-12-14 08:23:00' , $exportedItemHistory['created_at'], "Value missmatch");
    }

    public function testsSaveRecord_TwoNodesWithSameIdSameData()
    {
        // Load POI XML
        $xmlExportPoi = simplexml_load_file( TO_TEST_DATA_PATH . '/model/exported_poi_sample2.xml' );
        $xmlNode = $xmlExportPoi->entry[0];
        Doctrine::getTable( 'ExportedItem' )->saveRecord( $xmlNode, 'poi', 1 );
        $this->assertEquals( 1 , Doctrine::getTable( 'ExportedItem' )->count(), "There should be 1 record added to Database");
        $this->assertEquals( 1 , Doctrine::getTable( 'ExportedItemHistory' )->count(), "Each record should have 1 minimum History");

        $xmlNode = $xmlExportPoi->entry[1];
        Doctrine::getTable( 'ExportedItem' )->saveRecord( $xmlNode, 'poi', 1 );
        $this->assertEquals( 1 , Doctrine::getTable( 'ExportedItem' )->count(), "Since this is repeating, there should only have 1 record");
        $this->assertEquals( 1 , Doctrine::getTable( 'ExportedItemHistory' )->count(), "UI category did not change, there should still be only 1 history record");

        $modifiedRecord = Doctrine::getTable( 'ExportedItemHistory' )->find(1);
        $this->assertEquals( '0' , $modifiedRecord['value'], "Last vaue should 0 as Nightout don't exists");
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

    public function testSaveRecordInvalidModelException()
    {
        // Load POI XML
        $xmlExportPoi = simplexml_load_file( TO_TEST_DATA_PATH . '/model/exported_poi_sample.xml' );

        $this->setExpectedException( 'ExportedItemTableException' );

        Doctrine::getTable( 'ExportedItem' )->saveRecord( $xmlExportPoi->entry[0], 'InvalidModel', 1 );
    }

    public function testSaveRecordInvalidIdException()
    {
        //see exported_event_sample2.xml for invalid input

        // Load POI XML
        $xmlExportEvent = simplexml_load_file( TO_TEST_DATA_PATH . '/model/exported_event_sample2.xml' );

        $this->setExpectedException( 'ExportedItemTableException' );

        Doctrine::getTable( 'ExportedItem' )->saveRecord( $xmlExportEvent->event[0], 'event', 1 );
    }
    
    public function testSaveRecordInvalidDateException1()
    {
        //see exported_event_sample2.xml for invalid input

        // Load POI XML
        $xmlExportEvent = simplexml_load_file( TO_TEST_DATA_PATH . '/model/exported_event_sample2.xml' );

        $this->setExpectedException( 'ExportedItemTableException' );

        Doctrine::getTable( 'ExportedItem' )->saveRecord( $xmlExportEvent->event[1], 'event', 1 );
    }

    public function testSaveRecordInvalidDateException2()
    {
        //see exported_event_sample2.xml for invalid input

        // Load POI XML
        $xmlExportEvent = simplexml_load_file( TO_TEST_DATA_PATH . '/model/exported_event_sample2.xml' );

        $this->setExpectedException( 'ExportedItemTableException' );

        Doctrine::getTable( 'ExportedItem' )->saveRecord( $xmlExportEvent->event[2], 'event', 1 );
    }

    public function testSaveRecordWithDifferentModelTypes()
    {
        // Load POI XML
        $xmlExportPoi = simplexml_load_file( TO_TEST_DATA_PATH . '/model/exported_event_sample.xml' );

        foreach( $xmlExportPoi->event as $xmlNode)
        {
            Doctrine::getTable( 'ExportedItem' )->saveRecord( $xmlNode, 'event', 1 );
        }

        // assert
        $this->assertEquals( 2 , Doctrine::getTable( 'ExportedItem' )->count(), "There should be 2 record added to Database");
        $this->assertEquals( 2 , Doctrine::getTable( 'ExportedItemHistory' )->count(), "here should be 2 history record added to Database");
    }
    
    
    public function testsSaveRecordGetHighestValueUICategoryID()
    {
        // Load POI XML
        $xmlExportPoi = simplexml_load_file( TO_TEST_DATA_PATH . '/model/exported_poi_sample.xml' );

        Doctrine::getTable( 'ExportedItem' )->saveRecord( $xmlExportPoi->entry[5], 'poi', 1 );
        
        // get the LAST one to make sure that GetHighestValueUICategoryID() selected Eating and Drinking UI category
        $record = Doctrine::getTable( 'ExportedItem' )->find( 1 );
        $this->assertEquals( 2, $record['ExportedItemHistory'][0]->value );
    }

    public function testsSaveRecord0ValueIfUICategoryNotFound()
    {
        // Load POI XML
        $xmlExportPoi = simplexml_load_file( TO_TEST_DATA_PATH . '/model/exported_poi_sample.xml' );

        Doctrine::getTable( 'ExportedItem' )->saveRecord( $xmlExportPoi->entry[4], 'poi', 1 );

        // try to find non existent ui category
        $record = Doctrine::getTable( 'ExportedItem' )->find( 1 );
        $this->assertEquals( 0, $record['ExportedItemHistory'][0]->value );
    }

    /* Test for ->getItemsFirstExportedIn() */
    public function testGetItemsFirstExportedInSpecificDay()
    {
        // Import 3 Days worth of Data to simulate History and Different Records on Different days
        $this->importXMLNodes( simplexml_load_file( TO_TEST_DATA_PATH . '/model/export_poi_10_12_2010.xml') ); // Import POI for Date 10/12/2010
        $this->importXMLNodes( simplexml_load_file( TO_TEST_DATA_PATH . '/model/export_poi_15_12_2010.xml') ); // Import POI for Date 15/12/2010
        $this->importXMLNodes( simplexml_load_file( TO_TEST_DATA_PATH . '/model/export_poi_20_12_2010.xml') ); // Import POI for Date 20/12/2010

        // makesure that we have all the data in exportedItem and History added when category only changed
        $this->assertEquals( 5, Doctrine::getTable( 'ExportedItem' )->count() );
        $this->assertEquals( 11, Doctrine::getTable( 'ExportedItemHistory' )->count() );

        // fetch first exported pois by Dates
        $results = Doctrine::getTable( 'ExportedItem' )->getItemsFirstExportedIn( '2010-12-10','2010-12-10', 1, 'poi' );
        $this->assertEquals( 3, count($results), 'Should only Return All 3, Because, the Poi is created this date but cat 0 is exceptional for that date');

        $this->assertEquals( '2', $results[0]['value'], 'Last category (in this date range) of this records is 2');
        $this->assertEquals( '0', $results[1]['value'], 'Last category (in this date range) of this records is 0');
        $this->assertEquals( '1', $results[2]['value'], 'Last category (in this date range) of this records is 1');

    }

    public function testGetItemsFirstExportedLastDayOfImport()
    {
        // Import 3 Days worth of Data to simulate History and Different Records on Different days
        $this->importXMLNodes( simplexml_load_file( TO_TEST_DATA_PATH . '/model/export_poi_10_12_2010.xml') ); // Import POI for Date 10/12/2010
        $this->importXMLNodes( simplexml_load_file( TO_TEST_DATA_PATH . '/model/export_poi_15_12_2010.xml') ); // Import POI for Date 15/12/2010
        $this->importXMLNodes( simplexml_load_file( TO_TEST_DATA_PATH . '/model/export_poi_20_12_2010.xml') ); // Import POI for Date 20/12/2010

        $results = Doctrine::getTable( 'ExportedItem' )->getItemsFirstExportedIn( '2010-12-20','2010-12-20', 1, 'poi' );
        $this->assertEquals( 1 , count($results), 'Only 1 New poi Inserted in this Date, other should only show on other dates or their date range' );
        $this->assertEquals( '0', $results[0]['value'], 'Last category (in this date range) of this records is 0');
    }

    public function testGetItemsFirstExportedDateRangeForInclude0Cat10To15DateRange()
    {
        // Import 3 Days worth of Data to simulate History and Different Records on Different days
        $this->importXMLNodes( simplexml_load_file( TO_TEST_DATA_PATH . '/model/export_poi_10_12_2010.xml') ); // Import POI for Date 10/12/2010
        $this->importXMLNodes( simplexml_load_file( TO_TEST_DATA_PATH . '/model/export_poi_15_12_2010.xml') ); // Import POI for Date 15/12/2010
        $this->importXMLNodes( simplexml_load_file( TO_TEST_DATA_PATH . '/model/export_poi_20_12_2010.xml') ); // Import POI for Date 20/12/2010

        $results = Doctrine::getTable( 'ExportedItem' )->getItemsFirstExportedIn( '2010-12-10','2010-12-15', 1, 'poi' );
        $this->assertEquals( 4, count($results), 'Should include Both days Pois');
    }

    public function testGetItemsFirstExportedDateRangeForInclude0Cat15To20DateRange()
    {
        // Import 3 Days worth of Data to simulate History and Different Records on Different days
        $this->importXMLNodes( simplexml_load_file( TO_TEST_DATA_PATH . '/model/export_poi_10_12_2010.xml') ); // Import POI for Date 10/12/2010
        $this->importXMLNodes( simplexml_load_file( TO_TEST_DATA_PATH . '/model/export_poi_15_12_2010.xml') ); // Import POI for Date 15/12/2010
        $this->importXMLNodes( simplexml_load_file( TO_TEST_DATA_PATH . '/model/export_poi_20_12_2010.xml') ); // Import POI for Date 20/12/2010

        $results = Doctrine::getTable( 'ExportedItem' )->getItemsFirstExportedIn( '2010-12-15','2010-12-20', 1, 'poi' );
        $this->assertEquals( 3, count($results), 'Should include Both days Pois');
    }

    

    /**
     * Since there is no invoiceable option, model should always return original category event though it's changed in this date range
     */
    public function testGetItemsFirstExportedInDateRange()
    {
        // Import 3 Days worth of Data to simulate History and Different Records on Different days
        $this->importXMLNodes( simplexml_load_file( TO_TEST_DATA_PATH . '/model/export_poi_10_12_2010.xml') ); // Import POI for Date 10/12/2010
        $this->importXMLNodes( simplexml_load_file( TO_TEST_DATA_PATH . '/model/export_poi_15_12_2010.xml') ); // Import POI for Date 15/12/2010
        $this->importXMLNodes( simplexml_load_file( TO_TEST_DATA_PATH . '/model/export_poi_20_12_2010.xml') ); // Import POI for Date 20/12/2010

        // makesure that we have all the data in exportedItem and History added when category only changed
        $this->assertEquals( 3, Doctrine::getTable( 'ExportedItem' )->count() );
        $this->assertEquals( 8, Doctrine::getTable( 'ExportedItemHistory' )->count() );

        // fetch first exported pois by Dates
        $results = Doctrine::getTable( 'ExportedItem' )->getItemsFirstExportedIn( '2010-12-10','2010-12-20', 1, 'poi' );
        $this->assertEquals( 3, count($results) );

        $this->assertEquals( '2', $results[0]['value'], 'First category of this records is 2');
        $this->assertEquals( '2', $results[1]['value'], 'First category of this records is 2');
        $this->assertEquals( '1', $results[2]['value'], 'First category of this records is 1');
    }
    
    private function importXMLNodes( $xmlNodes )
    {
        foreach( $xmlNodes->entry as $xmlNode)
        {
            Doctrine::getTable( 'ExportedItem' )->saveRecord( $xmlNode, 'poi', 1 );
        }
    }

    /**
     * This method will add Vendor Categories exactly same as UI category and Map them to UI category
     * This mimic the Producer mapping action
     */
    private function setUpVendorCategoryUIMapping()
    {
        $uiCategories = Doctrine::getTable( 'UiCategory' )->findAll();

        foreach( $uiCategories as $uiCat )
        {
            $poiCategory = new VendorPoiCategory;
            $poiCategory['name'] = $uiCat['name'];
            $poiCategory['vendor_id'] = 1;
            $poiCategory->save();

            $eventCategory = new VendorEventCategory;
            $eventCategory['name'] = $uiCat['name'];
            $eventCategory['vendor_id'] = 1;
            $eventCategory->save();

            $uiCat['VendorPoiCategory'][] = $poiCategory;
            $uiCat['VendorEventCategory'][] = $eventCategory;
            $uiCat->save();

        }
    }
}