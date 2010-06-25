<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../../bootstrap.php';

/**
 * Test class for Yandex
 *
 * @package test
 * @subpackage geocoders.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class yandexGeocodeTest extends PHPUnit_Framework_TestCase 
{
  /**
   * @var yandexGeocoder
   */
  protected $object;
  protected $vendorObj;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp() 
  {
    $this->object = new yandexGeocoder( 'yandexGeocodeTestExactMockCurl' );
    $this->apiKey = 'ABIbCUwBAAAAQ2mQUwIA1oFXn_CffhQeYwZpC0CqL97RDwgAAAAAAAAAAAAu2D1hnUJ_hl3vURvlovEOBDueTQ==';
    $this->object->setApiKey( $this->apiKey );
  }

  public function testLookupUrl()
  {
    $address = 'Некрасова, 40, Санкт-Петербург';
    $this->object->setAddress( $address );
    $this->assertEquals( 'http://geocode-maps.yandex.ru/1.x/?geocode='.urlencode( $address ).'&key='.urlencode($this->apiKey),
                          $this->object->getLookupUrl()
                       );
  }

  public function testProcessResponseHasExact()
  {
    $this->object->setAddress( 'anything here will do. XML is mocked below' );
    $this->assertEquals( '59.938933', $this->object->getLatitude() );
    $this->assertEquals( '30.361323', $this->object->getLongitude() );
    $this->assertEquals( '9', $this->object->getAccuracy() );
  }
}

class yandexGeocodeTestExactMockCurl extends Curl
{
  public function getResponse()
  {
    return file_get_contents( TO_TEST_DATA_PATH . '/yandex_ymapsml.xml' );
  }
}
