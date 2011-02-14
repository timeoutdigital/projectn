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
class LinkingVendorEventCategoryTableTest extends PHPUnit_Framework_TestCase
{
    /**
    * Sets up the fixture, for example, opens a network connection.
    * This method is called before a test is executed.
    */
    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases( );
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

    public function testMapCategoriesTo()
    {
        /* This Table is exists to create link between Event and VendorEventCategory tables,
         * hence, this test will be uisng Event and VendorEventCategoty to test this MapCategories feature
         */

        $event = ProjectN_Test_Unit_Factory::add( 'event' );
        $category_1 = ProjectN_Test_Unit_Factory::add('VendorEventCategory');
        $category_2 = ProjectN_Test_Unit_Factory::add('VendorEventCategory');
        $category_3 = ProjectN_Test_Unit_Factory::add('VendorEventCategory');
        $event['VendorEventCategory'][] = $category_1;
        $event['VendorEventCategory'][] = $category_2;
        $event['VendorEventCategory'][] = $category_3;
        $event->save();
        $event->refresh(true);
        $this->assertEquals( 3, $event['VendorEventCategory']->count() );

        // Map 1 & 3 to category 2
        Doctrine::getTable('LinkingVendorEventCategory')->mapCategoriesTo( $category_2['id'], array( $category_1['id'], $category_3['id']) );

        $event->refresh(true);
        $this->assertEquals( 1, $event['VendorEventCategory']->count(), 'After mapping others, this Event should have only 1 Category linked to it');
        $this->assertEquals( $category_2['id'], $event['VendorEventCategory'][0]['id']);

    }
}