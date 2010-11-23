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

class nagiosTaskTest extends PHPUnit_Framework_TestCase
{
    protected $options;

    protected function setUp()
    {
        parent::setUp();
        $this->task = new mockNagiosTask( new sfEventDispatcher, new sfFormatter );

        $this->options['connection'] = 'project_n';
        $this->options['env'] = 'test';
    }

    protected function _runTask()
    {
        foreach( $this->options as $k => $v ) $options[] = "--$k=$v";

        ob_start();
        $this->task->runFromCLI( new sfCommandManager, $options );
        return ob_get_clean();
    }

    public function testNoErrorOrWarnings()
    {
        $output = $this->_runTask();
        $this->assertEquals( 'ok', $output );
        $this->assertEquals( 0, $this->task->exitCode );
    }
    
    public function testAddError()
    {
        foreach( array( 'error #1', 'error #2' ) as $error )        $this->task->addError( $error );
        
        $output = explode( PHP_EOL, trim( $this->_runTask() ) );
        
        $this->assertEquals( 'error #1', $output[0] );
        $this->assertEquals( 'error #2', $output[1] );
        $this->assertEquals( 2, $this->task->exitCode );
    }

    public function testAddWarning()
    {
        foreach( array( 'warning #1', 'warning #2' ) as $warning )  $this->task->addWarning( $warning );

        $output = explode( PHP_EOL, trim( $this->_runTask() ) );

        $this->assertEquals( 'warning #1', $output[0] );
        $this->assertEquals( 'warning #2', $output[1] );
        $this->assertEquals( 1, $this->task->exitCode );
    }

    public function testAddErrorAndAddWarning()
    {
        foreach( array( 'error #1', 'error #2' ) as $error )        $this->task->addError( $error );
        foreach( array( 'warning #1', 'warning #2' ) as $warning )  $this->task->addWarning( $warning );

        $output = explode( PHP_EOL, trim( $this->_runTask() ) );

        $this->assertEquals( 'error #1', $output[0] );
        $this->assertEquals( 'error #2', $output[1] );
        $this->assertEquals( 'warning #1', $output[2] );
        $this->assertEquals( 'warning #2', $output[3] );
        $this->assertEquals( 2, $this->task->exitCode );
    }
}

class mockNagiosTask extends nagiosTask
{
    public $exitCode;
    
    protected function executeNagiosTask( $arguments = array(), $options = array() ){}

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
}