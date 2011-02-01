<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';
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

class VendorCategoryBlackListTableTest extends PHPUnit_Framework_TestCase
{
    /**
    * Sets up the fixture, for example, opens a network connection.
    * This method is called before a test is executed.
    */
    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases( );
        Doctrine::loadData('data/fixtures');

        // add Test Black list categories
        ProjectN_Test_Unit_Factory::add( 'vendorcategoryblacklist', array( 'name' => 'Other' ) );
        ProjectN_Test_Unit_Factory::add( 'vendorcategoryblacklist', array( 'name' => 'Test' ) );
    }

    /**
    * Tears down the fixture, for example, closes a network connection.
    * This method is called after a test is executed.
    */
    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    public function testGetCategoryNameInArrayBy()
    {
        $this->assertEquals( 2, Doctrine::getTable( 'VendorCategoryBlackList' )->count() );
        // add another for Different vendor
        ProjectN_Test_Unit_Factory::add( 'vendorcategoryblacklist', array( 'name' => 'Different vendor', 'vendor_id' => 2 ) );
        $this->assertEquals( 3, Doctrine::getTable( 'VendorCategoryBlackList' )->count() );

        $nameArray = Doctrine::getTable( 'VendorCategoryBlackList' )->getCategoryNameInArrayBy( 1 ); // get names by Vendor ID
        $this->assertTrue( is_array( $nameArray ) );
        $this->assertEquals( 2, count( $nameArray ) );
        $this->assertEquals( 'Other', $nameArray[0] );
    }
    
    public function testFilterByCategoryBlackList()
    {
        $invaidCategories = array( 'Test', 'Other' );
        $filteredCategories = Doctrine::getTable( 'VendorCategoryBlackList' )->filterByCategoryBlackList( 1, $invaidCategories );
        $this->assertEquals( 0, count( $filteredCategories ) );

        $oneValidCategory= array( 'Music', 'Other' );
        $filteredCategories = Doctrine::getTable( 'VendorCategoryBlackList' )->filterByCategoryBlackList( 1, $oneValidCategory );
        $this->assertEquals( 1, count( $filteredCategories ) );
        $this->assertEquals( 'Music', $filteredCategories[0] );

        $bothValidCategories= array( 'Music', 'Around Town' );
        $filteredCategories = Doctrine::getTable( 'VendorCategoryBlackList' )->filterByCategoryBlackList( 1, $bothValidCategories );
        $this->assertEquals( 2, count( $filteredCategories ) );
        $this->assertEquals( 'Music', $filteredCategories[0] );
        $this->assertEquals( 'Around Town', $filteredCategories[1] );
    }

    public function testFilterByCategoryBlackListDifferentVendor()
    {
        $invaidCategories = array( 'Test', 'Other' );
        $filteredCategories = Doctrine::getTable( 'VendorCategoryBlackList' )->filterByCategoryBlackList( 2, $invaidCategories );
        $this->assertEquals( 2, count( $filteredCategories ), "as this vendor don't have any black list yet, both should be returned" );
    }
}