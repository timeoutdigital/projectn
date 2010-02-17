<?php
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../../../../../lib/processXml.class.php';
require_once dirname(__FILE__).'/../../../../../lib/import/ny/nyImportBc.class.php';
require_once dirname(__FILE__).'/../../../../../lib/import/ny/processNyBcXml.class.php';
require_once dirname(__FILE__).'/../../../../../lib/logImport.class.php';
require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
spl_autoload_register(array('Doctrine', 'autoload'));


/**
 * Test class for nyImportBc which tests the importing of NY's bars and clubs
 *
 * @package test
 * @subpackage ny.import.lib.unit
 *
 * @author Timmy Bowler <timbowler@timeout.com>
 *
 * @copyright Timeout Communications Ltd;
 * 
 * 
 */
class nyImportBcTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var nyImportBc
     */
    protected $object;

     /**
     * @var $vendorObj Vendor
     */
    protected $vendorObj;

     /**
     * @var $xmlObj
     */
    protected $xmlObj;

     /**
     * @var $existingPoiObj Poi
     */
    protected $existingPoiObj;

     /**
     * @var $loggerObj Object
     */
    protected $loggerObj;
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
         try {

          ProjectN_Test_Unit_Factory::createDatabases();

          Doctrine::loadData('data/fixtures');
          $this->vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('ny', 'en-US');

          $this->xmlObj = new processNyBcXml( dirname(__FILE__).'/../../../data/tony_bc.xml' );
          $this->loggerObj = new logImport($this->vendorObj, 'poi');

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
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    /**validationException
     * Test that an existing poi is not duplicated
     */
    public function testExistingSamePoiIsNotImported()
    {
        $this->createExistingUnchangedPoi();
        $this->createObject();

        //Find by name so we know there is only 1
         $poi = Doctrine::getTable('Poi')->findByPoiName('Milanos Bar');
         $this->assertEquals(1, count($poi->toArray()), 'Test that there is only 1 in the DB');
    }

    /**
     * Test that a Poi that has changed is logged and updated.
     *
     */
    public function testExistingChangedPoiIsLoggedAndUpdated()
    {
        $this->createExistingChangedPoi();
        $this->createObject();
        $updateTestArray = $this->object->logger->changesCollection->toArray();

        //Check logger has looged the update
        $this->assertEquals('update', $updateTestArray[0]['type'], 'Testing record is updated');
        $this->assertEquals(1,$this->object->logger->totalUpdates, 'Testing that the total has incremented' );

        //Check the DB for all entries
        $poi = Doctrine::getTable('Poi')->findByPoiName('Milanos Bar');
        $this->assertEquals(1, count($poi->toArray()), 'Test that there is only 1 in the DB');

    }


    /**
     * Test that an extry not already in the database is saved.
     */
    public function testNonExistantPoiIsImported()
    {
       $this->createNonExistingUnchangedPoi();
       $this->createObject();
        
       //Check the DB for all entries
       $poi = Doctrine::getTable('Poi')->findByPoiName('Milanos Bar');
       $this->assertEquals(2, count($poi->toArray()), 'Test that there is only 1 in the DB');
    }


    /**
     * Creates the object that is being tested
     */
    private function createObject()
    {
        
        $this->object = new nyImportBc($this->xmlObj, $this->vendorObj,  $this->loggerObj);
        $this->object->importBars($this->getXMLString());
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
           $this->existingPoiObj['poi_name'] = 'Milanos Bar';
           $this->existingPoiObj['street'] = '51 E Houston St between Mott and Mulberry Sts';
           $this->existingPoiObj['city'] = 'NY';
           $this->existingPoiObj['vendor_poi_id'] = 2936;
           $this->existingPoiObj['Vendor'] = $this->vendorObj;
           $this->existingPoiObj['country'] = 'USA';
           $this->existingPoiObj['longitude'] = '-122.805568';
           $this->existingPoiObj['latitude'] = '38.5105557';
           $this->existingPoiObj['local_language'] = 'en';
           $this->existingPoiObj['description'] = 'This no-nonsense Nolita dive has an Italian moniker, but thats where the affectations end. Suck in your stomach and squeeze through the narrow stretch between the bar and the clammy back wall, a bottleneck for the eclectic crowd of artists and perpetual barflies. You might need to holler your order over the jukes noisy rock tuneskeep it simple and ask for the signature combo of Guinness and a shot of Jameson ($11).';
           $this->existingPoiObj['price_information'] = 'Average drink: $4';
           $this->existingPoiObj['public_transport_links'] = 'Subway: B, D, F, V to BroadwayLafayette St; 6 to Bleecker St';
           $this->existingPoiObj['phone'] = '+1 212 226 8844';
           $this->existingPoiObj['zips'] = '10012';
           $this->existingPoiObj->save();
    }

    /**
     * Create an entry in the database that has different details from the one in the feed
     */
    public function createExistingChangedPoi()
    {
          /**
           * Both the description and price information are two changed fields
           *
           */
           $this->existingPoiObj = new Poi();
           $this->existingPoiObj['poi_name'] = 'Milanos Bar';
           $this->existingPoiObj['street'] = '51 E Houston St between Mott and Mulberry Sts';
           $this->existingPoiObj['city'] = 'NY';
           $this->existingPoiObj['vendor_poi_id'] = 2936;
           $this->existingPoiObj['Vendor'] = $this->vendorObj;
           $this->existingPoiObj['country'] = 'USA';
           $this->existingPoiObj['longitude'] = '-122.805568';
           $this->existingPoiObj['latitude'] = '38.5105557';
           $this->existingPoiObj['local_language'] = 'en';
           $this->existingPoiObj['description'] = 'XXX This no-nonsense Nolita dive has an Italian moniker, but thats where the affectations end. Suck in your stomach and squeeze through the narrow stretch between the bar and the clammy back wall, a bottleneck for the eclectic crowd of artists and perpetual barflies. You might need to holler your order over the jukes noisy rock tuneskeep it simple and ask for the signature combo of Guinness and a shot of Jameson ($11).';
           $this->existingPoiObj['price_information'] = 'Average drink: $5';
           $this->existingPoiObj['public_transport_links'] = 'Subway: B, D, F, V to BroadwayLafayette St; 6 to Bleecker St';
           $this->existingPoiObj['phone'] = '+1 212 226 8844';
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
           $this->existingPoiObj['poi_name'] = 'Milanos Bar';
           $this->existingPoiObj['street'] = '51 E Houston St between Mott and Mulberry Sts';
           $this->existingPoiObj['city'] = 'NY';
           $this->existingPoiObj['vendor_poi_id'] = 29366;
           $this->existingPoiObj['Vendor'] = $this->vendorObj;
           $this->existingPoiObj['country'] = 'USA';
           $this->existingPoiObj['longitude'] = '-122.805568';
           $this->existingPoiObj['latitude'] = '38.5105557';
           $this->existingPoiObj['local_language'] = 'en';
           $this->existingPoiObj['description'] = 'This no-nonsense Nolita dive has an Italian moniker, but thats where the affectations end. Suck in your stomach and squeeze through the narrow stretch between the bar and the clammy back wall, a bottleneck for the eclectic crowd of artists and perpetual barflies. You might need to holler your order over the jukes noisy rock tuneskeep it simple and ask for the signature combo of Guinness and a shot of Jameson ($11).';
           $this->existingPoiObj['price_information'] = 'Average drink: $4';
           $this->existingPoiObj['public_transport_links'] = 'Subway: B, D, F, V to BroadwayLafayette St; 6 to Bleecker St';
           $this->existingPoiObj['phone'] = '+1 212 226 8844';
           $this->existingPoiObj['zips'] = '10012';
           $this->existingPoiObj->save();
    }

    /**
     *
     * Create a simplexml object
     *
     * @return <SimpleXMLElement> SimpleXml object with one vendor
     *
     */
    private function getXMLString()
    {
        $string = <<<EOF

        <ROW MODID="331" RECORDID="2936">
		<barkey>Cheap drinks
Happy hour
After-work crowd
Jukebox
Sports bar
Extensive beer selection
Artsy
Older crowd
Date place
Wheelchair accessible bathroom</barkey>
		<barkey.1/>
		<barkey.2/>
		<barkey.3>MonFri 47</barkey.3>
		<barkey.4/>
		<barkey.5/>
		<BAR.body>This no-nonsense Nolita dive has an Italian moniker, but thats where the affectations end. Suck in your stomach and squeeze through the narrow stretch between the bar and the clammy back wall, a bottleneck for the eclectic crowd of artists and perpetual barflies. You might need to holler your order over the jukes noisy rock tuneskeep it simple and ask for the signature combo of Guinness and a shot of Jameson ($11).</BAR.body>
		<Category/>
		<cc.0>Cash only</cc.0>
		<cc.1/>
		<cc.10/>
		<cc.11/>
		<cc.12/>
		<cc.13/>
		<cc.14/>
		<cc.15/>
		<cc.2/>
		<cc.3/>
		<cc.4/>
		<cc.5/>
		<cc.6/>
		<cc.7/>
		<cc.8/>
		<cc.9/>
		<cheapeats/>
		<city.state.0>New York, NY</city.state.0>
		<city.state.1>New York, NY</city.state.1>
		<city.state.10>New York, NY</city.state.10>
		<city.state.11>New York, NY</city.state.11>
		<city.state.12>New York, NY</city.state.12>
		<city.state.13>New York, NY</city.state.13>
		<city.state.14>New York, NY</city.state.14>
		<city.state.15>New York, NY</city.state.15>
		<city.state.2>New York, NY</city.state.2>
		<city.state.3>New York, NY</city.state.3>
		<city.state.4>New York, NY</city.state.4>
		<city.state.5>New York, NY</city.state.5>
		<city.state.6>New York, NY</city.state.6>
		<city.state.7>New York, NY</city.state.7>
		<city.state.8>New York, NY</city.state.8>
		<city.state.9>New York, NY</city.state.9>
		<crixpix/>
		<entertainment/>
		<hood.shortcalc.0>ChinatownLittle Italy</hood.shortcalc.0>
		<hood.shortcalc.1/>
		<hood.shortcalc.10/>
		<hood.shortcalc.11/>
		<hood.shortcalc.12/>
		<hood.shortcalc.13/>
		<hood.shortcalc.14/>
		<hood.shortcalc.15/>
		<hood.shortcalc.2/>
		<hood.shortcalc.3/>
		<hood.shortcalc.4/>
		<hood.shortcalc.5/>
		<hood.shortcalc.6/>
		<hood.shortcalc.7/>
		<hood.shortcalc.8/>
		<hood.shortcalc.9/>
		<hours.0>8am4am</hours.0>
		<hours.1/>
		<hours.10/>
		<hours.11/>
		<hours.12/>
		<hours.13/>
		<hours.14/>
		<hours.15/>
		<hours.2/>
		<hours.3/>
		<hours.4/>
		<hours.5/>
		<hours.6/>
		<hours.7/>
		<hours.8/>
		<hours.9/>
		<ID>20191</ID>
		<loc.1/>
		<loc.10/>
		<loc.11/>
		<loc.12/>
		<loc.13/>
		<loc.14/>
		<loc.15/>
		<loc.2/>
		<loc.3/>
		<loc.4/>
		<loc.5/>
		<loc.6/>
		<loc.7/>
		<loc.8/>
		<loc.9/>
		<location.0>51 E Houston St between Mott and Mulberry Sts</location.0>
		<name.0>Milanos Bar</name.0>
		<name.1/>
		<name.10/>
		<name.11/>
		<name.12/>
		<name.13/>
		<name.14/>
		<name.15/>
		<name.2/>
		<name.3/>
		<name.4/>
		<name.5/>
		<name.6/>
		<name.7/>
		<name.8/>
		<name.9/>
		<pagenum>31</pagenum>
		<phone.0>212-226-8844</phone.0>
		<phone.1/>
		<phone.10/>
		<phone.11/>
		<phone.12/>
		<phone.13/>
		<phone.14/>
		<phone.15/>
		<phone.2/>
		<phone.3/>
		<phone.4/>
		<phone.5/>
		<phone.6/>
		<phone.7/>
		<phone.8/>
		<phone.9/>
		<prices.0>Average drink: $4</prices.0>
		<prices.1/>
		<prices.10/>
		<prices.11/>
		<prices.12/>
		<prices.13/>
		<prices.14/>
		<prices.15/>
		<prices.2/>
		<prices.3/>
		<prices.4/>
		<prices.5/>
		<prices.6/>
		<prices.7/>
		<prices.8/>
		<prices.9/>
		<PrimaryCuisine>Date place</PrimaryCuisine>
		<SecondaryCuisine/>
		<subway.0>Subway: B, D, F, V to BroadwayLafayette St; 6 to Bleecker St</subway.0>
		<subway.1/>
		<subway.10/>
		<subway.11/>
		<subway.12/>
		<subway.13/>
		<subway.14/>
		<subway.15/>
		<subway.2/>
		<subway.3/>
		<subway.4/>
		<subway.5/>
		<subway.6/>
		<subway.7/>
		<subway.8/>
		<subway.9/>
		<BAR.best/>
		<TONY100/>
		<Author>rr BC04 Clare Lambe</Author>
		<WEbody/>
		<date.modified>12/7/2009</date.modified>
		<WEphone>212-226-8844</WEphone>
		<Status>Fact check in
Ready to galley
Copy edit in
Final edit in
First edit in
Raw review in</Status>
		<winelist/>
		<zip.0>10012</zip.0>
		<zip.1/>
		<zip.10/>
		<zip.11/>
		<zip.12/>
		<zip.13/>
		<zip.14/>
		<zip.15/>
		<zip.2/>
		<zip.3/>
		<zip.4/>
		<zip.5/>
		<zip.6/>
		<zip.7/>
		<zip.8/>
		<zip.9/>
		<addendum/>
		<pricePoint>$</pricePoint>
		<namesort1>MilanosBar</namesort1>
		<ol.keywords/>
		<Publication>BC</Publication>
		<closed.0/>
		<closed.1/>
		<closed.10/>
		<closed.11/>
		<closed.12/>
		<closed.13/>
		<closed.14/>
		<closed.15/>
		<closed.2/>
		<closed.3/>
		<closed.4/>
		<closed.5/>
		<closed.6/>
		<closed.7/>
		<closed.8/>
		<closed.9/>
		<url.0/>
	</ROW>





EOF;
        return simplexml_load_string($string);
    }
}
?>