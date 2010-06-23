<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test of Barcelona Events Mapper import.
 *
 * @package test
 * @subpackage russia.import.lib.unit
 *
 * @author Peter Johnson <peterjohnson@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class barcelonaEventsMapperTest extends PHPUnit_Framework_TestCase
{
  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    Doctrine::loadData('data/fixtures');

    $vendor = Doctrine::getTable('Vendor')->findOneByCityAndLanguage( "barcelona", "ca" );
    $vendor->save();

    $this->vendor = $vendor;

    $this->object = new barcelonaEventsMapper( simplexml_load_file( TO_TEST_DATA_PATH . '/barcelona/events_trimmed.xml' ) );
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testMapEvents()
  {
    // Create A Venue for First Event
    $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
    $poi[ 'vendor_poi_id' ] = 1;
    $poi[ 'Vendor' ] = $this->vendor;
    $poi->save();

    $importer = new Importer();
    $importer->addDataMapper( $this->object );
    $importer->run();

    $events = Doctrine::getTable( 'Event' )->findAll();
    $this->assertEquals( 1, $events->count() );

    $event = $events[0];

    $this->assertEquals( 4548,  $event['vendor_event_id'] );
    $this->assertEquals( 'Museu de la xocolata', $event['name'] );
    $this->assertEquals( 'http://www.pastisseria.com/es/PortadaMuseu', $event['url'] );
    $this->assertEquals( '3,80 €', $event['price'] );

    $this->assertEquals( $this->vendor, $event['Vendor'] );

    $this->assertGreaterThan( 0, count( $event['EventProperty'] ) );

    $this->assertEquals( 'Timeout_link', $event['EventProperty'][0]['lookup'] );
    $this->assertEquals( 'http://www.timeout.cat/barcelona/ca/s/viu-barcelona', $event['EventProperty'][0]['value'] );

    $this->assertGreaterThan( 1, $event[ 'VendorEventCategory' ]->count() );
    $this->assertEquals( "A la Ciutat", $event[ 'VendorEventCategory' ]['A la Ciutat']['name'] );
    $this->assertEquals( "Artística | Exposicions", $event[ 'VendorEventCategory' ]['Artística | Exposicions']['name'] );

    $this->assertGreaterThan( 0, $event[ 'EventOccurrence' ]->count() );
  }
}
?>
