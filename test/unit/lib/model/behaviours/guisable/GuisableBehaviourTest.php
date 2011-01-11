<?php
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../../bootstrap.php';
spl_autoload_register(array('Doctrine', 'autoload'));

/**
 * Test class for Guisable Behaviour
 *
 * @package test
 * @subpackage guisable.behaviours.model.lib.unit
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 * @todo make this test independent of the projectn yaml
 *
 */
class GuisableBehaviourTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var Vendor
   */
  protected $vendorObject;

  protected function setUp()
  {
      ProjectN_Test_Unit_Factory::createDatabases();    
      $this->vendorObject = ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'inernational_dial_code' => '+1' ) );
      ProjectN_Test_Unit_Factory::createGuise( $this->vendorObject, 'Shenzhen', array( 'inernational_dial_code' => '+755' ) );
  }

  protected function tearDown()
  {
      $this->vendorObject->stopUsingGuise();
      ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  /**
   * @see Guise and GuisableListener class (the listener should prevent save)
   */
  public function testIfSavePreventedInGuisableMode()
  {
      $this->setExpectedException( 'GuiseException' );

      $this->vendorObject->useGuise( 'Shenzhen' );
      $this->vendorObject['city'] = 'test';
      $this->vendorObject->save();
  }

  /**
   * @see Guise and GuisableListener class (the listener should prevent delete)
   */
  public function testIfDeletePreventedInGuisableMode()
  {
      $this->setExpectedException( 'GuiseException' );

      $this->vendorObject->useGuise( 'Shenzhen' );
      $this->vendorObject->delete();
  }

  public function testIsInGuise()
  {
      $this->assertFalse( $this->vendorObject->isInGuise() );
      $this->vendorObject->useGuise( 'Shenzhen' );
      $this->assertTrue( $this->vendorObject->isInGuise() );
  }

  public function testGetCurrentGuiseInUse()
  {
      $this->assertFalse( $this->vendorObject->getCurrentGuiseInUse() );
      $this->vendorObject->useGuise( 'Shenzhen' );
      $this->assertEquals( 'Shenzhen', $this->vendorObject->getCurrentGuiseInUse() );
  }

  public function testStopUsingGuise()
  {
      $this->vendorObject->useGuise( 'Shenzhen' );
      $this->assertTrue( $this->vendorObject->isInGuise() );
      $this->vendorObject->stopUsingGuise();
      $this->assertFalse( $this->vendorObject->isInGuise() );
  }

  public function testGuiseExists()
  {
      $this->assertTrue( $this->vendorObject->guiseExists( 'Shenzhen' ) );
      $this->assertFalse( $this->vendorObject->guiseExists( 'test' ) );
  }

  public function testUseGuise()
  {
      $this->vendorObject->useGuise( 'Shenzhen' );
      $this->assertTrue( $this->vendorObject->isInGuise() );
      $this->vendorObject->stopUsingGuise();

      $this->setExpectedException( 'Doctrine_Record_Exception' );
      $this->vendorObject->useGuise( 'test' );
  }

  public function testGuisedValue()
  {
      $this->assertEquals( $this->vendorObject[ 'inernational_dial_code' ], '+1' );

      $this->vendorObject->useGuise( 'Shenzhen' );

      $this->assertEquals( $this->vendorObject[ 'inernational_dial_code' ], '+755' );
  }

  public function testUseGuiseIfExists()
  {
      $this->vendorObject->useGuiseIfExists( 'Shenzhen' );
      $this->assertTrue( $this->vendorObject->isInGuise() );
      $this->vendorObject->stopUsingGuise();
      $this->vendorObject->useGuiseIfExists( 'test' );
      $this->assertFalse( $this->vendorObject->isInGuise() );
      $this->vendorObject->stopUsingGuise();
  }
}
