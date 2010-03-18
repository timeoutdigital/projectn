<?php
require_once 'PHPUnit/Framework.php';

require_once dirname( __FILE__ ).'/../../../bootstrap.php';
require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';

/**
 * Test singapore import.
 *
 * @package test
 * @subpackage singapore.import.lib
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class singaporeImportTest extends PHPUnit_Framework_TestCase {
  /**
   * @var singaporeImport
   */
  protected $object;


  //protected $xmlObj;

  /**
   * @var Vendor
   */
  protected $vendor;

  /**
   * @var SimpleXmlElement
   */
  protected $dataXMLObject;

  /**
   * @var curlImporter
   */
  protected $stubCurlImporter;

  /**
   *
   * @var logImport
   */
  protected $logger;


  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp() 
  {
    setlocale(LC_MONETARY, 'en_US.UTF-8');

    ProjectN_Test_Unit_Factory::createDatabases();
    ProjectN_Test_Unit_Factory::add( 'Vendor', array(
      'city' => 'Singapore',
      'language' => 'en-US',
      'time_zone' => 'Asia/Singapore',
      'airport_code' => 'SIN',
      'inernational_dial_code' => '+65'
    ) );

    //Doctrine::loadData('data/fixtures');
   
    //$this->vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('singapore', 'en-US');

    //$this->stubCurlImporter = $this->getMock( 'curlImporter' );
    //$this->stubCurlImporter->expects( $this->any() )->method( 'pullXML' );

    //$this->logger = new logImport( $this->vendorObj );
    //$this->logger->setType('poi');

    //$this->object = new singaporeImportTestVersion( $this->vendorObj, $this->stubCurlImporter, $this->logger, 'http://www.timeoutsingapore.com/xmlapi/xml_detail/?venue={venueId}&key=ffab6a24c60f562ecf705130a36c1d1e' );
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown() 
  {
    //$this->logger->save();
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testPoiMapped()
  {
    $curlImporter = new singaporeVenuesMapperTestCurlImporter();

    $importer = new Importer();
    $importer->addDataMapper( new singaporeVenuesMapper( $curlImporter ) );
    $importer->run();

    $poiTable = Doctrine::getTable( 'Poi' );
    $this->assertEquals( 1, $poiTable->count(), 'Should have imported one poi' );

    $mappedPoi = $poiTable->findOneById( 1 );

    $reviewDate  = 'Thu, 04 Feb 2010 08:48:48 +0000';
    $description = '<excerpt>Enviro-minded eaters should get moving to this new vegetarian restaurant that uses foodstuffs free of pesticides and chemicals, sourced from Australia, Thailand and the US, and served in a space furnished with recycled materials. We recommend the healthy, greens-filled Sichuan sour spicy noodles.<br /><br /><em>Main courses $6.50-$9.</em>';
    $website     = 'http://www.timeout.com';
    $transport   = 'Near station: Little India';
    $phone       = '+65 656 392 0369';
    $street      = '#01-24-26 The Verge 2 Serangoon Rd';
    $price       = 'between $1.00 and $2.00';
    $this->assertEquals( 2154         , $mappedPoi['vendor_poi_id'] );
    $this->assertEquals( $reviewDate  , $mappedPoi['review_date'] );
    $this->assertEquals( 'en'         , $mappedPoi['local_language'] );
    $this->assertEquals( 'Vegsenz'    , $mappedPoi['name'] );
    $this->assertEquals( 'SGP'        , $mappedPoi['country'] );
    $this->assertEquals( $website     , $mappedPoi['url'] );
    //@todo test this
    //$this->assertEquals( $description , $mappedPoi['description'] );
    $this->assertEquals( $price           , $mappedPoi['price_information'] );
    $this->assertEquals( '9-5'        , $mappedPoi['openingtimes'] );
    //@todo test this
    //$this->assertEquals( ''           , $mappedPoi['longitude'], 'longitude' );
    //$this->assertEquals( ''           , $mappedPoi['latitude'], 'latitude' );
    $this->assertEquals( $transport   , $mappedPoi['public_transport_links'] );
    $this->assertEquals( $phone       , $mappedPoi['phone'] );
    $this->assertEquals( 'location'   , $mappedPoi['additional_address_details'] );
    $this->assertEquals( 'abc 890'    , $mappedPoi['zips'] );
    $this->assertEquals( $street      , $mappedPoi['street'] );
    $this->assertEquals( 'Singapore'  , $mappedPoi['city'] );
  }
}

class singaporeImportTestVersion extends singaporeImport
{
  public function setCurlImporter( $curlImporter )
  {
    $this->_curlImporter = $curlImporter;
  }

  protected function addImageHelper( Doctrine_Record $storeObject, SimpleXMLElement $element ) {
      return;
  }
}

class singaporeVenuesMapperTestCurlImporter extends curlImporter
{
  private $xml;

  private $urlToTestFileMap = array
  (
    'http://www.timeoutsingapore.com/xmlapi/venues/' 
      => '/all_of_singapore_full_venues_list.xml',

    'http://www.timeoutsingapore.com/xmlapi/xml_detail/'
      => '/venue_detail.xml',
  );

  public function pullXml( $url )
  {
    $file = $this->urlToTestFileMap[ $url ];
    $file = TO_TEST_DATA_PATH . '/singapore' . $file;
    $this->xml = simplexml_load_file( $file );
  }

  public function getXml()
  {
    return $this->xml;
  }
}

?>
