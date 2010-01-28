<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../lib/logger.class.php';
require_once dirname(__FILE__).'/../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../bootstrap.php';

/**
 * Test class for logger.
 * Generated by PHPUnit on 2010-01-26 at 15:11:41.
 */
class loggerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var logger
     */
    protected $object;

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
     *
     */
    public function testCountNewInsert()
    {
        $this->object->countNewInsert();
        $this->assertEquals('1', $this->object->totalInserts, 'Increment the total by one');

    }

    /**
     * @todo Implement testCountUpdate().
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

    public function testLoggerTypes()
    {
        $this->setExpectedException('Exception');
        $this->object = new logger($this->vendorObj, 'moviey');
    }

    public function testGetType()
    {
      $this->assertEquals( logger::MOVIE, $this->object->getType() );
    }
}
?>
