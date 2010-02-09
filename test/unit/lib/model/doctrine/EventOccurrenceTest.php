<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for EventOccurrence.
 * Generated by PHPUnit on 2010-01-26 at 13:20:53.
 */
class EventOccurrenceTest extends PHPUnit_Framework_TestCase
{

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  /*
   * testIfAutomaticVendorEventOccurrenceIdGenerationWorks
   */
  public function testIfAutomaticVendorEventOccurrenceIdGenerationWorks()
  {
    $eventOccurrenceObj = new EventOccurrence();
    $eventOccurrenceObj->generateVendorEventOccurrenceId( 1, 1, '08.02.2010 04:20' );
    
    $this->assertEquals('1_1_20100208042000', $eventOccurrenceObj[ 'vendor_event_occurrence_id' ] );
  }

}
?>
