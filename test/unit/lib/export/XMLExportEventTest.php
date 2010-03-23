<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ).'/../../bootstrap.php';


/**
 * Test class XML Events exports
 *
 * @package test
 * @subpackage export.lib.unit
 *
 * @author Timmy Bowler <timbowler@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class XMLExportEventTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var XMLExportEvent
   */
  protected $export;

  /**
   * @var Vendor
   */
  protected $vendor;

  /**
   * @var DOMDocument
   */
  protected $domDocument;



  /**
   *
   * @var DOMXPath
   */
  protected $xpath;

  protected $specialChars = '&<>\'"';

  protected $escapedSpecialChars;



  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    $this->createVendor();

    $poiCat = new PoiCategory();
    $poiCat->setName( 'eat-drink' );
    $poiCat->save();

    $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
    $poi->link( 'Vendor', array( 1 ) );
    $poi->link( 'PoiCategory', array( 1 ) );
    $poi->save();

    $poi2 = ProjectN_Test_Unit_Factory::get( 'Poi' );
    $poi2->link( 'Vendor', array( 1 ) );
    $poi2->link( 'PoiCategory', array( 1 ) );
    $poi2->save();

    $vendorEventCategory = new VendorEventCategory();
    $vendorEventCategory['name'] = 'test vendor category';
    $vendorEventCategory['Vendor'] = $this->vendor;
    $vendorEventCategory->save();

    $vendorEventCategories = new Doctrine_Collection( Doctrine::getTable('VendorEventCategory'));
    $vendorEventCategories[] = $vendorEventCategory;

    $eventCategories = new Doctrine_Collection(Doctrine::getTable('EventCategory'));

    $eventCat1 = ProjectN_Test_Unit_Factory::get( 'EventCategory', array( 'name' => 'concerts' ) );
    $eventCategories[] = $eventCat1;

    $eventCat2 = ProjectN_Test_Unit_Factory::get( 'EventCategory', array( 'name' => 'theater' ) );
    $eventCategories[] = $eventCat2;

    $eventCat3 = ProjectN_Test_Unit_Factory::get( 'EventCategory', array( 'name' => 'sport' ) );
    $eventCategories[] = $eventCat3;

    $event = ProjectN_Test_Unit_Factory::get( 'Event' );
    $event['VendorEventCategory'] = $vendorEventCategories;
    $event['EventCategory'] = $eventCategories;
    $event->link( 'Vendor', array( 1 ) );
    $event->save();

    $occurrence = ProjectN_Test_Unit_Factory::get( 'EventOccurrence', array( 
      'start_date' => $this->today(),
      'start_time' => '00:00:01',
    ) );
    $occurrence->link( 'Event', array( 1 ) );
    $occurrence->link( 'Poi', array( 1 ) );
    $occurrence->save();

    $property = ProjectN_Test_Unit_Factory::get( 'EventProperty', array( 
      'lookup' => 'test key 1',
      'value'  => 'test value 1',
      ) );
    $property->link( 'Event', array( 1 ) );
    $property->save();

    $property2 = ProjectN_Test_Unit_Factory::get( 'EventProperty', array( 
      'lookup' => 'test key 2',
      'value'  => 'test value 2',
      ) );
    $property2->link( 'Event', array( 1 ) );
    $property2->save();

    $property = new EventMedia();
    $property[ 'ident' ] = 'md5 hash of the url';
    $property[ 'mime_type' ] = 'image/';
    $property[ 'url' ] = 'url';
    $property->link( 'Event', array( $event['id'] ) );
    $property->save();

    $event2 = new Event();
    $event2['VendorEventCategory'] = $vendorEventCategories;
    $event2['EventCategory'][] = $eventCat1;
    $event2['vendor_event_id'] = 1112;
    $event2->setName( 'test event2' . $this->specialChars );
    $event2->link( 'Vendor', array( 1 ) );
    $event2->save();

    $occurrence2 = ProjectN_Test_Unit_Factory::get( 'EventOccurrence', array( 'start_date' => $this->today() ) );
    $occurrence2['vendor_event_occurrence_id'] = 1110;
    $occurrence2->link( 'Event', array( 2 ) );
    $occurrence2->link( 'Poi', array( 1 ) );
    $occurrence2->save();

    $occurrence3 = ProjectN_Test_Unit_Factory::get( 'EventOccurrence', array( 'start_date' => $this->today() ) );
    $occurrence3['vendor_event_occurrence_id'] = 1111;
    $occurrence3->link( 'Event', array( 2 ) );
    $occurrence3->link( 'Poi', array( 2 ) );
    $occurrence3->save();

    $occurrence4 = ProjectN_Test_Unit_Factory::get( 'EventOccurrence', array( 'start_date' => $this->today() ) );
    $occurrence4['vendor_event_occurrence_id'] = 1111;
    $occurrence4->link( 'Event', array( 2 ) );
    $occurrence4->link( 'Poi', array( 2 ) );
    $occurrence4->save();

    $this->runExport();

    $this->escapedSpecialChars = htmlspecialchars( $this->specialChars );
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
    unlink( $this->destination );
  }

  /**
   * test generateXML() has vendor-events root tag with required attributes
   *
   * @todo Check if this is still needed
   */
