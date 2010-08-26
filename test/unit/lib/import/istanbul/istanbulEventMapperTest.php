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
    ) );
  }

  public function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testMap()
  {
    $importer = new Importer();
    $xml = simplexml_load_file( TO_TEST_DATA_PATH . '/istanbul/events.xml' );
    $importer->addDataMapper( new istanbulEventMapper( $xml ) );
    $importer->run();

    $this->assertEquals( $xml->count(), Doctrine::getTable( 'event' )->count() );
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
    
    //test occurrences
    //awaiting istanbul reply
  }
}
