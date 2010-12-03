<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';

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
    $vendor =  ProjectN_Test_Unit_Factory::add('Vendor', array( 'city' => 'london', 'language' => 'en-GB' ) );

    // Create new Mapper
    $this->mapper = new UnitTestSomeLondonAPIMapper( $vendor, array( 'curlImporterClassName' => 'curlMockApiCrawlerMock', 'datasource' => array( 'classname' => 'LondonAPICrawlerMockTest' ) ) );
    // Importer
    $importer = new Importer( );
    $importer->addDataMapper( $this->mapper );
    $importer->run(); // Execute Importer
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
      // there are only 2 records refereing to Details.. hence it should only have 2 records
      $this->assertEquals( 2, $this->mapper->getCount() );
  }
}

// Mock API crawler it self to override the SEARCH URL ONYL
class LondonAPICrawlerMockTest extends LondonAPICrawler
{
    protected $searchUrl = 'api_bars_and_pubs_search.xml';
}

// Mock CurlMock again to overide URL
class curlMockApiCrawlerMock extends CurlMock
{
    public function  getUrl() {
        $url = parent::getUrl();
        return TO_TEST_DATA_PATH . '/london/' .$url;
    }
}

// Dummy Mapper class to test the Api craewer
class UnitTestSomeLondonAPIMapper extends LondonAPIBaseMapper
{
  private $count = 0;

  public function  __construct(Doctrine_Record $vendor, $params) {

      // Create the apiCrawler using MOCK and Pass the curlImporter Mock
      $this->apiCrawler = new $params['datasource']['classname']( $params['curlImporterClassName'] );
      
      parent::__construct($vendor, $params);
   }

  public function mapSometing()
  {
      $this->crawlApi();
  }
  public function getCount(){ return $this->count; }
  public function getDetailsUrl(){ return 'api_bars_and_pubs_search.xml'; }
  public function getApiType(){ return 'Bars & pubs'; }
  public function doMapping( SimpleXMLElement $xml ){
    $this->count++;
  }
}