<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../lib/Curl.class.php';

/**
 * Test class for Curl.
 * Generated by PHPUnit on 2010-02-10 at 16:09:19.
 */
class CurlTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var Curl
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    $this->object = new Curl( 'http://www.google.co.uk/search', array( 'q' => 'wave', 'foo' => 'bar' ) );
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
  }

  /**
   * @todo Implement testGetResponse().
   */
  public function testGetResponse()
  {
    $this->assertRegExp('/wave/', $this->object->getResponse() );
  }

  /**
   * @todo Implement testGetParametersString().
   */
  public function testGetParametersString()
  {
    $this->assertEquals('q=wave&foo=bar', $this->object->getParametersString() );
  }
}
?>