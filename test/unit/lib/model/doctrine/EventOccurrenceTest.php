<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for EventOccurance Model
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

  /**
   * test getVendorUidFieldName() returns the right string
   */
  public function testOccurrencesAreNotDuplicated()
  {
    $occurrence1 = ProjectN_Test_Unit_Factory::get( 'EventOccurrence' );
    $occurrence2 = ProjectN_Test_Unit_Factory::get( 'EventOccurrence' );

    $event = ProjectN_Test_Unit_Factory::get( 'Event' );
    $event[ 'EventOccurrence' ][] = $occurrence1;
    $event[ 'EventOccurrence' ][] = $occurrence2;
    $event->save();

    $event[ 'EventOccurrence' ][] = ProjectN_Test_Unit_Factory::get( 'EventOccurrence' );
    $event[ 'EventOccurrence' ][] = ProjectN_Test_Unit_Factory::get( 'EventOccurrence' );
    $event[ 'EventOccurrence' ][] = ProjectN_Test_Unit_Factory::get( 'EventOccurrence' );
    $event[ 'EventOccurrence' ][] = ProjectN_Test_Unit_Factory::get( 'EventOccurrence' );
    $event[ 'EventOccurrence' ][] = ProjectN_Test_Unit_Factory::get( 'EventOccurrence' );
    $event->save();

    $this->assertEquals( 1, Doctrine::getTable( 'EventOccurrence' )->count() );
  }
}
?>
