
<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test class for London API Bars And Pubs Mapper.
 *
 * @package test
 * @subpackage london.import.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 *
 * @version 1.0.1
 */
class LondonAPIBaseTest extends PHPUnit_Framework_TestCase
{

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    ProjectN_Test_Unit_Factory::add( 'Vendor', array( 
      'city' => 'london', 
      'language' => 'en-GB', 
      ) );
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
   * @see unfuddled ticket #253
   */
  public function testPoiUrlIsNotTimeoutUrlAndTimeoutLinkIsStoredAsAProperty()
  {
    $mock = new MockLondonAPIBaseMapper();

    $poi = new Poi();
    $xml = simplexml_load_string( '<xml><url>http://www.google.com</url><webUrl>http://www.timeout.com</webUrl></xml>' );
    $mock->map( $poi, $xml );

    $this->assertEquals( 'http://www.google.com', $poi['url'] );
    $this->assertEquals( 'Timeout_link', $poi['PoiProperty'][0]['lookup'] );
    $this->assertEquals( 'http://www.timeout.com', $poi['PoiProperty'][0]['value'] );
  }

  /**
   * @see unfuddled ticket #250
   */
  public function testCommaLondonNotInEndOfAddressField()
  {
    $mock = new MockLondonAPIBaseMapper();

    $poi = new Poi();
    $xml = simplexml_load_string( '<xml><address>foo, London </address></xml>' );
    $mock->map( $poi, $xml );
    $this->assertEquals( 'foo', $poi['street'] );
  }
}

class MockLondonAPIBaseMapper extends LondonAPIBaseMapper
{
  public function map( Poi $poi, SimpleXMLElement $xml )
  {
    $this->mapCommonPoiMappings( $poi, $xml );
  }
  public function getDetailsUrl(){}
  public function getApiType(){}
  public function doMapping( SimpleXMLElement $xml ){}
}
