<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * ExportedItem Test
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

class ExportedItemTest extends PHPUnit_Framework_TestCase
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

    public function testIsInvoiceableBasicTest()
    {
        // Import Sample Data
        $xmlExportPoi = simplexml_load_file( TO_TEST_DATA_PATH . '/model/exported_poi_sample.xml' );
        $this->importXMLNodes( $xmlExportPoi, strtotime( '2010-12-14T08:23:00') );
        
        $this->assertEquals( 5, Doctrine::getTable( 'ExportedItem' )->count( ) );
        //there are six nodes, but two IDs are same, hence five resulting records

        // Test Invoiceable
        $exportedItem = Doctrine::getTable( 'ExportedItem' )->find(1);
        $this->assertFalse( $exportedItem->isInvoiceable( null, null ) );
        $this->assertTrue( $exportedItem->isInvoiceable( '2010-12-14', '2010-12-14' ) );

        // This category is not in Category
        $exportedItem = Doctrine::getTable( 'ExportedItem' )->find(4);
        $this->assertEquals( 3, $exportedItem['ExportedItemHistory'][0]['value'], 'This Record UI category ID should be 3 ( Around Town )');
        $this->assertFalse( $exportedItem->isInvoiceable( '2010-12-14', '2010-12-14' ) );
    }

    /**
     * Simple test to check isInvoiceable working with DateRanges...
     */
    public function testIsInvoiceableDateRange()
    {
        $xml = $this->generateXMLNodes( array( 1=> 'Around Town', 2 => 'Eating & Drinking', 3 => 'Art' ) );
        $this->assertEquals( 3, count( $xml ) );

        // Import these XML nodes into DB
        $this->importXMLNodes( $xml );
        $this->assertEquals( 3, Doctrine::getTable( 'ExportedItem' )->count() );

        // Update the Dates manually to simulate time period
        $this->updateRecordFields( 'ExportedItemHistory', 1, array( 'created_at' => '2010-10-15 12:00:00' ) );
        $this->updateRecordFields( 'ExportedItemHistory', 2, array( 'created_at' => '2010-10-15 12:00:00' ) );

        // Run import again
        $this->importXMLNodes( $xml );
        $exporedItem = Doctrine::getTable( 'ExportedItemHistory' )->find(1);
        $this->assertEquals( $exporedItem['created_at'], '2010-10-15 12:00:00');

        /*
         * now, We have 3 records
         * 1 = Around Town; not invoiceable as of 2010-10-15
         * 2 = Eating & Drinking; invoiceable as of 2010-10-15
         * 3 = Art; not invoiceable  as of 2010-10-15
         */
        // Run import again and change the 1 category to Invoiceable Eating and Drinking and 2 to not invoiceable Music
        $this->importXMLNodes( $this->generateXMLNodes( array( 2=> 'Music', 1 => 'Eating & Drinking' ) ) );
        $exporedItem = Doctrine::getTable( 'ExportedItem' )->find(1);
        $this->assertEquals(2, $exporedItem->getUICategoryID());
        $this->assertEquals(2, $exporedItem['ExportedItemHistory']->count() );

        $exporedItem = Doctrine::getTable( 'ExportedItem' )->find(2);
        $this->assertEquals(4, $exporedItem->getUICategoryID());
        $this->assertEquals(2, $exporedItem['ExportedItemHistory']->count() );

        $exporedItem = Doctrine::getTable( 'ExportedItem' )->find(3);
        $this->assertEquals(7, $exporedItem->getUICategoryID());
        $this->assertEquals(1, $exporedItem['ExportedItemHistory']->count() );


        /*
         * Now;
         * 1 is invoiceable as of TODAY
         * 2 was invoiceable on 15/10/2010 not Today
         */
        $exporedItem = Doctrine::getTable( 'ExportedItem' )->find(1);
        $this->assertTrue( $exporedItem->isInvoiceable( date( 'Y-m-d' ), date( 'Y-m-d' ) ) );
        $this->assertFalse( $exporedItem->isInvoiceable( '2010-10-15', '2010-10-15' ) );

        $exporedItem = Doctrine::getTable( 'ExportedItem' )->find(2);
        $this->assertFalse( $exporedItem->isInvoiceable( date( 'Y-m-d' ), date( 'Y-m-d' ) ) );
        $this->assertTrue( $exporedItem->isInvoiceable( '2010-10-15', '2010-10-15' ) );

    }

    /**
     * Advance phase test:
     * when the curent ui_category is not invoiceable but there is one or more invoiceable category withing the DateRange
     * It is Invoiceable in that Date Range, Given that there is no past invoiceable category found for the record.
     */
    public function testIsInvoiceableValidWithinDateRange()
    {
        // insert data and update
        $this->importXMLNodes( $this->generateXMLNodes( array( 1=> 'Around Town', 2 => 'Eating & Drinking', 3 => 'Art' ) ) );
        $this->updateRecordFields( 'ExportedItemHistory', 1, array( 'created_at' => '2010-10-15 12:00:01' ) );
        $this->updateRecordFields( 'ExportedItemHistory', 2, array( 'created_at' => '2010-10-15 12:00:01' ) );
        $this->updateRecordFields( 'ExportedItemHistory', 3, array( 'created_at' => '2010-10-15 12:00:01' ) );
        $this->assertEquals( 3, Doctrine::getTable( 'ExportedItem' )->count() );
        $this->assertEquals( 3, Doctrine::getTable( 'ExportedItemHistory' )->count() );

        // Import again
        $this->importXMLNodes( $this->generateXMLNodes( array( 1=> 'Eating & Drinking', 2 => 'Around Town', 3 => 'Art' ) ) );
        $this->assertEquals( 3, Doctrine::getTable( 'ExportedItem' )->count() );
        $this->assertEquals( 5, Doctrine::getTable( 'ExportedItemHistory' )->count() );
        $this->updateRecordFields( 'ExportedItemHistory', 4, array( 'created_at' => '2010-10-20 12:00:01' ) );
        $this->updateRecordFields( 'ExportedItemHistory', 5, array( 'created_at' => '2010-10-20 12:00:01' ) );

        // import again
        $this->importXMLNodes( $this->generateXMLNodes( array( 1=> 'Music', 2 => 'Around Town', 3 => 'Eating & Drinking' ) ) );
        $this->assertEquals( 3, Doctrine::getTable( 'ExportedItem' )->count() );
        $this->assertEquals( 7, Doctrine::getTable( 'ExportedItemHistory' )->count() );
        $this->updateRecordFields( 'ExportedItemHistory', 6, array( 'created_at' => '2010-10-25 12:00:01' ) );
        $this->updateRecordFields( 'ExportedItemHistory', 7, array( 'created_at' => '2010-10-25 12:00:01' ) );
        $this->assertEquals( 7, Doctrine::getTable( 'ExportedItemHistory' )->count() );

        // assert changes
        $exportedItem1 = Doctrine::getTable( 'ExportedItem' )->find(1);
        $this->assertFalse( $exportedItem1->isInvoiceable( date('Y-m-d'), date('Y-m-d') ) );
        $this->assertFalse( $exportedItem1->isInvoiceable( '2010-10-01', '2010-10-18' ) );
        $this->assertFalse( $exportedItem1->isInvoiceable( '2010-10-24', '2010-10-28' ) );
        $this->assertTrue( $exportedItem1->isInvoiceable( '2010-10-20', '2010-10-20' ) );
        $this->assertTrue( $exportedItem1->isInvoiceable( '2010-10-01', '2010-10-28' ) );

        $exportedItem1 = Doctrine::getTable( 'ExportedItem' )->find(2);
        $this->assertTrue( $exportedItem1->isInvoiceable( '2010-10-01', '2010-10-18' ) );
        $this->assertFalse( $exportedItem1->isInvoiceable( '2010-10-18', '2010-10-28' ) );
        $this->assertTrue( $exportedItem1->isInvoiceable( '2010-10-01', '2010-10-28' ) );
        
    }

    public function testGetInvoicedUICategoryID()
    {
        $this->importXMLNodes( simplexml_load_file( TO_TEST_DATA_PATH . '/model/export_poi_10_12_2010.xml') ); // Import POI for Date 10/12/2010
        $this->importXMLNodes( simplexml_load_file( TO_TEST_DATA_PATH . '/model/export_poi_15_12_2010.xml') ); // Import POI for Date 15/12/2010

        $this->assertEquals( 3, Doctrine::getTable( 'ExportedItem' )->count() );
        $this->assertEquals( 5, Doctrine::getTable( 'ExportedItemHistory' )->count() );

        $item = Doctrine::getTable( 'ExportedItem' )->find(1);
        $this->assertEquals( 2, $item->getInvoicedUICategoryID() );

        $item = Doctrine::getTable( 'ExportedItem' )->find(3);
        $this->assertNull( $item->getInvoicedUICategoryID() );
    }
    
    private function generateXMLNodes( $arrayCategory )
    {
        $xmlString = '<vendor-pois vendor="timeout">';
        foreach( $arrayCategory as $poiID => $categoryName )
        {
            $xmlString .= '<entry vpid="ABC000000'.$poiID.'" modified="'. date('Y-m-d H:i:s') .'">';
            $xmlString .= '<vendor-category><![CDATA['.$categoryName.']]></vendor-category>';
            $xmlString .= '<property key="UI_CATEGORY"><![CDATA['.$categoryName.']]></property>';
            $xmlString .= '</entry>';
        }
        $xmlString .= '</vendor-pois>';

        return simplexml_load_string( $xmlString );
    }

    private function importXMLNodes( $xmlNodes, $stamp = null)
    {
        $stamp = ($stamp === null ) ? time() : $stamp;
        foreach( $xmlNodes->entry as $xmlNode)
        {
            Doctrine::getTable( 'ExportedItem' )->saveRecord( $xmlNode, 'poi', 1, $stamp);
        }
    }

    private function updateRecordFields( $table, $id, $valuesArray )
    {
        $record = Doctrine::getTable( $table )->find($id);
        $record->fromArray( $valuesArray );
        $record->save();
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