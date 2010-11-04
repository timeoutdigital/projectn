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
    
    protected function runTask()
    {
        foreach( $this->options as $k => $v )
        {
                $options[] = "--$k=$v";
        }

        ob_start();
        $this->task->runFromCLI( new sfCommandManager, $options );
        return ob_get_clean();
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
        $this->options['type'] = 'mock-one';
        $this->runTask();
        $this->assertTrue( MockLondonClassOne::$mappFunctionCalled );
    }

    public function testLogStartOutput()
    {
        $this->options['type'] = 'mock-one';
        $taskOutput = $this->runTask();

        $this->assertRegExp('/start import for ' . $this->options['city'] . '/', $taskOutput);
        $this->assertRegExp('/type: ' . $this->options['type'] . '/', $taskOutput);
        $this->assertRegExp('/environment: ' . $this->options['env'] . '/', $taskOutput);
    }

    public function testLogEndOutput()
    {
        $this->options['type'] = 'mock-one';
        $taskOutput = $this->runTask();

        $this->assertRegExp('/end import for ' . $this->options['city'] . '/', $taskOutput);
        $this->assertRegExp('/type: ' . $this->options['type'] . '/', $taskOutput);
        $this->assertRegExp('/environment: ' . $this->options['env'] . '/', $taskOutput);
    }

}

class MockLondonClassOne extends DataMapper
{
    static public $constructParams;

    static public $mappFunctionCalled = false;
    
    public function  __construct( $vendor, $type )
    {
        self::$constructParams = $type;
    }

    public function mapTest()
    {
        self::$mappFunctionCalled = true;
    }

}

class MockLondonClassTwo extends MockLondonClassOne
{
}