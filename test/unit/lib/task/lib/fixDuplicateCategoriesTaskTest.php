<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 *
 * @package test
 * @subpackage task.lib.unit.test
 *
 * @author Peter Johnson <peterjohnson@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class fixDuplicateCategoriesTaskTest extends PHPUnit_Framework_TestCase
{
    protected $options;

    protected function setUp()
    {
        parent::setUp();
        $this->task = new fixDuplicateCategoriesTask( new sfEventDispatcher, new sfFormatter );

        $this->options['connection'] = 'project_n';
        $this->options['env'] = 'test';

        ProjectN_Test_Unit_Factory::createDatabases();
        
        $this->populateDatabase();
    }

    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    /**
     * Fill Database With:
     *
     * 2x Vendor
     * 2x Poi   ( sharing VendorPoiCategory 'test name' )
     * 2x Event ( sharing VendorEventCategory 'something' )
     */
    protected function populateDatabase()
    {
        $this->vendor1 = ProjectN_Test_Unit_Factory::add( 'Vendor' );
        $this->vendor2 = ProjectN_Test_Unit_Factory::add( 'Vendor' );

        // Pois
        $this->poi1 = ProjectN_Test_Unit_Factory::add( 'Poi' );
        $this->poi2 = ProjectN_Test_Unit_Factory::add( 'Poi' );

        $vpc = Doctrine::getTable('VendorPoiCategory')->findAll();

        $this->assertEquals( 2,             Doctrine::getTable('Poi')->findAll()->count() );
        $this->assertEquals( 1,             count( $vpc ) );
        $this->assertEquals( 1,             $vpc[0]['id'] );
        $this->assertEquals( 'test name',   $vpc[0]['name'] );

        // Events
        $this->event1 = ProjectN_Test_Unit_Factory::get( 'Event' );
        $this->event1->addVendorCategory( 'something');
        $this->event1->save();

        $this->event2 = ProjectN_Test_Unit_Factory::add( 'Event' );
        $this->event2->addVendorCategory( 'something');
        $this->event2->save();

        $vec = Doctrine::getTable('VendorEventCategory')->findAll();

        $this->assertEquals( 2,             Doctrine::getTable('Event')->findAll()->count() );
        $this->assertEquals( 1,             count( $vec ) );
        $this->assertEquals( 1,             $vec[0]['id'] );
        $this->assertEquals( 'something',   $vec[0]['name'] );
    }

    public function testDryRun()
    {
        $output = $this->runTask();

        $vpc = Doctrine::getTable('VendorPoiCategory')->findAll();

        $this->assertEquals( 1,             count( $vpc ) );
        $this->assertEquals( 1,             $vpc[0]['id'] );
        $this->assertEquals( 'test name',   $vpc[0]['name'] );

        $vec = Doctrine::getTable('VendorEventCategory')->findAll();

        $this->assertEquals( 1,             count( $vec ) );
        $this->assertEquals( 1,             $vec[0]['id'] );
        $this->assertEquals( 'something',   $vec[0]['name'] );
    }
    
    public function testRemoveUnusedPoiCategory()
    {
        $output = $this->runTask();

        $vpc = new VendorPoiCategory;
        $vpc['name'] = 'test name';
        $vpc['Vendor'] = $this->vendor1;
        $vpc->save();

        $vpc = Doctrine::getTable('VendorPoiCategory')->findAll();

        $this->assertEquals( 2,             count( $vpc ) );
        $this->assertEquals( 1,             $vpc[0]['id'] );
        $this->assertEquals( 'test name',   $vpc[0]['name'] );
        $this->assertEquals( 2,             $vpc[1]['id'] );
        $this->assertEquals( 'test name',   $vpc[1]['name'] );

        $output = $this->runTask();

        $vpc = Doctrine::getTable('VendorPoiCategory')->findAll();

        $this->assertEquals( 1,             count( $vpc ) );
        $this->assertEquals( 1,             $vpc[0]['id'] );
        $this->assertEquals( 'test name',   $vpc[0]['name'] );
    }

    public function testRemoveDuplicatePoiCategory()
    {
        $output = $this->runTask();

        $vpc = new VendorPoiCategory;
        $vpc['name'] = 'test name';
        $vpc['Vendor'] = $this->vendor1;
        $vpc->save();

        $this->poi2['VendorPoiCategory'][] = $vpc;

        $vpc = Doctrine::getTable('VendorPoiCategory')->findAll();

        $this->assertEquals( 2,             count( $vpc ) );
        $this->assertEquals( 1,             $vpc[0]['id'] );
        $this->assertEquals( 'test name',   $vpc[0]['name'] );
        $this->assertEquals( 2,             $vpc[1]['id'] );
        $this->assertEquals( 'test name',   $vpc[1]['name'] );

        $output = $this->runTask();

        $vpc = Doctrine::getTable('VendorPoiCategory')->findAll();

        $this->assertEquals( 1,             count( $vpc ) );
        $this->assertEquals( 1,             $vpc[0]['id'] );
        $this->assertEquals( 'test name',   $vpc[0]['name'] );
    }

    public function testRemoveUnusedEventCategory()
    {
        $output = $this->runTask();

        $vec = new VendorEventCategory;
        $vec['name'] = 'something';
        $vec['Vendor'] = $this->vendor1;
        $vec->save();

        $vec = Doctrine::getTable('VendorEventCategory')->findAll();

        $this->assertEquals( 2,             count( $vec ) );
        $this->assertEquals( 1,             $vec[0]['id'] );
        $this->assertEquals( 'something',   $vec[0]['name'] );
        $this->assertEquals( 2,             $vec[1]['id'] );
        $this->assertEquals( 'something',   $vec[1]['name'] );

        $output = $this->runTask();

        $vec = Doctrine::getTable('VendorEventCategory')->findAll();

        $this->assertEquals( 1,             count( $vec ) );
        $this->assertEquals( 1,             $vec[0]['id'] );
        $this->assertEquals( 'something',   $vec[0]['name'] );
    }

    public function testRemoveDuplicateEventCategory()
    {
        $output = $this->runTask();

        $vec = new VendorEventCategory;
        $vec['name'] = 'something';
        $vec['Vendor'] = $this->vendor1;
        $vec->save();

        $vec = Doctrine::getTable('VendorEventCategory')->findAll();

        $this->assertEquals( 2,             count( $vec ) );
        $this->assertEquals( 1,             $vec[0]['id'] );
        $this->assertEquals( 'something',   $vec[0]['name'] );
        $this->assertEquals( 2,             $vec[1]['id'] );
        $this->assertEquals( 'something',   $vec[1]['name'] );

        $output = $this->runTask();

        $vec = Doctrine::getTable('VendorEventCategory')->findAll();

        $this->assertEquals( 1,             count( $vec ) );
        $this->assertEquals( 1,             $vec[0]['id'] );
        $this->assertEquals( 'something',   $vec[0]['name'] );
    }

    protected function runTask()
    {
        foreach( $this->options as $k => $v ) $options[] = "--$k=$v";

        ob_start();
        $this->task->runFromCLI( new sfCommandManager, $options );
        return ob_get_clean();
    }
}