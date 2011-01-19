<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 *
 * @package test
 * @subpackage task.lib.unit.test
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class sqlRunnerTaskTest extends PHPUnit_Framework_TestCase
{
    protected $options;

    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();

        $this->task = new sqlRunnerTaskMock( new sfEventDispatcher, new sfFormatter );
        $this->options['connection'] = 'project_n';
        $this->options['env'] = 'test';
        $this->options['scriptsFolder'] = 'test/unit/lib/scripts/sql';
    }

    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    public function testInvalidSql()
    {
        $this->setExpectedException( 'PDOException' );

        $this->options['scripts'] = 'testOne';

        $output = $this->_runTask();

        //check if no genres where inserted (see sql scripts) e.g. if the transaction
        //was rolled back successfully
        $this->assertEquals( 0, Doctrine::getTable( 'MovieGenre' )->findAll()->count() );
    }

    public function testValidSql()
    {
        $this->options['scripts'] = 'testTwo';
        $output = $this->_runTask();

        //check if two genres where inserted (see sql scripts)
        $this->assertEquals( 2, Doctrine::getTable( 'MovieGenre' )->count() );
    }

    private function _runTask()
    {
        foreach( $this->options as $k => $v ) $options[] = "--$k=$v";
        ob_start();
        $this->task->runFromCLI( new sfCommandManager, $options );
        return ob_get_clean();
    }

}

class sqlRunnerTaskMock extends sqlRunnerTask
{
    //this function is overloaded in order to net get the tasks output into
    //the test output. the ob methods in the _runTask() are nice and working
    //in general but not in case of an exception -> testInvalidSql()
    protected function writeLogLine( $message )
    {
        return;
    }
}