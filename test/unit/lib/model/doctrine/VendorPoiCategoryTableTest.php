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


class VendorPoiCategoryTableTest extends PHPUnit_Framework_TestCase
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
        $poi = ProjectN_Test_Unit_Factory::add('poi');
        $category_1 = ProjectN_Test_Unit_Factory::add('VendorPoiCategory', array('name' => 'duplicate'));
        $category_2 = ProjectN_Test_Unit_Factory::add('VendorPoiCategory', array('name' => 'duplicate'));
        $category_3 = ProjectN_Test_Unit_Factory::add('VendorPoiCategory', array('name' => 'duplicate'));

        $this->assertEquals(1, $poi['VendorPoiCategory']->count(), 'By default, there should be a category associated to this poi');

        $poi['VendorPoiCategory'][] = $category_1;
        $poi['VendorPoiCategory'][] = $category_2;
        $poi['VendorPoiCategory'][] = $category_3;
        $poi->save();
        
        $this->assertEquals(4, $poi['VendorPoiCategory']->count(), '1+3 = 4 Categories in total');

        // Find the Duplicates
        $duplicates = Doctrine::getTable( 'VendorPoiCategory' )->findConcatDuplicateCategoryIdBy( $poi['vendor_id'], Doctrine_Core::HYDRATE_ARRAY );
        $this->assertTrue( is_array($duplicates) );
        $this->assertEquals( 1, count($duplicates) );
        $this->assertEquals( stringTransform::concatNonBlankStrings(',', array($category_1['id'], $category_2['id'], $category_3['id'])),
                $duplicates[0]['dupeIds'] );
        
    }

    public function testFindUnusedCategoriesBy()
    {
        // at this point we have No Poi's and All those categoris int he Table should be returned as un-used
        $this->assertEquals(7, Doctrine::getTable('VendorPoiCategory')->count());
        $this->assertEquals( 7, Doctrine::getTable('VendorPoiCategory')->findUnusedCategoriesBy( 1 )->count() );
    }

    public function testFindUnusedCategoriesByMapOneToRecordAndTest()
    {
        // at this point we have No Poi's and All those categoris int he Table should be returned as un-used
        $poi = ProjectN_Test_Unit_Factory::add( 'Poi' );
        $this->assertEquals(7, Doctrine::getTable('VendorPoiCategory')->count());
        $this->assertEquals( 6, Doctrine::getTable('VendorPoiCategory')->findUnusedCategoriesBy( 1 )->count(), '1 less than whats in the Table as 1 mapped to a record' );
    }
}