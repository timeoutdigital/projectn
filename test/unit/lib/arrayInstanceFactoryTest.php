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
      $this->assertEquals( $param2, $instance->param2 );
      $this->assertEquals( $paramWithDefault, $instance->paramWithDefault );
      $this->assertEquals( $paramCanBeNull, $instance->paramCanBeNull );
  }

  public function testCreateInstanceWithMisorderedParams()
  {
      $class            = 'MockForArrayInstanceFactoryTest';
      $param1           = 'arg 1';
      $param2           = 'arg 2';
      $paramWithDefault = 'arg 3';
      $paramCanBeNull   = 'arg 4';

      $specs = array(
        'class'            => $class,
        'param2'           => $param2,
        'param1'           => $param1,
        'paramCanBeNull'   => $paramCanBeNull,
        'paramWithDefault' => $paramWithDefault,
      );
      $testFactory = new arrayInstanceFactory( $specs );
      $instance = $testFactory->createInstance();

      $this->assertEquals( $class, get_class( $instance ) );
      $this->assertEquals( $param1, $instance->param1 );
      $this->assertEquals( $param2, $instance->param2 );
      $this->assertEquals( $paramWithDefault, $instance->paramWithDefault );
      $this->assertEquals( $paramCanBeNull, $instance->paramCanBeNull );
  }

  public function testCreateInstanceWithoutNullParam()
  {
      $class            = 'MockForArrayInstanceFactoryTest';
      $param1           = 'arg 1';
      $param2           = 'arg 2';
      $paramWithDefault = 'arg 3';

      $specs = array(
        'class'            => $class,
        'param1'           => $param1,
        'param2'           => $param2,
        'paramWithDefault' => $paramWithDefault,
      );
      $testFactory = new arrayInstanceFactory( $specs );
      $instance = $testFactory->createInstance();

      $this->assertEquals( $class, get_class( $instance ) );
      $this->assertEquals( $param1, $instance->param1 );
      $this->assertEquals( $param2, $instance->param2 );
      $this->assertEquals( $paramWithDefault, $instance->paramWithDefault );
      $this->assertNull( $instance->paramCanBeNull );
  }

  public function testCreateInstanceWithoutDefaultParam()
  {
      $class            = 'MockForArrayInstanceFactoryTest';
      $param1           = 'arg 1';
      $param2           = 'arg 2';

      $specs = array(
        'class'            => $class,
        'param1'           => $param1,
        'param2'           => $param2,
      );
      $testFactory = new arrayInstanceFactory( $specs );
      $instance = $testFactory->createInstance();

      $this->assertEquals( $class, get_class( $instance ) );
      $this->assertEquals( $param1, $instance->param1 );
      $this->assertEquals( $param2, $instance->param2 );
      $this->assertEquals( 0, $instance->paramWithDefault );
      $this->assertNull( $instance->paramCanBeNull );
  }

  public function testCreateInstanceWithoutEnoughParams()
  {
      $class            = 'MockForArrayInstanceFactoryTest';
      $param1           = 'arg 1';

      $specs = array(
        'class'            => $class,
        'param1'           => $param1,
      );
      $testFactory = new arrayInstanceFactory( $specs );

      try
      {
        $instance = $testFactory->createInstance();
        $this->fail();
      }
      catch( Exception $e )
      {
          $this->assertTrue( true ); //is there a better way?
      }
  }

  public function testCreateInstanceWithoutClass()
  {
      $param1           = 'arg 1';

      $specs = array(
        'param1'           => $param1,
      );

      try
      {
        $testFactory = new arrayInstanceFactory( $specs );
        $this->fail();
      }
      catch( Exception $e )
      {
          $this->assertTrue( true ); //is there a better way?
      }
  }
}

class MockForArrayInstanceFactoryTest
{
    public $param1;
    public $param2;
    public $paramWithDefault;
    public $paramCanBeNull;

    public function __construct( $param1, $param2, $paramWithDefault = 0, $paramCanBeNull = null)
    {
        $this->param1           = $param1;
        $this->param2           = $param2;
        $this->paramWithDefault = $paramWithDefault;
        $this->paramCanBeNull   = $paramCanBeNull;
    }
}
