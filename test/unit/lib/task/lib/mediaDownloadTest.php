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

class mediaDownloadTest extends PHPUnit_Framework_TestCase
{
    protected $options;

    protected function setUp()
    {
        parent::setUp();
        $this->task = new mediaDownloadTask( new sfEventDispatcher, new sfFormatter );

        $this->options['connection'] = 'project_n';
        $this->options['env'] = 'test';
    }

    protected function tearDown()
    {
        parent::tearDown();
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    protected function runTask()
    {
        foreach( $this->options as $k => $v ) $options[] = "--$k=$v";
        $this->task->runFromCLI( new sfCommandManager, $options );
    }

    public function testSomething()
    {
        
    }
}