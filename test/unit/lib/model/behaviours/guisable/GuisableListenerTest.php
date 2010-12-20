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
      Doctrine::loadData( 'data/fixtures' );
  }

  protected function tearDown()
  {
      ProjectN_Test_Unit_Factory::destroyDatabases();
  }


  public function testFindByVendorId()
  {

  }
}
