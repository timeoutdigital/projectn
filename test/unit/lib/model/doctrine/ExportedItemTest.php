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
    }

    public function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    public function testIsInvoiceableBasicTest()
    {
        // Import Sample Data
        $xmlExportPoi = simplexml_load_file( TO_TEST_DATA_PATH . '/model/exported_poi_sample.xml' );
        $this->importXMLNodes( $xmlExportPoi );
        
        $this->assertEquals( 5, Doctrine::getTable( 'ExportedItem' )->count( ) );

        // Test Invoiceable
        $exportedItem = Doctrine::getTable( 'ExportedItem' )->find(1);
        $this->assertFalse( $exportedItem->isInvoiceable( null, null ) );
        $this->assertTrue( $exportedItem->isInvoiceable( date('Y-m-d' ), date('Y-m-d' ) ) );

        // This category is not in Category
        $exportedItem = Doctrine::getTable( 'ExportedItem' )->find(4);
        $this->assertEquals( 3, $exportedItem['ExportedItemHistory'][0]['value'], 'This Record UI category ID should be 3 ( Around Town )');
        $this->assertFalse( $exportedItem->isInvoiceable( date('Y-m-d' ), date('Y-m-d' ) ) );
    }

    public function testIsInvoiceableDateRange()
    {
        $xml = $this->generateXMLNodes( array( 1=> 'Around Town', 2 => 'Eating & Drinking', 3 => 'Art' ) );
        $this->assertEquals( 3, count( $xml ) );

        // Import these XML nodes into DB
        $this->importXMLNodes( $xml );
        $this->assertEquals( 3, Doctrine::getTable( 'ExportedItem' )->count() );

        // Update the Dates manually to simulate time period
        $exporedItem = Doctrine::getTable( 'ExportedItemHistory' )->find(1);
        $exporedItem['created_at'] = '2010-10-15 12:00:00';
        $exporedItem->save();
        $exporedItem = Doctrine::getTable( 'ExportedItemHistory' )->find(2);
        $exporedItem['created_at'] = '2010-10-15 12:00:00';
        $exporedItem->save();
        $this->assertEquals( $exporedItem['created_at'], '2010-10-15 12:00:00');
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
//        $this->importXMLNodes( $this->generateXMLNodes( array( 2=> 'Music', 1 => 'Eating & Drinking' ) ) );
//        $exporedItem = Doctrine::getTable( 'ExportedItem' )->find(1);
//        $this->assertEquals(2, $exporedItem['ui_category_id']);
//        $this->assertEquals(1, $exporedItem['ExportedItemModification']->count() );
//        $this->assertNotEquals( $exporedItem['created_at'], $exporedItem['updated_at']);
//        $exporedItem = Doctrine::getTable( 'ExportedItem' )->find(2);
//        $this->assertEquals(4, $exporedItem['ui_category_id']);
//        $this->assertEquals(1, $exporedItem['ExportedItemModification']->count() );
//        $this->assertNotEquals( $exporedItem['created_at'], $exporedItem['updated_at']);
//        $exporedItem = Doctrine::getTable( 'ExportedItem' )->find(3);
//        $this->assertEquals(7, $exporedItem['ui_category_id']);
//        $this->assertEquals(0, $exporedItem['ExportedItemModification']->count() );
//        $this->assertEquals( $exporedItem['created_at'], $exporedItem['updated_at']);
//
//        /*
//         * Now;
//         * 1 is invoiceable as of TODAY
//         * 2 was invoiceable on 15/10/2010 not Today
//         */
//        $exporedItem = Doctrine::getTable( 'ExportedItem' )->find(1);print_r( $exporedItem->toArray());
//        $this->assertTrue( $exporedItem->isInvoiceable( date( 'Y-m-d' ), date( 'Y-m-d' ) ) );
//        $this->assertFalse( $exporedItem->isInvoiceable( '2010-10-15', '2010-10-15' ) );
//
//        $exporedItem = Doctrine::getTable( 'ExportedItem' )->find(2);
//        $this->assertFalse( $exporedItem->isInvoiceable( date( 'Y-m-d' ), date( 'Y-m-d' ) ) );
//        $this->assertTrue( $exporedItem->isInvoiceable( '2010-10-15', '2010-10-15' ) );

    }

    private function generateXMLNodes( $arrayCategory )
    {
        $xmlString = '<vendor-pois vendor="timeout">';
        foreach( $arrayCategory as $poiID => $categoryName )
        {
            $xmlString .= '<entry vpid="ABC000000'.$poiID.'">';
            $xmlString .= '<property key="UI_CATEGORY"><![CDATA['.$categoryName.']]></property>';
            $xmlString .= '</entry>';
        }
        $xmlString .= '</vendor-pois>';

        return simplexml_load_string( $xmlString );
    }

    private function importXMLNodes( $xmlNodes )
    {
        foreach( $xmlNodes->entry as $xmlNode)
        {
            Doctrine::getTable( 'ExportedItem' )->saveRecord( $xmlNode, 'poi', 1 );
        }
    }
    
}