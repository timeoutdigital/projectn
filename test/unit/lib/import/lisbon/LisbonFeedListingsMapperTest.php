<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test class for LisbonFeedListingsMapper.
 *
 *
 * @package test
 * @subpackage lisbon.import.lib.unit
 *
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 *
 * @version 1.0.1
 */
class LisbonFeedListingsMapperTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var LisbonFeedListingsMapper
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
    ProjectN_Test_Unit_Factory::createDatabases();

    $vendor = ProjectN_Test_Unit_Factory::get( 'Vendor', array(
      'city' => 'Lisbon',
      'language' => 'pt',
      'time_zone' => 'Europe/Lisbon',
      )
    );
    $vendor->save();
    $this->vendor = $vendor;

    foreach( array( 833, 2844/*, 4109*/ ) as $placeid )
    {
      ProjectN_Test_Unit_Factory::add( 'Poi', array( 'vendor_poi_id' => $placeid ) );
    }

    $this->object = new LisbonFeedListingsMapper(
      simplexml_load_file( TO_TEST_DATA_PATH . '/lisbon_listings.short.xml' )
    );
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
   * @todo Implement testMapVenues().
   */
  public function testMapListings()
  {
    $importer = new Importer();
    $importer->addDataMapper( $this->object );
    $importer->run();

    $events = Doctrine::getTable( 'Event' )->findAll();
    $this->assertEquals( 3, $events->count() );

    $event = $events[0];

    $this->assertEquals( '50805', $event['vendor_event_id'] );
    $this->assertEquals( 'Arquitecto José Santa-Rita, arquitecto: Obra, marcas e identidade(s) de um percu', $event['name'] );
    $this->assertEquals( 'Constituindo, pela primeira vez, uma homenagem póstuma, esta edição do Prémio Mu', $event['short_description'] );
    $this->assertRegExp( '/^Constituindo,.*Exposições.$/', $event['description'] );
    $this->assertEquals( '', $event['booking_url'] );
    $this->assertEquals( '', $event['url'] );
    $this->assertEquals( '', $event['price'] );
    $this->assertEquals( '', $event['rating'] );
    $this->assertEquals( '1', $event['vendor_id'] );

    $eventOccurrence1 = $event['EventOccurrence'][0];
    $this->assertEquals( '2010-01-01 00:00:00', $eventOccurrence1['start'] );
    $this->assertEquals( '+00:00', $eventOccurrence1['utc_offset'] );

    $eventOccurrence2 = $event['EventOccurrence'][1];
    $this->assertEquals( '2010-07-07 00:00:00', $eventOccurrence2['start'] );
    $this->assertEquals( '+01:00', $eventOccurrence2['utc_offset'] );

    $eventOccurrences = Doctrine::getTable( 'EventOccurrence' )->findAll();
    $this->assertEquals( 6, $eventOccurrences->count() );
    
    $event = Doctrine::getTable( 'Event' )->findOneByVendorEventId( 50797 );
     
    $this->assertEquals( 2 ,count( $event['EventOccurrence'] )  );
  }
}
?>
