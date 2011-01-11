<?php
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../../bootstrap.php';
spl_autoload_register(array('Doctrine', 'autoload'));

/**
 * Test class for Guisable Listener
 *
 * @package test
 * @subpackage guisable.behaviours.model.lib.unit
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class GuisableListenerTest extends PHPUnit_Framework_TestCase
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
   * @see Guise and GuisableBehaviour class (the listener should prevent save)
   */
  public function testIfSavePreventedInGuisableMode()
  {
      $this->setExpectedException( 'GuiseException' );

      $this->vendorObject->useGuise( 'Shenzhen' );
      $this->vendorObject['city'] = 'test';
      $this->vendorObject->save();
  }

  /**
   * @see Guise and GuisableBehaviour class (the listener should prevent delete)
   */
  public function testIfDeletePreventedInGuisableMode()
  {
      $this->setExpectedException( 'GuiseException' );

      $this->vendorObject->useGuise( 'Shenzhen' );
      $this->vendorObject->delete();
  }
}
