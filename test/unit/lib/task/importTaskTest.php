<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../../bootstrap.php';

/**
 *
 * @package test
 * @subpackage task.lib.unit.test
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class importTaskTest extends PHPUnit_Framework_TestCase
{

    protected $config;

    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();
        ProjectN_Test_Unit_Factory::add( 'vendor', array(
            'city'     => 'london',
            'language' => 'en-GB'
        ) );

        $this->task = new importTask( new sfEventDispatcher, new sfFormatter );

        $this->options['connection'] = 'project_n';
        $this->options['env'] = 'test';
        $this->options['city'] = 'london';
        $this->options['configFolder'] = TO_TEST_ROOT_PATH . '/config';
    }

    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }
    
    protected function runTask( $trapResult = false )
    {
        foreach( $this->options as $k => $v )
        {
                $options[] = "--$k=$v";
        }

        if ( $trapResult ) ob_start();
        $this->task->runFromCLI( new sfCommandManager, $options );
        if ( $trapResult ) return ob_get_clean();
    }

    public function testCorrectOptionAndClassOne()
    {
        $this->options['type'] = 'mock-one';
        $this->runTask();
        $this->assertEquals( 'test-type', MockLondonClassOne::$constructParams['type'] );
    }

    public function testCorrectOptionAndClassTwo()
    {
        $this->options['type'] = 'mock-two';
        $this->runTask();
        $this->assertEquals( 'footest', MockLondonClassTwo::$constructParams['foo'] );
        $this->assertEquals( 'bartest', MockLondonClassTwo::$constructParams['bar'] );
        $this->assertEquals( 'baztest', MockLondonClassTwo::$constructParams['baz'] );
    }

    public function testIfRunIsCalled()
    {
        //check it
    }

    public function testLogOutput()
    {
        //test for log output if present
        //2010-09-15 14:42:26 -- start import for london (type: mock-one, environment: test) --
        //2010-09-15 14:53:37 -- start import for london (type: mock-two, environment: test) --

        //test it by calling the runTask like this runTask( true ) --> to trap the result
    }
}

class MockLondonClassOne
{
    static public $constructParams;
    
    public function  __construct( $vendor, $type )
    {
        self::$constructParams = $type;
    }

    public function run()
    {
        echo 'yoooo';
    }

}

class MockLondonClassTwo
{
    static public $constructParams;

    public function  __construct( $vendor, $type )
    {
        self::$constructParams = $type;
    }
}