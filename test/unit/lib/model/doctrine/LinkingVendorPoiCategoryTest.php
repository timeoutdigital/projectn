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

class LinkingVendorPoiCategoryTest extends PHPUnit_Framework_TestCase
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
        /* This Table is exists to create link between Poi and VendorPoiCategory tables,
         * hence, this test will be uisng POI and VendorPoiCategoty to test this MapCategories feature
         */

        $poi = ProjectN_Test_Unit_Factory::add( 'poi' );
        $category_1 = ProjectN_Test_Unit_Factory::add('VendorPoiCategory');
        $category_2 = ProjectN_Test_Unit_Factory::add('VendorPoiCategory');
        $category_3 = ProjectN_Test_Unit_Factory::add('VendorPoiCategory');
        $poi['VendorPoiCategory'][] = $category_1;
        $poi['VendorPoiCategory'][] = $category_2;
        $poi['VendorPoiCategory'][] = $category_3;
        $poi->save();
        $poi->refresh(true);
        $this->assertEquals( 4, $poi['VendorPoiCategory']->count() );

        // Map 1 & 3 to category 2
        Doctrine::getTable('LinkingVendorPoiCategory')->mapCategoriesTo( $category_2['id'], array( $category_1['id'], $category_3['id']) );

        $poi->refresh(true);
        $this->assertEquals( 2, $poi['VendorPoiCategory']->count(), 'After mapping other, this POI should have only 2 Category linked to it');
        $this->assertEquals( $category_2['id'], $poi['VendorPoiCategory'][1]['id']);
        
    }
}