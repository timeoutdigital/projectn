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
        print_r( $nameArray);
        $this->assertTrue( is_array( $nameArray ) );
        $this->assertEquals( 2, count( $nameArray ) );
        $this->assertEquals( 'Other', $nameArray[0] );
    }
}