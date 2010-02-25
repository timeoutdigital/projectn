<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../lib/import/uae/ImportUaeEvents.class.php';
require_once dirname(__FILE__).'/../../../../../lib/ValidateXmlFeed.class.php';
require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
spl_autoload_register(array('Doctrine', 'autoload'));



/**
 * Test class for Import Uae Events.
 *
 * @package test
 * @subpackage import.lib.unit
 *
 * @author Timmy Bowler <timbowler@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */
class ImportUaeEventsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ImportUaeEvents
     */
    protected $object;

    protected $xmlObj;

    protected $vendorObj;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
         try {

          ProjectN_Test_Unit_Factory::createDatabases();
          Doctrine::loadData('data/fixtures');
          $this->vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('dubai', 'en-US');

        }
        catch( Exception $e )
        {
          echo $e->getMessage();
        }

        
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @todo Implement testImport().
     */
    public function testImport()
    {
        $this->createObject();
        $this->object->import(); 
    }

    /**
     * @todo Implement testImportPois().
     */
    public function testImportPois()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testImportEvents().
     */
    public function testImportEvents()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetPoi().
     */
    public function testGetPoi()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetEvent().
     */
    public function testGetEvent()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * Creates the object that is being tested
     */
    private function createObject()
    {

        if($this->xmlObj == '')
        {
            $feed = new Curl('http://www.timeoutdubai.com/nokia/latestevents');
            $xmlObj = new ValidateUaeXmlFeed($feed->getResponse());
            $this->xmlObj = $xmlObj->getXmlFeed();
        }

        $this->object = new ImportUaeEvents( $this->xmlObj , $this->vendorObj);
        
    }
}
?>
