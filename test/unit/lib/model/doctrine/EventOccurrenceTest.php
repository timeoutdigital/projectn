<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';
/**
 * Description
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Rajeevan kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class EventOccurrenceTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();
        Event::resetVendorCategoryCache();
    }

    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    public function testCleanEmptyStringEmptyString()
    {
        $event = ProjectN_Test_Unit_Factory::add( 'Event' );
        $eo =ProjectN_Test_Unit_Factory::get( 'EventOccurrence' );
        $eo['booking_url'] = '';
        $eo['start_date'] = '2011-01-12';
        $eo['end_date'] = '';
        $eo['end_time'] = '';
        $eo['start_time'] = ' ';
        $eo['Event'] = $event;
        $eo->save();

        $this->assertEquals( null, $eo['end_date']);
        $this->assertEquals( null, $eo['end_time']);
        $this->assertEquals( null, $eo['start_time']);
        $this->assertEquals( '2011-01-12', $eo['start_date']);
    }

    public function testCleanEmptyStringValidDateTime()
    {
        $event = ProjectN_Test_Unit_Factory::add( 'Event' );
        $eo =ProjectN_Test_Unit_Factory::get( 'EventOccurrence' );
        $eo['booking_url'] = '';
        $eo['start_date'] = '2011-01-12';
        $eo['end_date'] = '2011-01-13';
        $eo['end_time'] = '10:30';
        $eo['start_time'] = '12:00';
        $eo['Event'] = $event;
        $eo->save();

        $this->assertEquals( '2011-01-13', $eo['end_date']);
        $this->assertEquals( '10:30', $eo['end_time']);
        $this->assertEquals( '12:00', $eo['start_time']);
        $this->assertEquals( '2011-01-12', $eo['start_date']);
        $this->assertEquals( null, $eo['booking_url']);
    }
}