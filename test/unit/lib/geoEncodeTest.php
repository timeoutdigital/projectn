<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../bootstrap.php';

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
  protected $vendorObj;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp() {
    $this->object = new geoEncode( 'ABQIAAAAmYqAbSR2FhObG6Z6FL8nKhRU_WMEl20ocrt2ynGk4s1dqZjnGhSJ99yXGf0aEBbrPNUwBX1jiAA1gg', new geoEncodeTestMockCurl( null ) );
     try {

      ProjectN_Test_Unit_Factory::createDatabases();

      Doctrine::loadData('data/fixtures');
      

     }
    catch( Exception $e )
    {
      echo $e->getMessage();
    }
    
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown() {
    //Close DB connection
    ProjectN_Test_Unit_Factory::destroyDatabases();
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


  public function testSetAddress()
  {
      include('Net/URL2.php');

      $this->object->setAddress('Bermondsey Stree London SE1 3TQ');
      $urlObj = new Net_URL2($this->object->getLookupUrl());
      $this->assertFalse(key_exists('region', $urlObj->getQueryVariables()), 'Testing that no region is appended');

      //Test that a vendor region is added
      $this->vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('ny', 'en-US');
      $this->object->setAddress('Bermondsey Stree London SE1 3TQ', $this->vendorObj);
      $urlObj = new Net_URL2($this->object->getLookupUrl());

      $this->assertTrue(key_exists('region', $urlObj->getQueryVariables()), 'Testing that a region is appended');
  }


  /**
   * Test longitude is returned as an float
   */
  public function testLongitude()
  {
    $this->object->setAddress('Bermondsey Stree London SE1 3TQ');
    $this->assertType('float', $this->object->getGeoCode()->getLongitude());
  }


  /**
   * Test Latitude is an float
   */
  public function testLatitude()
  {
      $this->object->setAddress('Bermondsey Stree London SE1 3TQ');
    $this->assertType('float', $this->object->getGeoCode()->getLatitude());
  }


  /**
   * Test accuracy is returned as an float
   */
  public function testAccuracy()
  {

      //print_r($this->object->getGeoCode()->getAccuracy());
      $this->assertType('int', $this->object->getGeoCode()->getAccuracy());
  }
}

class geoEncodeTestMockCurl extends Curl
{
  public function exec()
  {
    return '200, 1, 2, 3';
  }
}
