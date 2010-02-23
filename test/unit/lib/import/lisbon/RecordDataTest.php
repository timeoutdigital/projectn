<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test class for Record Data.
 *
 * @package test
 * @subpackage lisbon.import.lib.unit
 *
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 *
 * @version 1.0.1
 */
class RecordDataTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var RecordData
   */
  protected $object;

  /**
   * @var array
   */
  protected $data;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    $this->data = array( 'poi_name' => 'A place of interest' );
    $this->object = new RecordData( 'Poi', $this->data );
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
  }

  /**
   * test constructor requires name of an existing class
   */
  public function testConstructorRequiresExistingClass()
  {
    $this->setExpectedException('Exception');
    new RecordData( 'not_a_class' );
  }

  /**
   * test constructor accepts only array data
   */
  public function testConstructorAcceptsOnlyArrayData()
  {
    $this->setExpectedException('Exception');
    new RecordData( 'Poi', 'array' );
  }

  /**
   * test getClass() returns string, name of class
   */
  public function testGetClass()
  {
    $this->assertEquals( 'Poi', $this->object->getClass() );
  }

  /**
   * test getData() returns data array
   */
  public function testGetData()
  {
    $this->assertEquals( $this->data, $this->object->getData() );
  }
}
?>
