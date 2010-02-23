<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test class for Null Doctrine Record.
 *
 * @package test
 * @subpackage lisbon.import.lib.unit
 *
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 *
 * @version 1.0.1
 */
class NullDoctrineRecordTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var NullDoctrineRecord
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    $this->object = new NullDoctrineRecord;
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  /**
   * Validating should throw an exception
   */
  public function testIsValidThrowsException()
  {
    $this->setExpectedException('Exception');
    $this->object->isValid();
  }

  /**
   * Saving should throw an exception
   */
  public function testSaveThrowsException()
  {
    $this->setExpectedException('Exception');
    $this->object->save();
  }
}
?>
