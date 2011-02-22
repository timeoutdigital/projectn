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

    public function testRelatePois()
    {
        $master_poi = ProjectN_Test_Unit_Factory::add('poi');
        $duplicate_poi = ProjectN_Test_Unit_Factory::add('poi');
        
        $this->assertEquals( false, $master_poi->isDuplicate() );
        $this->assertEquals( false, $master_poi->isMaster() );

        $this->assertEquals( false, $duplicate_poi->isDuplicate() );
        $this->assertEquals( false, $duplicate_poi->isMaster() );

        Doctrine::getTable( 'PoiReference' )->relatePois( $master_poi['id'], $duplicate_poi['id']);
        $this->assertEquals( false, $master_poi->isDuplicate() );
        $this->assertEquals( true, $master_poi->isMaster() );
        $this->assertEquals( true, $duplicate_poi->isDuplicate() );
        $this->assertEquals( false, $duplicate_poi->isMaster() );
        
    }

    public function testRelatePoisDuplicateAsMaster()
    {
        $master_poi = ProjectN_Test_Unit_Factory::add('poi');
        $duplicate_poi1 = ProjectN_Test_Unit_Factory::add('poi');
        $duplicate_poi2 = ProjectN_Test_Unit_Factory::add('poi');

        Doctrine::getTable( 'PoiReference' )->relatePois( $master_poi['id'], $duplicate_poi1['id']);
        $this->assertEquals( 1, Doctrine::getTable('PoiReference')->count());

        // When you try adding Duplicate Poi as Master, it should throw Exception
        $this->setExpectedException( 'PoiReferenceTableException');
        Doctrine::getTable( 'PoiReference' )->relatePois( $duplicate_poi1['id'], $duplicate_poi2['id']);
    }

    public function testRelatePoisMasterAsDuplicate()
    {
        $master_poi1 = ProjectN_Test_Unit_Factory::add('poi');
        $master_poi2= ProjectN_Test_Unit_Factory::add('poi');
        $duplicate_poi = ProjectN_Test_Unit_Factory::add('poi');

        Doctrine::getTable( 'PoiReference' )->relatePois( $master_poi1['id'], $duplicate_poi['id']);
        $this->assertEquals( 1, Doctrine::getTable('PoiReference')->count());

        // Master Poi of cannot be added as duplicate POI
        $this->setExpectedException( 'PoiReferenceTableException');
        Doctrine::getTable( 'PoiReference' )->relatePois( $master_poi2['id'], $master_poi1['id']);
    }

    public function testRelatePoisAsDuplicateTwice()
    {
        $master_poi1 = ProjectN_Test_Unit_Factory::add('poi');
        $master_poi2 = ProjectN_Test_Unit_Factory::add('poi');
        $duplicate_poi = ProjectN_Test_Unit_Factory::add('poi');

        Doctrine::getTable( 'PoiReference' )->relatePois( $master_poi1['id'], $duplicate_poi['id'] );

        $this->setExpectedException( 'PoiReferenceTableException' );
        Doctrine::getTable( 'PoiReference' )->relatePois( $master_poi2['id'], $duplicate_poi['id'] );
    }

    public function testRemoveDuplicateReferences()
    {
        $master_poi = ProjectN_Test_Unit_Factory::add('poi');
        $duplicate_poi1 = ProjectN_Test_Unit_Factory::add('poi');
        $duplicate_poi2 = ProjectN_Test_Unit_Factory::add('poi');

        $duplicate_poi1->setMasterPoi( $master_poi );
        $duplicate_poi1->save();
        $duplicate_poi2->setMasterPoi( $master_poi );
        $duplicate_poi2->save();

        $this->assertEquals( 2, $master_poi->getDuplicatePois()->count() );
        $this->assertEquals( true, $master_poi->isMaster() );
        Doctrine::getTable( 'PoiReference' )->removeDuplicateReferences( $master_poi['id'] );
        $this->assertEquals( false, $master_poi->getDuplicatePois(), 'this should not return anything as there is nothing to fetch' );
        $this->assertEquals( false, $master_poi->isMaster() );
    }
}