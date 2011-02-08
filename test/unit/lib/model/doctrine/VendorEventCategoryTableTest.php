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

class VendorEventCategoryTableTest extends PHPUnit_Framework_TestCase
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

    public function testFindConcatDuplicateCategoryIdBy()
    {
        $event = ProjectN_Test_Unit_Factory::add('event');
        $category_1 = ProjectN_Test_Unit_Factory::add('VendorEventCategory', array('name' => 'duplicate'));
        $category_2 = ProjectN_Test_Unit_Factory::add('VendorEventCategory', array('name' => 'duplicate'));
        $category_3 = ProjectN_Test_Unit_Factory::add('VendorEventCategory', array('name' => 'duplicate'));

        $this->assertEquals(0, $event['VendorEventCategory']->count(), 'Event don\'t have any category associated by default?');

        $event['VendorEventCategory'][] = $category_1;
        $event['VendorEventCategory'][] = $category_2;
        $event['VendorEventCategory'][] = $category_3;
        $event->save();

        $this->assertEquals(3, $event['VendorEventCategory']->count(), '1+3 = 4 Categories in total');

        // Find the Duplicates
        $duplicates = Doctrine::getTable( 'VendorEventCategory' )->findConcatDuplicateCategoryIdBy( $event['vendor_id'], Doctrine_Core::HYDRATE_ARRAY );
        $this->assertTrue( is_array($duplicates) );
        $this->assertEquals( 1, count($duplicates) );
        $this->assertEquals( stringTransform::concatNonBlankStrings(',', array($category_1['id'], $category_2['id'], $category_3['id'])),
                $duplicates[0]['dupeIds'] );
    }
}