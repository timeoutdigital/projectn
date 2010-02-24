<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test class for lisbonImport.
 *
 * @package test
 * @subpackage lisbon.import.lib.unit
 *
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 *
 * @version 1.0.1
 */
class lisbonImportTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var lisbonImport
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    $vendor = ProjectN_Test_Unit_Factory::get( 'Vendor', array( 'city' => 'Lisbon', 'language' => 'xx-xx', 'time_zone' => 'Europe/Lisbon' ) );
    $this->object = new lisbonImport();
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testNada()
  {
    $this->markTestIncomplete();
  }
}
?>
