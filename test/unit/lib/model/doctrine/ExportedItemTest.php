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
        foreach( $xmlExportPoi->entry as $xmlNode)
        {
            Doctrine::getTable( 'ExportedItem' )->saveRecord( $xmlNode, 'poi', 1 );
        }
        
        $this->assertEquals( 5, Doctrine::getTable( 'ExportedItem' )->count( ) );

        // Test Invoiceable
        $exportedItem = Doctrine::getTable( 'ExportedItem' )->find(1);
        $this->assertFalse( $exportedItem->isInvoiceable( null, null ) );
        $this->assertTrue( $exportedItem->isInvoiceable( date('Y-m-d' ), date('Y-m-d' ) ) );

        // This category is not in Category
        $exportedItem = Doctrine::getTable( 'ExportedItem' )->find(4);
        $this->assertEquals( 3, $exportedItem['ui_category_id'], 'This Record UI category ID should be 3 ( Around Town )');
        $this->assertFalse( $exportedItem->isInvoiceable( date('Y-m-d' ), date('Y-m-d' ) ) );
    }

    public function testIsInvoiceableDateRange()
    {
        $xml = $this->generateXMLNodes( array( 1=> 'Around Town', 2 => 'Eating & Drinking', 3 => 'Art' ) );
        $this->assertEquals( 3, count( $xml ) );

        // Import these XML nodes into DB
        foreach( $xml->entry as $xmlNode )
        {
            Doctrine::getTable( 'ExportedItem' )->saveRecord( $xmlNode, 'poi', 1 );
        }
        print_r( Doctrine::getTable( 'ExportedItem' )->findAll()->toArray());
        $this->assertEquals( 3, Doctrine::getTable( 'ExportedItem' )->count() );
        
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
    
}