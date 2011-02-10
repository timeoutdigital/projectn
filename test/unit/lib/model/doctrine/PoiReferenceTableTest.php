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

class PoiReferenceTableTest extends PHPUnit_Framework_TestCase
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

    public function testRemoveRelationShip()
    {
        $this->assertEquals( 0, Doctrine::getTable( 'PoiReference' )->count() );

        $pr = new PoiReference;
        $pr['master_poi_id'] = 1;
        $pr['duplicate_poi_id'] = 2;
        $pr->save();
        $pr = new PoiReference;
        $pr['master_poi_id'] = 1;
        $pr['duplicate_poi_id'] = 3;
        $pr->save();
        $this->assertEquals( 2, Doctrine::getTable( 'PoiReference' )->count(), 'There should be 2 Relationship to Master poi with ID 1' );

        // Remove one
        Doctrine::getTable( 'PoiReference' )->removeRelationShip( 3 );
        $this->assertEquals( 1, Doctrine::getTable( 'PoiReference' )->count(), '1 of those relationship should have been removed' );
    }
}