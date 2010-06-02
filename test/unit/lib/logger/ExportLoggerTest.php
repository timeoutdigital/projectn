<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../bootstrap.php';

/**
 * Test class for ImportLogger.
 */
class ExportLoggerTest extends PHPUnit_Framework_TestCase {

    /**
     * @var ImportLogger
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

}
?>
