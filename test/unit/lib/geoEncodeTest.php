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
    $this->object = new geoEncode( 'geoEncodeTestMockCurl' );
    $this->object->setApiKey( 'ABQIAAAAmYqAbSR2FhObG6Z6FL8nKhRU_WMEl20ocrt2ynGk4s1dqZjnGhSJ99yXGf0aEBbrPNUwBX1jiAA1gg', 'geoEncodeTestMockCurl' );
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
    $address = 'foo&!"£';
    $this->object->setAddress( $address );
    $this->assertEquals( urlencode( $address ), $this->object->getAddress() );
    $this->assertRegexp( ':' . urlencode( $address ) . ':', $this->object->getLookupUrl() );
  }

  public function testGetLookupUrl()
  {
    $geocoder = new geoEncode();

    $geocoder->setAddress( 'test_address' );
    $this->assertRegExp(    '/test_address/', $geocoder->getLookupUrl() );
    $this->assertNotRegExp( '/test_bounds/' , $geocoder->getLookupUrl() );
    $this->assertNotRegExp( '/test_region/' , $geocoder->getLookupUrl() );
    $this->assertNotRegExp( '/test_key/'    , $geocoder->getLookupUrl() );

    $geocoder->setBounds( 'test_bounds' );
    $this->assertRegExp(    '/test_bounds/', $geocoder->getLookupUrl() );

    $geocoder->setRegion( 'test_region' );
    $this->assertRegExp(    '/test_region/', $geocoder->getLookupUrl() );

    $geocoder->setApiKey( 'test_api_key_which_should_be_86_chars_long_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx' );
    $this->assertRegExp(    '/test_api_key_which_should_be_86_chars_long_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx/', $geocoder->getLookupUrl() );
  }


  /**
   * Test longitude is returned as an float
   */
  public function testLongitude()
  {
    $this->object->setAddress('Bermondsey Stree London SE1 3TQ');
    $this->assertEquals( 3, $this->object->getGeoCode()->getLongitude() );
  }


  /**
   * Test Latitude is an float
   */
  public function testLatitude()
  {
    $this->object->setAddress('Bermondsey Stree London SE1 3TQ');
    $this->assertEquals( 2, $this->object->getGeoCode()->getLatitude() );
  }


  /**
   * Test accuracy is returned as an float
   */
  public function testAccuracy()
  {
    $this->object->setAddress('Bermondsey Stree London SE1 3TQ');
    $this->assertEquals(1, $this->object->getGeoCode()->getAccuracy());
  }
}

class geoEncodeTestMockCurl extends Curl
{
  public function exec()
  {
    return '200, 1, 2, 3'; //http code, accuracy, lat, long
  }
}
