<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../bootstrap.php';

require_once dirname(__FILE__).'/../../../lib/Curl.class.php';
require_once dirname(__FILE__).'/../../../lib/reverseGeocode.class.php';

/**
 * Test class for reverse geoencoding
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
class reverseGeocodeTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var reverseGeocode
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    $this->object = new reverseGeocode( 56.0076, -4.53186 );
  }

  /**
   * @todo Implement testGetAddresses().
   */
  public function testGetAddresses()
  {
    $this->assertTrue( $this->object->getAddressesXml() instanceof SimpleXMLElement );
  }

  /**
   * @todo test for google maps fail status code
   */
  public function testFailedStatusCodes()
  {
    $this->setExpectedException( 'Exception' );

    $this->object = new reverseGeocode( 560.0076, -4.53186 );
    $this->object->setApiKey( 'foo' );
    $this->object->getAddressesXml();
  }
}
?>
