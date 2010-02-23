<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test class for London API Client.
 *
 * @package test
 * @subpackage london.import.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 *
 * @version 1.0.1
 */
class LondonAPICrawlerTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var LondonAPIClient
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    $this->object = new LondonAPICrawler();
    $this->object->setLimit( 11 );
    $this->mapper = new UnitTestSomeLondonAPIMapper($this->object);
    
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testo()
  {
    $this->object->crawlApi();
    $this->assertEquals( 11, $this->mapper->getCount() );
  }
}

class UnitTestSomeLondonAPIMapper extends LondonAPIBaseMapper
{
  private $count = 0;
  public function getCount(){ return $this->count; }
  public function getDetailsUrl(){ return 'http://api.timeout.com/v1/getBar.xml'; }
  public function getApiType(){ return 'Bars & Pubs'; }
  public function doMapping( SimpleXMLElement $xml ){
    $this->count++;
  }
}