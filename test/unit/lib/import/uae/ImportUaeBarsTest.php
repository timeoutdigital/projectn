<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';


/**
 * Test class for Import Uae Bars.
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
class ImportUaeBarsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ImportUaeBars
     */
    protected $object;


    protected  $vendorObj;

    protected  $xmlObj;

    protected $existingPoiObj;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        try {

          ProjectN_Test_Unit_Factory::createDatabases();
          Doctrine::loadData('data/fixtures');
          

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
       //Close DB connection
       ProjectN_Test_Unit_Factory::destroyDatabases();
    
    }

    /**
     * @todo Implement testImportPoi().
     */
    public function testImportPoi()
    {
        
        $this->createObject();
        $this->createExistingUnchangedPoi();
        $this->object->importPoi($this->xmlObj);
    }

     /**
     * Creates the object that is being tested
     */
    private function createObject()
    {

        if($this->xmlObj == '' || $this->vendorObj == '' )
        {
            $feed = new Curl('http://www.timeoutdubai.com/nokia/bars');
            $feed->exec();
            $xmlObj = new ValidateUaeXmlFeed($feed->getResponse());
            $this->xmlObj = $xmlObj->getXmlFeed();
            $this->vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('dubai', 'en-US');
        }

        $this->object = new ImportUaeBars($this->xmlObj, $this->vendorObj);

    }

    /**
     * Creat an entry in the database that matches one from the feed
     */
    private function createExistingUnchangedPoi()
    {
          /**
           * This is a poi which is in the xml
           *
           */
           $this->existingPoiObj = new Poi();
           $this->existingPoiObj['poi_name'] = 'Bartini';
           $this->existingPoiObj['street'] = 'Al Sufouh Road';
           $this->existingPoiObj['city'] = 'Dubai';
           $this->existingPoiObj['vendor_poi_id'] = 129300;
           $this->existingPoiObj['Vendor'] = $this->vendorObj;
           $this->existingPoiObj['country'] = 'ARE';
           $this->existingPoiObj['longitude'] = '-122.805568';
           $this->existingPoiObj['latitude'] = '38.5105557';
           $this->existingPoiObj['local_language'] = 'en';
           $this->existingPoiObj['description'] = 'You can almost see what they were trying to do with Bartini';
           $this->existingPoiObj['phone'] = '+4 399 5000';
           $this->existingPoiObj['zips'] = '10012';
           $this->existingPoiObj->save();
    }


    /**
     * Create an entry in the database that is not in the feed
     */
    private function createNonExistingUnchangedPoi()
    {
          /**
           * The vendor_poi_id has changed and therefore classed as a new entry
           *
           */
         $this->existingPoiObj = new Poi();
           $this->existingPoiObj['poi_name'] = 'Bartini';
           $this->existingPoiObj['street'] = 'Al Sufouh Road';
           $this->existingPoiObj['city'] = 'Dubai';
           $this->existingPoiObj['vendor_poi_id'] = 201911;
           $this->existingPoiObj['Vendor'] = $this->vendorObj;
           $this->existingPoiObj['country'] = 'ARE';
           $this->existingPoiObj['longitude'] = '-122.805568';
           $this->existingPoiObj['latitude'] = '38.5105557';
           $this->existingPoiObj['local_language'] = 'en';
           $this->existingPoiObj['description'] = 'You can almost see what they were trying to do with Bartini';
           $this->existingPoiObj['phone'] = '+4 399 5000';
           $this->existingPoiObj['zips'] = '10012';
           $this->existingPoiObj->save();

    }
}
?>
