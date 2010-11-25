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

class categoriesCheckTaskTest extends PHPUnit_Framework_TestCase
{
    protected $options;

    protected function setUp()
    {
        parent::setUp();
        ProjectN_Test_Unit_Factory::createDatabases();

        $this->task = new mockCategoriesCheckTask( new sfEventDispatcher, new sfFormatter );

        $this->options['connection'] = 'project_n';
        $this->options['env'] = 'test';
    }

    protected function  tearDown() {
        parent::tearDown();
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    protected function _runTask()
    {
        foreach( $this->options as $k => $v ) $options[] = "--$k=$v";

        ob_start();
        $this->task->runFromCLI( new sfCommandManager, $options );
        return ob_get_clean();
    }

    private function _wipeTaskErrorsAndWarnings()
    {
        // Wipe Errors
        $this->task->clearErrors();
        $this->task->clearWarnings();
    }

    public function testNoCategoriesFound()
    {
        $output = explode( PHP_EOL, trim( $this->_runTask() ) );

        $this->assertEquals( 0, $this->task->exitCode );
        $this->assertEquals( 'ok', $output[0] );
    }

    public function testPoiCategoryMappedAndUnmappedCorrectlyReported()
    {
        $c = new VendorPoiCategory;
        $c['name'] = 'cat 1';
        $c['vendor_id'] = 1;
        $c->save();
        
        $output = explode( PHP_EOL, trim( $this->_runTask() ) );

        $this->assertEquals( 2, $this->task->exitCode );
        $this->assertEquals( 'Database contains 1 unused poi categories.', $output[0] );

        $this->_wipeTaskErrorsAndWarnings();

        // Map to a Poi
        $p = ProjectN_Test_Unit_Factory::add( 'Poi' );
        $p->addVendorCategory( 'cat 1' );
        $p->save();
        
        $output = explode( PHP_EOL, trim( $this->_runTask() ) );
        $this->assertEquals( 0, $this->task->exitCode );
        $this->assertEquals( 'ok', $output[0] );

        $this->_wipeTaskErrorsAndWarnings();

        // Unmap from Poi
        $p = Doctrine::getTable( 'Poi' )->findOneById( $p['id'] );
        $p->unlinkInDb( 'VendorPoiCategory', array( 1 ) );

        $output = explode( PHP_EOL, trim( $this->_runTask() ) );
        
        $this->assertEquals( 2, $this->task->exitCode );
        $this->assertEquals( 'Database contains 1 unused poi categories.', $output[0] );
    }

    public function testEventCategoryMappedAndUnmappedCorrectlyReported()
    {
        $c = new VendorEventCategory;
        $c['name'] = 'cat 1';
        $c['vendor_id'] = 1;
        $c->save();

        $output = explode( PHP_EOL, trim( $this->_runTask() ) );

        $this->assertEquals( 2, $this->task->exitCode );
        $this->assertEquals( 'Database contains 1 unused event categories.', $output[0] );

        $this->_wipeTaskErrorsAndWarnings();

        // Map to an Event
        $e = ProjectN_Test_Unit_Factory::add( 'Event' );
        $e->addVendorCategory( 'cat 1' );
        $e->save();

        $output = explode( PHP_EOL, trim( $this->_runTask() ) );
        $this->assertEquals( 0, $this->task->exitCode );
        $this->assertEquals( 'ok', $output[0] );

        $this->_wipeTaskErrorsAndWarnings();

        // Unmap from Event
        $e = Doctrine::getTable( 'Event' )->findOneById( $e['id'] );
        $e->unlinkInDb( 'VendorEventCategory', array( 1 ) );

        $output = explode( PHP_EOL, trim( $this->_runTask() ) );

        $this->assertEquals( 2, $this->task->exitCode );
        $this->assertEquals( 'Database contains 1 unused event categories.', $output[0] );
    }
    
    public function testUnusedCategories()
    {
        $c = new VendorPoiCategory;
        $c['name'] = 'cat 1';
        $c['vendor_id'] = 1;
        $c->save();

        $c = new VendorEventCategory;
        $c['name'] = 'cat 1';
        $c['vendor_id'] = 1;
        $c->save();
        
        $output = explode( PHP_EOL, trim( $this->_runTask() ) );

        $this->assertEquals( 2, $this->task->exitCode );
        $this->assertEquals( 'Database contains 1 unused poi categories.', $output[0] );
        $this->assertEquals( 'Database contains 1 unused event categories.', $output[1] );
    }

    public function testDuplicateCategories()
    {
        $c = new VendorPoiCategory;
        $c['name'] = 'cat 1';
        $c['vendor_id'] = 1;
        $c->save();

        $c = new VendorPoiCategory;
        $c['name'] = 'cat 1';
        $c['vendor_id'] = 1;
        $c->save();

        $c = new VendorEventCategory;
        $c['name'] = 'cat 1';
        $c['vendor_id'] = 1;
        $c->save();

        $c = new VendorEventCategory;
        $c['name'] = 'cat 1';
        $c['vendor_id'] = 1;
        $c->save();

        $output = explode( PHP_EOL, trim( $this->_runTask() ) );

        $this->assertEquals( 2, $this->task->exitCode );
        $this->assertEquals( 'Database contains 1 duplicate poi categories.', $output[0] );
        $this->assertEquals( 'Database contains 2 unused poi categories.', $output[1] );
        $this->assertEquals( 'Database contains 1 duplicate event categories.', $output[2] );
        $this->assertEquals( 'Database contains 2 unused event categories.', $output[3] );
    }
}

class mockCategoriesCheckTask extends categoriesCheckTask
{
    public $exitCode;

    protected function postExecute()
    {
        foreach( array_merge( $this->errors, $this->warnings ) as $message )
        {
            echo $message . PHP_EOL;
        }

        switch( false )
        {
            case empty( $this->errors )     : $this->exitCode = 2; break;
            case empty( $this->warnings )   : $this->exitCode = 1; break;
            default                         : echo 'ok'; $this->exitCode = 0;
        }
    }

    public function addWarning( $message )
    {
        $this->warnings[] = $message;
    }

    public function addError( $message )
    {
        $this->errors[] = $message;
    }

    public function clearWarnings()
    {
        $this->warnings = array();
    }

    public function clearErrors()
    {
        $this->errors = array();
    }
}