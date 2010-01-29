<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../lib/logger.class.php';
require_once dirname(__FILE__).'/../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../bootstrap.php';

/**
 * Test class for logger.
 *
 * @package test
 * @subpackage lib.unit
 *
 * @author Timmy Bowler <timbowler@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class loggerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var logger Contains the logger object
     */
    protected $object;

    /**
     *
     * @var vendor Contains Vendor object
     */
    protected $vendorObj;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {

        ProjectN_Test_Unit_Factory::createDatabases();
        $this->vendorObj = ProjectN_Test_Unit_Factory::get('vendor');
        $this->object = new logger($this->vendorObj, logger::MOVIE);

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
     *  Test that the counter for new inserts is incrementing
     */
    public function testCountNewInsert()
    {
        $this->object->countNewInsert();
        $this->assertEquals('1', $this->object->totalInserts, 'Increment the total by one');

    }

    /**
     * Test that the counter for new inserts is working
     */
    public function testCountUpdate()
    {
       $this->object->countUpdate();
       $this->assertEquals('1', $this->object->totalUpdates, 'Increment the total updates by one');
    }

    /**
     * @todo Implement testSaveStats().
     */
    public function testSaveStats()
    {
       $this->object->countNewInsert();
       $this->object->countUpdate();
       $this->object->saveStats();

       $results = Doctrine::getTable('ImportStats')->findAll();
       $results = $results->toArray();
       $result = array_pop($results);

       $this->assertEquals('1', $result['total_inserts'], 'The insert was incremented');
       $this->assertEquals('1', $result['total_updates'], 'The update was incremented');

    }

    /**
     * Test that the logger only accepts either movie, event or poi
     */
    public function testLoggerTypes()
    {
        $this->setExpectedException('Exception');
        $this->object = new logger($this->vendorObj, 'moviey');
    }

    /**
     * 
     */
    public function testGetType()
    {
      $this->assertEquals( logger::MOVIE, $this->object->getType() );
    }
}
?>
