<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test class for IstanbulEventMapper.
 >*
 * @package test
 * @subpackage instabul.import.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 *
 * @version 1.0.1
 */
class IstanbulEventMapperTest extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    ProjectN_Test_Unit_Factory::add( 'Vendor', array(
      'city' => 'istanbul',
      'inernational_dial_code' => '+90',
      'language' => 'tr',
      'country_code' => 'tr',
      'country_code_long' => 'TUR',
      'time_zone' => 'Asia/Istanbul',
    ) );
  }

  public function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testMap()
  {
    $this->createPoisRequiredForEventImport();
    $importer = new Importer();
    $xml = simplexml_load_file( TO_TEST_DATA_PATH . '/istanbul/events.xml' );
    $importer->addDataMapper( new istanbulEventMapper( $xml ) );
    $importer->run();

    $this->assertEquals( 2, Doctrine::getTable( 'event' )->count() );
    $firstEvent = Doctrine::getTable( 'event' )->findOneById( 1 );

    $this->assertEquals( '1',           $firstEvent[ 'vendor_event_id' ] );
    $this->assertEquals( 'Grace Jones', $firstEvent[ 'name' ] );
    $this->assertEquals( 'Muazzam sesi ve sahne performansıyla tanınan, disco’dan rock’a, 70’lerden günümüze uzanan efsane bir kadın Grace Jones. Bu ilk İstanbul konseri, kaçmasın. Biletler önceden alınsın, kara borsaya gerek kalmasın.',
                                        $firstEvent[ 'short_description' ] );
    $this->assertEquals( 'http://www.timeoutistanbul.com/e8672/muzik/grace_jones',
                                        $firstEvent->getTimeoutLinkProperty() );
    $this->assertEquals( 'sahne önü: 134 TL, 1. kategori: 102 TL, 2. kategori: 90 TL, 3. kategori: 78,50 TL, 4. kategori: 67,50 TL, öğrenci: 45 TL',
                                        $firstEvent[ 'price' ] );
    $this->assertEquals( 'http://www.gracejones.org/',
                                        $firstEvent[ 'url' ] );
    $this->assertEquals( 'Alternatif',
                                        $firstEvent[ 'VendorEventCategory' ][0]['name'] );
    $this->assertEquals( 'http://www.timeoutistanbul.com/images/uploadedimages/standart/10122.jpg',
                                        $firstEvent[ 'EventMedia' ][0]['url'] );


    $this->assertEquals( 2,count( $firstEvent[ 'EventOccurrence' ] ));
    $occurrence  = $firstEvent[ 'EventOccurrence' ][0];
    $this->assertEquals( '01_01_1', $occurrence['vendor_event_occurrence_id']);
    $this->assertEquals( 'http://www.timeout.com', $occurrence['booking_url']);
    $this->assertEquals( '2010-09-03', $occurrence['start_date']);
    $this->assertEquals( '3588', $occurrence['Poi'][ 'vendor_poi_id']);

    //test the second occurrence
    $occurrence  = $firstEvent[ 'EventOccurrence' ][1];
    $this->assertEquals( '01_01_2', $occurrence['vendor_event_occurrence_id']);
    $this->assertEquals( 'http://www.facebook.com', $occurrence['booking_url']);
    $this->assertEquals( '2010-09-04', $occurrence['start_date']);
    $this->assertEquals( '3589', $occurrence['Poi'][ 'vendor_poi_id']);
    $this->assertEquals( '+03:00', $occurrence['utc_offset']);

  }

  private function createPoisRequiredForEventImport()
  {
    $vendorPoiIds = array( 3588 ,3589 ,3766);
    foreach ($vendorPoiIds as $vendorPoiId)
    {
        $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
        $poi[ 'vendor_poi_id' ] = $vendorPoiId;
        $poi[ 'vendor_id' ] = 1;
        $poi->save();
    }
  }
}