//  public function testGeneratedXMLHasEventWithRequiredAttribute()
//  {
//    $this->assertTrue( $this->domDocument instanceof DOMDocument );
//
//    //vendor-event
//    $rootElement = $this->domDocument->firstChild;
//    $this->assertEquals( $this->vendor->getName(), $rootElement->getAttribute('vendor') );
//    $this->assertRegExp( '/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}/', $rootElement->getAttribute('modified') );
//  }
//
//  /**
//   * Each event should have a name
//   */
//  public function testGeneratedXMLEventTags()
//  {
//    $eventElement = $this->domDocument->firstChild->firstChild;
//    $this->domDocument->formatOutput = true;
//    var_dump( get_class( $eventElement ) );
//    $this->assertTrue( $eventElement instanceof DOMElement );
//    $this->assertEquals( 'LHR000000000000000000000000000001', $eventElement->getAttribute('id') );
//    //$this->assertRegExp( '/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}/', $eventElement->getAttribute('modified') );
//
//    $this->assertEquals('test name', $eventElement->getElementsByTagName('name')->item(0)->nodeValue );
//  }

  /**
   * test geneate if xml has at least one event with its required children
   */
  public function testGeneratedXMLCategory()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
    ProjectN_Test_Unit_Factory::createDatabases();

    $this->createVendor();
    $this->addAnEventWithEventCategories( 'concerts', 'theater', 'sport' );
    $this->addAnEventWithEventCategories( 'family' );
    $this->runExport();

    $categoryElements1 = $this->xpath->query( '/vendor-events/event[1]/category' );

    $this->assertEquals( 3, $categoryElements1->length );

    $this->assertEquals( 'concerts', $categoryElements1->item(0)->nodeValue );
    $this->assertEquals( 'theater',  $categoryElements1->item(1)->nodeValue );
    $this->assertEquals( 'sport',    $categoryElements1->item(2)->nodeValue );

    $categoryElements2 = $this->xpath->query( '/vendor-events/event[2]/category' );
    $this->assertEquals( 'family', $categoryElements2->item(0)->nodeValue );
  }

  /**
   * Each event should have atleast one version tag
   */
  public function testGeneratedXMLEventVersionTag()
  {
    $langAttributes = $this->xpath->query( '/vendor-events/event[1]/version' );
    $this->assertEquals(1, $langAttributes->length);
    $this->assertEquals( 'en', $langAttributes->item(0)->getAttribute( 'lang' ) );
  }

  /**
   * Check the version tag's children
   */
  public function testGeneratedXMLEventVersionChildrenTags()
  {
    $versionTag = $this->xpath->query( '/vendor-events/event[1]/version' )->item(0);

    $vendorCategoryElements = $versionTag->getElementsByTagName( 'vendor-category' );
    $this->assertEquals( 1, $vendorCategoryElements->length );
    $this->assertEquals( 'test vendor category', $vendorCategoryElements->item(0)->nodeValue );

    $shortDescriptions = $versionTag->getElementsByTagName( 'short-description' );
    $this->assertEquals(1, $shortDescriptions->length );
    $this->assertEquals( 'test short description', $shortDescriptions->item(0)->nodeValue );

    $descriptions = $versionTag->getElementsByTagName( 'description' );
    $this->assertEquals(1, $descriptions->length );
    $this->assertEquals( 'test description', $descriptions->item(0)->nodeValue );

    $bookingUrl = $versionTag->getElementsByTagName( 'booking_url' );
    $this->assertEquals(1, $bookingUrl->length );
    $this->assertEquals( 'http://timeout.com', $bookingUrl->item(0)->nodeValue );

    $url = $versionTag->getElementsByTagName( 'url' );
    $this->assertEquals(1, $url->length );
    $this->assertEquals( 'http://timeout.com', $url->item(0)->nodeValue );

    $price = $versionTag->getElementsByTagName( 'price' );
    $this->assertEquals(1, $price->length );
    $this->assertEquals( 'test price', $price->item(0)->nodeValue );
  }

  /**
   * Check that the event has a POI linked to it
   *
   */
  public function testPoiExistsBeforeEventExport()
  {
      ProjectN_Test_Unit_Factory::destroyDatabases();
      ProjectN_Test_Unit_Factory::createDatabases();

      unlink( $this->destination );

      $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor', array( 'airport_code' => 'LHR' ) );


      for( $i=0; $i<3; $i++)
      {
        if( $i == 1 ) continue;
        $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
        $poi['Vendor'] = $vendor;
        $poi->save();
      }

      for( $i=0; $i<3; $i++ )
      {
        $event = ProjectN_Test_Unit_Factory::get( 'Event' );
        $event[ 'Vendor' ] = $vendor;
        $occurrence = ProjectN_Test_Unit_Factory::get( 'EventOccurrence', array( 'start_date' => date( 'Y-m-d' ) ) );
        $occurrence['poi_id'] = $i+1;
        $event['EventOccurrence'][] = $occurrence;
        $event->save();
      }

      $event2 = Doctrine::getTable( 'Event' )->findOneById( 2 );
      $this->assertEquals( 2, $event2['EventOccurrence'][0]['poi_id'], 'Event occurrence id should match poi id.' );
      $this->assertEquals( 'LHR', $event2['Vendor']['airport_code'], 'airport code' );

      $this->assertEquals( 2, count( $vendor['Poi'] ), 'Testing the total venues' );

      $poiExport = new XMLExportPOI( $vendor, $this->poiXmlLocation );
      $poiExport->run();

      $this->export();

      $this->assertEquals(2, $this->xpath->query('//event')->length, 'Testing the total true events' );
  }

  public function testThrowsErrorIfPoiXmlNotFound()
  {
      //$this->setExpectedException(Exception);
      //$export = new XMLExportEvent($this->vendor, $this->destination, 'not_a_real_file');
  }

  /**
   * Check showtimes children
   */
  public function testGeneratedXMLEventShowtimesDirectChildrenTags()
  {
    $showtimes = $this->xpath->query( '/vendor-events/event[1]/showtimes' );
    $this->assertEquals( 1, $showtimes->length );

    $showtimes1 = $showtimes->item(0);
    $placeTags  = $showtimes1->getElementsByTagName( 'place' );

    $this->assertEquals( 'LHR000000000000000000000000000001', $placeTags->item(0)->getAttribute( 'place-id' ) );
    $this->assertEquals( 'http://timeout.com', $showtimes1->getElementsByTagName( 'booking_url' )->item(0)->nodeValue);
    $this->assertEquals( $this->today(), $showtimes1->getElementsByTagName( 'start_date' )->item(0)->nodeValue, 'Testing the Start time' );
    $this->assertEquals( '00:00:01', $showtimes1->getElementsByTagName( 'event_time' )->item(0)->nodeValue, 'Testing for a start time present' );
    $this->assertEquals( '+00:00:00', $showtimes1->getElementsByTagName( 'utc_offset' )->item(0)->nodeValue );

    $placesForEvent2 = $this->xpath->query( '/vendor-events/event[2]/showtimes/place' );
    $this->assertEquals( 2, $placesForEvent2->length );

    $this->assertEquals( 'LHR000000000000000000000000000002', $placesForEvent2->item(1)->getAttribute( 'place-id' ) );
  }

  /**
   * check properties tags
   */
  public function testPropertyTags()
  {
    $this->markTestSkipped();
    $propertyElements1 = $this->xpath->query( '/vendor-events/event[1]/version/property' );
    $this->assertEquals(2, $propertyElements1->length);

    $this->assertEquals( 'test key 1',   $propertyElements1->item(0)->getAttribute( 'key' ) );
    $this->assertEquals( 'test value 1', $propertyElements1->item(0)->nodeValue );
    $this->assertEquals( 'test key 2',   $propertyElements1->item(1)->getAttribute( 'key' ) );
    $this->assertEquals( 'test value 2', $propertyElements1->item(1)->nodeValue );
  }

  /**
   * check xml against customer's schema
   */
  public function _testAgainstSchema()
  {
    $this->assertTrue( $this->domDocument->schemaValidate( TO_PROJECT_ROOT_PATH . '/data/xml_schemas/' . 'events.xsd' ) );
  }

  /**
   *
   */
  public function testCorrectNumberPlaceAndOccurrenceTags()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
    ProjectN_Test_Unit_Factory::createDatabases();

    $event = $this->createEventStartingToday();
    $event->save();

    $event = $this->createEventStartingToday();
    $poi = ProjectN_Test_Unit_Factory::add( 'Poi' );
    $this->addAnotherEventOccurrenceTo( $event, $poi );
    $this->addAnotherEventOccurrenceTo( $event, $poi );
    $event->save();

    $this->runExport();
    var_dump( $this->domDocument->saveXml() );
    
    $placesForEvent2 = $this->xpath->query( '/vendor-events/event[2]/showtimes/place' );

    $this->assertEquals( 2, $placesForEvent2->length );
    $this->assertEquals( 1, $placesForEvent2->item(0)->getElementsByTagName( 'occurrence' )->length );
    $this->assertEquals( 2, $placesForEvent2->item(1)->getElementsByTagName( 'occurrence' )->length );
  }

    /**
     * check properties tags
     */
    public function testMediaTags()
    {
      $this->markTestSkipped();
      $propertyElements = $this->xpath->query( '/vendor-events/event[1]/version/media' );
      $this->assertEquals( 'image/', $propertyElements->item(0)->getAttribute('mime-type') );
      $this->assertEquals( 'url',    $propertyElements->item(0)->nodeValue );
    }

    /**
     * get today's date
     */
    private function today()
    {
      return date( 'Y-m-d' );
    }

    private function export()
    {
      $this->export = new XMLExportEvent( $this->vendor, $this->destination, $this->poiXmlLocation );
      $this->export->run();
      $this->domDocument = new DOMDocument();
      $this->domDocument->load( $this->destination );
      $this->xpath = new DOMXPath( $this->domDocument );
    }

    private function createVendor()
    {
      $this->vendor = ProjectN_Test_Unit_Factory::add( 'Vendor', array( 
        'city'         => 'test',
        'language'     => 'en-GB',
        'airport_code' => 'LHR',
        ) );
    }

    private function runExport()
    {
      $this->destination    = dirname( __FILE__ ) . '/../../export/event/test.xml';
      $this->poiXmlLocation = TO_TEST_DATA_PATH . '/poi.xml';

      $poiExport = new XMLExportPOI( $this->vendor, $this->poiXmlLocation );
      $poiExport->run();
     
      $this->export();
    }

    private function addAnEventWithEventCategories()
    {
      $event = ProjectN_Test_Unit_Factory::get( 'Event' );
      $event[ 'Vendor' ] = $this->vendor;
      $event[ 'EventOccurrence' ][] = ProjectN_Test_Unit_Factory::get( 'EventOccurrence', array( 
        'start_date' => ProjectN_Test_Unit_Factory::today() 
      ) );

      foreach( func_get_args() as $category )
      {
        $event->addVendorCategory( $category );
        $event->save();
        $this->saveEventCategoryMapping( $category );
      }
      $event->save();
    }

    private function saveEventCategoryMapping( $category )
    {
      $eventCategory = ProjectN_Test_Unit_Factory::get( 'EventCategory', array( 'name' => $category ) );

      $vendorEventCategory = Doctrine::getTable( 'VendorEventCategory' )
        ->findOneByName( $category );

      $eventCategory = ProjectN_Test_Unit_Factory::get( 'EventCategory', array( 'name' => $category ) );
      $eventCategory[ 'VendorEventCategory' ][] = $vendorEventCategory;
      $eventCategory->save();
    }

    private function createEventStartingToday()
    {
      $event = ProjectN_Test_Unit_Factory::get( 'Event' );
      $event[ 'Vendor' ] = $this->vendor;

      $eventOccurrence = ProjectN_Test_Unit_Factory::get( 'EventOccurrence' );
      $eventOccurrence[ 'start_date' ] = ProjectN_Test_Unit_Factory::today();
      $event[ 'EventOccurrence' ][] = $eventOccurrence;

      return $event;
    }

    private function addAnotherEventOccurrenceTo( $event, $poi )
    {
      $year  = '2020';
      $month = str_pad( rand( 1, 12 ), 2, '0', STR_PAD_LEFT );
      $day   = str_pad( rand( 1, 28 ), 2, '0', STR_PAD_LEFT );
      $start_date = implode( '-', array( $year, $month, $day ) );
      $eventOccurrence = ProjectN_Test_Unit_Factory::get( 'EventOccurrence', array( 'start_date' => $start_date ) );
      $eventOccurrence[ 'Poi' ] = $poi;
      $event[ 'EventOccurrence' ][] = $eventOccurrence;
    }
}
?>
