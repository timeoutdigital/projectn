<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../bootstrap.php';

/**
 * Test class for the curl importer
 *
 * @package test
 * @subpackage lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class arrayInstanceFactoryTest extends PHPUnit_Framework_TestCase
{
  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {    
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
  }

  public function testCreateInstanceWithAllParams()
  {
      $class            = 'MockForArrayInstanceFactoryTest';
      $param1           = 'arg 1';
      $param2           = 'arg 2';
      $paramWithDefault = 'arg 3';
      $paramCanBeNull   = 'arg 4';

      $specs = array(
        'class'            => $class,
        'param1'           => $param1,
        'param2'           => $param2,
        'paramWithDefault' => $paramWithDefault,
        'paramCanBeNull'   => $paramCanBeNull,
      );
      $testFactory = new arrayInstanceFactory( $specs );
      $instance = $testFactory->createInstance();

      $this->assertEquals( $class, get_class( $instance ) );
      $this->assertEquals( $param1, $instance->param1 );
      $this->assertEquals( $param1, $instance->param2 );
      $this->assertEquals( $paramWithDefault, $instance->paramWithDefault );
      $this->assertEquals( $paramCanBeNull, $instance->paramCanBeNull );
  }
}

class MockForArrayInstanceFactoryTest
{
    public $param1;
    public $param2;
    public $paramWithDefault;
    public $paramCanBeNull;

    public function __construct( $param1, $param2, $paramWithDefault = 'default', $paramCanBeNull = null)
    {
        $this->param1           = $param1;
        $this->param2           = $param1;
        $this->paramWithDefault = $paramWithDefault;
        $this->paramCanBeNull   = $paramCanBeNull;
    }
}
