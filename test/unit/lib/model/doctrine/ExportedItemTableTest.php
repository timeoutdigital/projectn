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
        $this->assertEquals( 0 , Doctrine::getTable( 'ExportedItemModification' )->count(), "There is no Modification yet!");

        $xmlNode = $xmlExportPoi->entry[1];
        Doctrine::getTable( 'ExportedItem' )->saveRecord( $xmlNode, 'poi', 1 );
        $this->assertEquals( 1 , Doctrine::getTable( 'ExportedItem' )->count(), "Since this is repeating, there should only have 1 record");
        $this->assertEquals( 1 , Doctrine::getTable( 'ExportedItemModification' )->count(), "UI category has been changed, There should be 1 Record in modification");
        
        $modifiedRecord = Doctrine::getTable( 'ExportedItemModification' )->find(1);
        $this->assertEquals( '0' , $modifiedRecord['value_before_change'], "Last vaue should 0 as Nightout don't exists");
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
        $this->assertEquals( 1 , Doctrine::getTable( 'ExportedItemModification' )->count(), "UI category has been changed, There should be 1 Record in modification");
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
        $this->assertEquals( 2, $record['ui_category_id'] );
    }
}