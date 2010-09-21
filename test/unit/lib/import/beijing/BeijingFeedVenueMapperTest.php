<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test of beijing Venue Mapper
 *
 * @package test
 * @subpackage beijing.import.lib.unit
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */

class BeijingFeedVenueMapperTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var DataMaper
     */
    protected $dataMapper;

    /**
     * @var PDO Database Connection object
     */
    private $pdoDB;

    /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    Doctrine::loadData('data/fixtures');

    //$this->connectToPDODB();
    $this->createDummyDB();

    $this->dataMapper = new BeijingFeedVenueMapper( $this->pdoDB );
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
      $this->pdoDB = null;
      ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testMapVenues()
  {
      $importer = new Importer();
      $importer->addDataMapper( $this->dataMapper );
      $importer->run();

      // Get All from
      $pois = Doctrine::getTable('Poi')->findAll();

      $this->assertEquals( 2, $pois->count(), 'Should be 2, as two dummy data Inserted for Beijing and 1 Other City' );

      $poi = $pois[0];

      // Assert 1st POI
      $this->assertEquals( 'Sofitel Wanda', $poi['name'] );
      $this->assertEquals( 'Wanda Plaza, Chaoyang' , $poi['additional_address_details'] ); // Building name & Neighbourhood->name merged together
      $this->assertEquals( '93 Jianguo Lu, Tower C Wanda Plaza, Chaoyang district', $poi['provider'] );
      $this->assertEquals( 'beijing', $poi['Vendor']['city'] );
      $this->assertEquals( '', $poi['zips'] );
      $this->assertEquals( $poi['Vendor']['country_code_long'], $poi['country'] );
      $this->assertEquals( '', $poi['email'] );
      $this->assertEquals( 'http://www.theemperor.com.cn', $poi['url'] );
      $this->assertEquals( '+86 8 599 6666', $poi['phone'] );

      $this->assertEquals( '', $poi['openingtimes'] );
      $this->assertEquals( '', $poi['public_transport_links'] );
      $this->assertEquals( '<p>With three giant Swarovski peonies winking at you as soon', substr( $poi['description'], 0, 60 ) );

      $this->assertEquals( '39.909988', $poi['latitude'] );
      $this->assertEquals( '116.452393', $poi['longitude'] );

      // check category
      $this->assertGreaterThan( 0 , $poi['VendorPoiCategory']->count() );
      $this->assertEquals( 'Restaurents | Chinese', $poi['VendorPoiCategory'][0]['name'] );

      // Check 2nd Record
      $poi = $pois[1];
      $this->assertEquals( 'Meli Melo', $poi['name'] );
      $this->assertEquals( 'The Centre' , $poi['additional_address_details'] ); // Building name & Neighbourhood->name merged together
      $this->assertEquals( 'Second Floor, Les Millésimes, 16 Yonganli (next to Building 15 Jianwai SOHO), Chaoyang district', $poi['provider'] );
      $this->assertEquals( 'beijing', $poi['Vendor']['city'] );
      $this->assertEquals( '', $poi['zips'] );
      $this->assertEquals( $poi['Vendor']['country_code_long'], $poi['country'] );
      $this->assertEquals( '', $poi['email'] );
      $this->assertEquals( '', $poi['url'] );
      $this->assertEquals( '+86 8 521 9988', $poi['phone'] );

      $this->assertEquals( '11am-2.30pm, 6-10.30pm daily', $poi['openingtimes'] );
      $this->assertEquals( '', $poi['public_transport_links'] );

      $statusMetaFound =false;

      foreach ($poi['PoiMeta'] as $meta)
      {
        if( $meta[ 'lookup'] == 'status' )
        {
            $statusMetaFound = true;
            $this->assertEquals( '10', $meta['value'] );
        }
      }
      $this->assertTrue( $statusMetaFound ,'status meta not found' );

      $this->assertEquals( '<p>This French fusion restaurant tends towards the whimsical', substr( $poi['description'], 0, 60 ) );

      $this->assertNull( $poi['latitude'] );
      $this->assertNull( $poi['longitude'] );

      // check category
      $this->assertGreaterThan( 0 , $poi['VendorPoiCategory']->count() );
      $this->assertEquals( 'Restaurents', $poi['VendorPoiCategory'][0]['name'] );

      // Image Fails Refs : #516 Mime Type
      // $this->assertGreaterThan( 0 , $poi['PoiMedia']->count() );

  }

  public function testNonLiveVenuesAreNotImported()
  {
      $importer = new Importer();
      $importer->addDataMapper( $this->dataMapper );
      $importer->run();

      $vendorPoiIdOfNonLiveVenueInDB = 4;
      $nonLiveVenueIsImported = false;

      $pois = Doctrine::getTable('Poi')->findAll();
      foreach ($pois as $poi)
      {
        if( $poi[ 'vendor_poi_id' ] == 4 )
        {
            $nonLiveVenueIsImported = true;
        }

      }
      $this->assertFalse($nonLiveVenueIsImported , 'non-live venues (if the status != 10) shouldnt be imported'  );

  }
  /**
   * Create Dummy SqLite3 Database in memoty and add Dummy Data
   * @return Boolean
   */
  private function createDummyDB()
  {
      try {
          $pdoDB = new PDO('sqlite::memory:');

          $pdoDB->beginTransaction();

          $pdoDB->query('DROP TABLE IF EXISTS neighbourhood; DROP TABLE IF EXISTS category; DROP TABLE IF EXISTS venue; DROP TABLE IF EXISTS venue_category_mapping;');


          // Create Tables
          $pdoDB->exec('CREATE TABLE neighbourhood(id INTEGER PRIMARY KEY, city_id INTEGER, name VARCHAR(100))');
          $pdoDB->exec('CREATE TABLE category(id INTEGER PRIMARY KEY, parent_category_id INTEGER, lft INTEGER, rgt INTEGER, name VARCHAR(100));');
          $pdoDB->exec('CREATE TABLE venue(id INTEGER PRIMARY KEY, neighbourhood_id INTEGER, name VARCHAR(255), alt_name VARCHAR(255), building_name VARCHAR(200), address VARCHAR(255), postcode VARCHAR(255),travel VARCHAR(255),opening_times VARCHAR(255),url VARCHAR(255),latitude VARCHAR(255), longitude VARCHAR(255), phone VARCHAR(255),email VARCHAR(255),image_id VARCHAR(255),status VARCHAR(255),source_id VARCHAR(255),annotation TEXT);');
          $pdoDB->exec('CREATE TABLE venue_category_mapping(venue_id INTEGER, category_id INTEGER);');

          $pdoDB->exec('INSERT INTO neighbourhood VALUES(1, 2, "The Centre");');
          $pdoDB->exec('INSERT INTO neighbourhood VALUES(2, 2, "Chaoyang");');
          $pdoDB->exec('INSERT INTO neighbourhood VALUES(3, 1, "None Beijing City");');
          $pdoDB->exec('INSERT INTO neighbourhood VALUES(4, 4, "None Beijing City 2");');

          $pdoDB->exec('INSERT INTO category VALUES(1, 0, 0, 1132, "Root" );');
          $pdoDB->exec('INSERT INTO category(id, parent_category_id, lft, rgt, name) VALUES(381, 1, 2, 233, "International" );');
          $pdoDB->exec('INSERT INTO category(id, parent_category_id, lft, rgt, name) VALUES(382, 381, 3, 232, "cn" );');
          $pdoDB->exec('INSERT INTO category(id, parent_category_id, lft, rgt, name) VALUES(383, 382, 4, 231, "en" );');
          $pdoDB->exec('INSERT INTO category(id, parent_category_id, lft, rgt, name) VALUES(384, 383, 5, 230, "Beijing" );');
          $pdoDB->exec('INSERT INTO category(id, parent_category_id, lft, rgt, name) VALUES(392, 384, 58, 229, "Venues" );');
          $pdoDB->exec('INSERT INTO category(id, parent_category_id, lft, rgt, name) VALUES(404, 392, 83, 178, "Restaurents" );');
          $pdoDB->exec('INSERT INTO category(id, parent_category_id, lft, rgt, name) VALUES(406, 404, 86, 127, "Chinese" );');
          $pdoDB->exec('INSERT INTO category(id, parent_category_id, lft, rgt, name) VALUES(561, 406, 123, 124, "Yunnan & SouthWest Minority" );');

          $pdoDB->exec('INSERT INTO venue(id, neighbourhood_id, status, name, building_name, address, postcode, travel, opening_times, url, latitude, longitude, phone, email, image_id, annotation) VALUES(1, 2, 10, "Sofitel Wanda", "Wanda Plaza", "93 Jianguo Lu, Tower C Wanda Plaza, Chaoyang district", "", "", "", "http://www.theemperor.com.cn", "39.909988", "116.452393", "8599 6666", "", "", "<p>With three giant Swarovski peonies winking at you as soon as you enter and a daring mix of French chic and Tang dynasty chinoiserie characterising the decor throughout, this hotel is gorgeous. A fantastic French restaurant &ndash; Le Pre Lenotre &ndash; and a relaxing Le Spa add to the appeal.</p>");');
          $pdoDB->exec('INSERT INTO venue(id, neighbourhood_id, status, name, building_name, address, postcode, travel, opening_times, url, latitude, longitude, phone, email, image_id, annotation) VALUES(2, 1, 10, "Meli Melo", "", "Second Floor, Les Millésimes, 16 Yonganli (next to Building 15 Jianwai SOHO), Chaoyang district", "", "", "11am-2.30pm, 6-10.30pm daily", "", "0.000000", "0.000000", "8521 9988", "", "44494", "<p>This French fusion restaurant tends towards the whimsical in its eclectic d&eacute;cor of peach-coloured sofas, gilded screens and red chandelier covers made of an Issey Miyake inspired fabric. Yet when it comes to food, Meli Melo gets serious. Meli Melo belongs to the three-storey Les Mill&eacute;simes development, which includes a wine bar, cigar bar, private club and the French seafood restaurant La Maree, located on the same floor as Meli Melo. The two restaurants share a kitchen and a wine list. That the wines are currently available only by the bottle is unfortunate since it is nearly impossible to match one wine with the many flavours that Meli Melo combines on one plate. Staff are polite and professional yet unlike many new restaurants around town, Meli Melo does not add an extra service charge.</p>");');
          $pdoDB->exec('INSERT INTO venue(id, neighbourhood_id, status, name, building_name, address, postcode, travel, opening_times, url, latitude, longitude, phone, email, image_id, annotation) VALUES(3, 4, 10, "None Beijin", "", "Invaid Address", "", "", "11am-2.30pm, 6-10.30pm daily", "", "0.000000", "0.000000", "8521 9988", "", "44494", "<p>This shoudn\'t be in. Beijing???</p>");');
          $pdoDB->exec('INSERT INTO venue(id, neighbourhood_id, status, name, building_name, address, postcode, travel, opening_times, url, latitude, longitude, phone, email, image_id, annotation) VALUES(4, 1, 50, "not-live-venue", "", "Invaid Address", "", "", "11am-2.30pm, 6-10.30pm daily", "", "0.000001", "0.000002", "85221 99188", "", "442494", "<p>This ssadadhoudn\'t be in. Beijing???</p>");');

          $pdoDB->exec('INSERT INTO venue_category_mapping(venue_id, category_id) VALUES(1, 406);');
          $pdoDB->exec('INSERT INTO venue_category_mapping(venue_id, category_id) VALUES(2, 404);');
          $pdoDB->exec('INSERT INTO venue_category_mapping(venue_id, category_id) VALUES(3, 561);');
          $pdoDB->exec('INSERT INTO venue_category_mapping(venue_id, category_id) VALUES(4, 561);');

          $pdoDB->commit();

          // Set Connection
          $this->pdoDB = $pdoDB;

        }
        catch(PDOException $e)
        {
            echo 'PDO Connection Exception: ' . $e->getMessage() . PHP_EOL;
        }
        return false;
  }

}
?>
