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

    $vendor = new Vendor();
    $vendor['city'] = 'test';
    $vendor['language'] = 'en-GB';
    $vendor['time_zone'] = 'Europe/London';
    $vendor['inernational_dial_code'] = '+44';
    $vendor->save();
    $this->vendor = $vendor;

    $poiCat = new PoiCategory();
    $poiCat->setName( 'test' );
    $poiCat->save();

    $poi = new Poi();
    $poi->setPoiName( 'test name' );
    $poi->setStreet( 'test street' );
    $poi->setHouseNo('12' );
    $poi->setZips('1234' );
    $poi->setCity( 'test town' );
    $poi->setDistrict( 'test district' );
    $poi->setCountry( 'GBR' );
    $poi->setVendorPoiId( '123' );
    $poi->setLocalLanguage('en');
    $poi->setLongitude( '0.1' );
    $poi->setLatitude( '0.2' );
    $poi->link( 'Vendor', array( 1 ) );
    $poi->link('PoiCategories', array( 1 ) );
    $poi->save();

    $poi2 = new Poi();
    $poi2->setPoiName( 'test name2' );
    $poi2->setStreet( 'test street' );
    $poi2->setHouseNo('12' );
    $poi2->setZips('1234' );
    $poi2->setCity( 'test town' );
    $poi2->setDistrict( 'test district' );
    $poi2->setCountry( 'GBR' );
    $poi2->setVendorPoiId( '123' );
    $poi2->setLocalLanguage('en');
    $poi2->setLongitude( '0.1' );
    $poi2->setLatitude( '0.2' );
    $poi2->link( 'Vendor', array( 1 ) );
    $poi2->link('PoiCategories', array( 1 ) );
    $poi2->save();

    $vendorEventCategory = new VendorEventCategory();
    $vendorEventCategory['name'] = 'test vendor category';
    $vendorEventCategory['Vendor'] = $vendor;
    $vendorEventCategory->save();

    $vendorEventCategories = new Doctrine_Collection( Doctrine::getTable('VendorEventCategory'));
    $vendorEventCategories[] = $vendorEventCategory;

    $eventCategories = new Doctrine_Collection(Doctrine::getTable('EventCategory'));

    $eventCat = new EventCategory();
    $eventCat->setName( 'concerts' );
    $eventCat->save();
    $eventCategories[] = $eventCat;

    $eventCat = new EventCategory();
    $eventCat->setName( 'theater' );
    $eventCat->save();
    $eventCategories[] = $eventCat;

    $eventCat = new EventCategory();
    $eventCat->setName( 'sport' );
    $eventCat->save();
    $eventCategories[] = $eventCat;

    $event = new Event();
    $event->setName( 'test event' );
    $event['VendorEventCategories'] = $vendorEventCategories;
    $event['EventCategories'] = $eventCategories;
    $event['vendor_event_id'] = 1111;
    $event->setShortDescription( 'test vendor short description' );
    $event->setDescription( 'test vendor description' );
    $event->setBookingUrl( 'http://timeout.com' );
    $event->setUrl( 'http://timeout.com' );
    $event->setPrice( 'test price' );
    $event->link( 'Vendor', array( 1 ) );
    $event->save();

    $occurrence = new EventOccurrence();
    $occurrence['vendor_event_occurrence_id'] = 1110;
    $occurrence->setStart( '2010-01-31 19:30:00' );
    $occurrence->setEnd( '2010-01-31  19:30:00' );
    $occurrence->setUtcOffset( '-05:00:00' );
    $occurrence->link( 'Event', array( 1 ) );
    $occurrence->link( 'Poi', array( 1 ) );
    $occurrence->save();

    $property = new EventProperty();
    $property['lookup'] = 'test key 1';
    $property['value'] = 'test value 1';
    $property->link( 'Event', array( 1 ) );
    $property->save();

    $property2 = new EventProperty();
    $property2['lookup'] = 'test key 2';
    $property2['value'] = 'test value 2';
    $property2->link( 'Event', array( 1 ) );
    $property2->save();

    $event2 = new Event();
    $event2['VendorEventCategories'] = $vendorEventCategories;
    $event2['EventCategories'] = $eventCategories;
    $event2['vendor_event_id'] = 1112;
    $event2->setName( 'test event2' . $this->specialChars );
    $event2->link( 'Vendor', array( 1 ) );
    $event2->save();

    $occurrence2 = new EventOccurrence();
    $occurrence2['vendor_event_occurrence_id'] = 1110;
    $occurrence2->setStart( '2010-01-31 19:30:00' );
    $occurrence2->setEnd( '2010-01-31  19:30:00' );
    $occurrence2->setUtcOffset( '-05:00:00' );
    $occurrence2->link( 'Event', array( 2 ) );
    $occurrence2->link( 'Poi', array( 1 ) );
    $occurrence2->save();

    $occurrence3 = new EventOccurrence();
    $occurrence3['vendor_event_occurrence_id'] = 1111;
    $occurrence3->setStart( '2010-01-31 19:30:00' );
    $occurrence3->setEnd( '2010-01-31  19:30:00' );
    $occurrence3->setUtcOffset( '-05:00:00' );
    $occurrence3->link( 'Event', array( 2 ) );
    $occurrence3->link( 'Poi', array( 2 ) );
    $occurrence3->save();

    $occurrence4 = new EventOccurrence();
    $occurrence4['vendor_event_occurrence_id'] = 1111;
    $occurrence4->setStart( '2010-01-31 19:30:00' );
    $occurrence4->setEnd( '2010-01-31  19:30:00' );
    $occurrence4->setUtcOffset( '-05:00:00' );
    $occurrence4->link( 'Event', array( 2 ) );
    $occurrence4->link( 'Poi', array( 2 ) );
    $occurrence4->save();


    $this->destination = dirname( __FILE__ ) . '/../../export/event/test.xml';
    $this->export = new XMLExportEvent( $this->vendor, $this->destination );

    $this->export->run();
    $this->domDocument = new DOMDocument();
    $this->domDocument->load( $this->destination );
    $this->xpath = new DOMXPath( $this->domDocument );

    $this->escapedSpecialChars = htmlspecialchars( $this->specialChars );
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
   * test generateXML() has vendor-events root tag with required attributes
   */
  public function testGeneratedXMLHasEventWithRequiredAttribute()
  {
    $this->assertTrue( $this->domDocument instanceof DOMDocument );

    //vendor-event
    $rootElement = $this->domDocument->firstChild;
    $this->assertEquals( $this->vendor->getName(), $rootElement->getAttribute('vendor') );
    $this->assertRegExp( '/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}/', $rootElement->getAttribute('modified') );
  }

  /**
   * Each event should have a name
   */
  public function testGeneratedXMLEventTags()
  {
    $eventElement = $this->domDocument->firstChild->firstChild;
    $this->assertTrue( $eventElement instanceof DOMElement );
    $this->assertEquals( '1111', $eventElement->getAttribute('id') );
    $this->assertRegExp( '/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}/', $eventElement->getAttribute('modified') );

    $this->assertEquals('test event', $eventElement->getElementsByTagName('name')->item(0)->nodeValue );
  }

  /**
   * test geneate if xml has at least one event with its required children
   */
  public function testGeneratedXMLCategory()
  {
    $categoryElements1 = $this->xpath->query( '/vendor-events/event[1]/category' );

    $this->assertEquals(3, $categoryElements1->length);

    $this->assertEquals( 'concerts', $categoryElements1->item(0)->nodeValue );
    $this->assertEquals( 'theater', $categoryElements1->item(1)->nodeValue );
    $this->assertEquals( 'sport', $categoryElements1->item(2)->nodeValue );

    $categoryElements2 = $this->xpath->query( '/vendor-events/event[2]/category' );
    $this->assertEquals( 'concerts', $categoryElements2->item(0)->nodeValue );
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
    $this->assertEquals( 'test vendor short description', $shortDescriptions->item(0)->nodeValue );

    $descriptions = $versionTag->getElementsByTagName( 'description' );
    $this->assertEquals(1, $descriptions->length );
    $this->assertEquals( 'test vendor description', $descriptions->item(0)->nodeValue );

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
   * Check showtimes children
   */
  public function testGeneratedXMLEventShowtimesDirectChildrenTags()
  {
    $showtimes = $this->xpath->query( '/vendor-events/event[1]/showtimes' );
    $this->assertEquals( 1, $showtimes->length );

    $showtimes1 = $showtimes->item(0);

    $this->assertEquals( '1', $showtimes1->getElementsByTagName( 'place' )->item(0)->getAttribute( 'place-id' ) );
    $this->assertEquals( 'http://timeout.com', $showtimes1->getElementsByTagName( 'booking_url' )->item(0)->nodeValue);
    $this->assertEquals( '2010-01-31', $showtimes1->getElementsByTagName( 'start_date' )->item(0)->nodeValue, 'Testing the Start time' );
    $this->assertEquals( '19:30:00', $showtimes1->getElementsByTagName( 'event_time' )->item(0)->nodeValue );
    $this->assertEquals( '-05:00:00', $showtimes1->getElementsByTagName( 'utc_offset' )->item(0)->nodeValue );

    $numPlacesForEvent2 = $this->xpath->query( '/vendor-events/event[2]/showtimes/place' )->length;
    $this->assertEquals( 2, $numPlacesForEvent2 );
  }

  /**
   * check properties tags
   */
  public function testPropertyTags()
  {
    $propertyElements1 = $this->xpath->query( '/vendor-events/event[1]/version/property' );
    $this->assertEquals(2, $propertyElements1->length);

    $this->assertEquals( 'test key 1', $propertyElements1->item(0)->getAttribute( 'key' ) );
    $this->assertEquals( 'test value 1', $propertyElements1->item(0)->nodeValue );
    $this->assertEquals( 'test key 2', $propertyElements1->item(1)->getAttribute( 'key' ) );
    $this->assertEquals( 'test value 2', $propertyElements1->item(1)->nodeValue );
  }

  /**
   * check xml against customer's schema
   */
  public function testAgainstSchema()
  {
    $this->assertTrue( $this->domDocument->schemaValidate( TO_PROJECT_ROOT_PATH . '/data/xml_schemas/' . 'events.xsd' ) );
  }

  /**
   *
   */
  public function testCorrectNumberPlaceAndOccurrenceTags()
  {
    $placesForEvent2 = $this->xpath->query( '/vendor-events/event[2]/showtimes/place' );

    $this->assertEquals( 2, $placesForEvent2->length );
    $this->assertEquals( 1, $placesForEvent2->item(0)->getElementsByTagName( 'occurrence' )->length );
    $this->assertEquals( 2, $placesForEvent2->item(1)->getElementsByTagName( 'occurrence' )->length );
}


}
?>
