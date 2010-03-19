<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for Event Occurance Table Model
 *
 * @package test
 * @subpackage doctrine.model.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class EventOccurrenceTableTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var EventOccurrenceTable
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    $this->object = Doctrine::getTable('EventOccurrence');
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
   * test getVendorUidFieldName() returns the right string
   */
  public function testGetVendorUidFieldName()
  {
    $column = $this->object->getVendorUidFieldName();
    $this->assertTrue( $this->object->hasColumn( $column ) );
  }

  /*
   * testIfAutomaticVendorEventOccurrenceTableIdGenerationWorks
   */
  public function testIfAutomaticVendorEventOccurrenceTableIdGenerationWorks()
  {
    $eventOccurrenceId = Doctrine::getTable('EventOccurrence')->generateVendorEventOccurrenceId( 1, 1, '08.02.2010 04:20' );
    $this->assertEquals('1_1_20100208042000', $eventOccurrenceId);
  }

	public function testFindEquivalent()
	{
		$event = ProjectN_Test_Unit_Factory::get( 'Event' );
		$event[ 'EventOccurrence' ][] = ProjectN_Test_Unit_Factory::get( 'EventOccurrence' );
		$event->save();

		$occurrence2 = ProjectN_Test_Unit_Factory::get( 'EventOccurrence' );

		$equivalents = Doctrine::getTable( 'EventOccurrence' )->findEquivalents( $occurrence2 );
		$this->assertEquals( 0, $equivalents->count() );

		$event[ 'EventOccurrence' ][] = $occurrence2;

		$equivalents = Doctrine::getTable( 'EventOccurrence' )->findEquivalents( $occurrence2 );
		$this->assertEquals( 1, $equivalents->count() );

		$event[ 'EventOccurrence' ][] = ProjectN_Test_Unit_Factory::get( 'EventOccurrence' );
		$event[ 'EventOccurrence' ][] = ProjectN_Test_Unit_Factory::get( 'EventOccurrence' );
		$event[ 'EventOccurrence' ][] = ProjectN_Test_Unit_Factory::get( 'EventOccurrence' );
		$event->save();

		$equivalents = Doctrine::getTable( 'EventOccurrence' )->findEquivalents( $occurrence2 );
		$this->assertEquals( 1, $equivalents->count() );
		//you'll never get more than one because EventOccurrence doesn't save equivalent objects
	}

	public function testHasEquivalent()
	{
		$event = ProjectN_Test_Unit_Factory::get( 'Event' );
		$event[ 'EventOccurrence' ][] = ProjectN_Test_Unit_Factory::get( 'EventOccurrence' );
		$event->save();

		$occurrence2 = ProjectN_Test_Unit_Factory::get( 'EventOccurrence' );

		$equivalent = Doctrine::getTable( 'EventOccurrence' )->hasEquivalent( $occurrence2 );
		$this->assertFalse( $equivalent );

		$event[ 'EventOccurrence' ][] = $occurrence2;

		$equivalent = Doctrine::getTable( 'EventOccurrence' )->hasEquivalent( $occurrence2 );
		$this->assertTrue( $equivalent );
	}
}
?>
