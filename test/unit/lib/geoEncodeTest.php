<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../lib/geoEncode.class.php';

/**
 * Test class for Geoencoding
 *
 * @package test
 * @subpackage lib.unit
 *
 * @author Timmy Bowler <timbowler@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class geoEncodeTest extends PHPUnit_Framework_TestCase {
  /**
   * @var geoEncode
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp() {
    $this->object = new geoEncode();
    $this->object->setAddress('Bermondsey Stree London SE1 3TQ');
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown() {
  }


 
  /**
   * Test that address is encoded.
   * @todo mock/stub the function
   *
   */
  public function testGetGeoCodeData()
  {
    $this->markTestSkipped();
    $this->assertType('array', $this->object->getGeoCode());
    $this->assertType('string',$this->object->getGeoCode('string'));

    $observer = $this->getMock('geoEncode' , array('getGeoCode'));
    $observer
      ->expects($this->once())
      ->method('getGeoCode')
      ->with(isType('string'));

   
    $subject = new Subject;
    $subject->attach($observer);
    $subject->doSomething();

    $stub = $this->getMock('geoEncode');
    $stub->expects($this->once())
         ->method('getGeoCode')
         ->will($this->returnValue('foo'));
         //->will($this->returnCallback('callback'));

    $this->assertEquals('foo', $stub->getGeoCode());
  }

  
  /**
   * Test longitude is returned as an float
   */
  public function testLongitude()
  {
    $this->assertType('float', $this->object->getGeoCode()->getLongitude());
  }


  /**
   * Test Latitude is an float
   */
  public function testLatitude()
  {
    $this->assertType('float', $this->object->getGeoCode()->getLatitude());
  }


  /**
   * Test accuracy is returned as an float
   */
  public function testAccuracy()
  {

      print_r($this->object->getGeoCode()->getAccuracy());
      $this->assertType('int', $this->object->getGeoCode()->getAccuracy());
  }


}
?>
