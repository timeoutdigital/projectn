<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';


/**
 * Test class for chicagoImportBcEd.
 *
 * @package test
 * @subpackage chicago.import.lib.unit
 *
 * @author Timmy Bowler <timbowler@timeout.com>
 *
 * @version 1.0.0
 */
class chicagoImportBcEdTest extends PHPUnit_Framework_TestCase
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
          $this->vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('chicago', 'en-US');

          $this->xmlObj = new processNyBcXml( TO_TEST_DATA_PATH.'/toc_ed.xml' );
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

    /**
     * Test to see that POIs with Property names 'features' do not contain the string "Cheap (entrees under $10)".
     * as per ticket #251
     */
    public function testPoiPropertyNamedFeaturesDoesNotContainCheapEatsString()
    {
        $this->createObject();
        $poiProperty = Doctrine::getTable('PoiProperty')->findByLookup('features');
        $this->assertEquals( false, strpos( $poiProperty[0]['value'], "Cheap (entrees under $10)" ), "POI value for lookup 'features' cannot contain string 'Cheap (entrees under $10)'" );
    }

    /**
     * Test to see that POIs with a property of 'cuisine' does not contain price information s per #260
     */
    public function testPoiPropertyNamedCuisineDoesNotContainPriceInfo()
    {
        $this->createObject();
        $poiProperty = Doctrine::getTable('PoiProperty')->findByLookup('cuisine');
        $this->assertEquals( false, strpos( $poiProperty[0]['value'], ": $" ), "POI value for lookup 'cuisine' cannot contain string ': $'" );
    }

    /**
     * Test to see that POIs with a property of 'cuisine' and correct price info now contain a 'price_general_remark' property, per #260.
     */
    public function testPoiPropertyNamedCuisineHaveAPropertyCalledPriceGeneralRemarkWithPriceInfoInIt()
    {
        $this->createObject();
        $poiProperty = Doctrine::getTable('PoiProperty')->findByLookup('cuisine');
        $poi = Doctrine::getTable('Poi')->findOneById( $poiProperty[0]['Poi']['id'] );

        foreach( $poi['PoiProperty'] as $poiProperty )
        {
            if( isset( $poiProperty['value'] ) && (string) $poiProperty['lookup'] == 'cuisine' )
            {
                $this->assertEquals( false, strpos( $poiProperty['value'], ": $" ), "POI value for lookup 'cuisine' cannot contain string ': $'" );
                $this->assertEquals( true, ( isset( $previousProperty['lookup'] ) && $previousProperty['lookup'] == 'price_general_remark' ), "Price info was removed from 'cuisine' property but 'price_general_remark' was not added." );
                $this->assertEquals( true, is_numeric( strpos( $previousProperty['value'], "$" ) ), "'price_general_remark' value should now contain a '$'" );
            }
            $previousProperty = $poiProperty;
        }
    }

    /**
     * validationException
     * Test that an existing poi is not duplicated
     */
    public function testExistingSamePoiIsNotImported()
    {
        $this->createExistingUnchangedPoi();
        $this->createObject();

        //Find by name so we know there is only 1
        $this->assertEquals(1, Doctrine::getTable('Poi')->findByPoiName('A La Turka')->count(), 'Test that there is only 1 in the DB');
    }

    /**
     * Test that a Poi that has changed is logged and updated.
     *
     * @todo re-implment
     *
     */
    public function testExistingChangedPoiIsLoggedAndUpdated()
    {
        $this->markTestSkipped();
        $this->createExistingChangedPoi();
        $this->createObject();
        $updateTestArray = $this->object->loggerObj->changesCollection->toArray();

        //Check logger has looged the update
        $this->assertEquals('update', $updateTestArray[0]['type'], 'Testing record is updated');
        $this->assertEquals(1,$this->object->loggerObj->totalUpdates, 'Testing that the total has incremented' );

        //Check the DB for all entries
        $poi = Doctrine::getTable('Poi')->findByPoiName('A La Turka');
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
       $poi = Doctrine::getTable('Poi')->findByPoiName('A La Turka');
       $this->assertEquals(2, count($poi->toArray()), 'Test that there is only 1 in the DB');

     }


    /**
     * Creates the object that is being tested
     */
    private function createObject()
    {

        $this->object = new chicagoImportBcEd($this->xmlObj, $this->vendorObj,  $this->loggerObj);
        $this->object->importPoi($this->getXMLString()); // Loads from String Below (not from file)
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
           $this->existingPoiObj['poi_name'] = 'A La Turka';
           $this->existingPoiObj['street'] = '3134 N Lincoln Ave';
           $this->existingPoiObj['city'] = 'Chicago';
           $this->existingPoiObj['vendor_poi_id'] = 1004;
           $this->existingPoiObj['Vendor'] = $this->vendorObj;
           $this->existingPoiObj['country'] = 'USA';
           $this->existingPoiObj['longitude'] = '-122.805568';
           $this->existingPoiObj['latitude'] = '38.5105557';
           $this->existingPoiObj['local_language'] = 'en';
           $this->existingPoiObj['description'] = 'Modern-day Turkey isnt the idea here, so if youre looking for contemporary Istanbul, get a plane ticket. This dining room hearkens to times of eating on luxurious floor cushions and sucking on hookahs like theyre Tic Tacs. You can make a meal out of the hot appetizer platterthin zucchini pancakes, tangy rolls of feta-stuffed phyllo and the Turkish pizza, a housemade pita piled with perfectly seasoned ground lamb. Or fill up on the A La Turka special: sauted beef and grilled vegetables served with a slightly sweet eggplant puree. Either way the portions are sized for big appetites, so you wont leave hungry';
           $this->existingPoiObj['price_information'] = 'Average main course: $15';
           $this->existingPoiObj['public_transport_links'] = 'El: Brown to Paulina. Bus: 9, 11, 77';
           $this->existingPoiObj['phone'] = '+1 773-935-6101';
           $this->existingPoiObj['zips'] = '60657';
           $this->existingPoiObj['geocode_look_up'] = "Somewhere Nice.";
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
           $this->existingPoiObj['poi_name'] = 'A La Turka';
           $this->existingPoiObj['street'] = '3134 N Lincoln Ave';
           $this->existingPoiObj['city'] = 'Chicago';
           $this->existingPoiObj['vendor_poi_id'] = 1004;
           $this->existingPoiObj['Vendor'] = $this->vendorObj;
           $this->existingPoiObj['country'] = 'USA';
           $this->existingPoiObj['longitude'] = '-122.805568';
           $this->existingPoiObj['latitude'] = '38.5105557';
           $this->existingPoiObj['local_language'] = 'en';
           $this->existingPoiObj['description'] = 'XXX Modern-day Turkey isnt the idea here, so if youre looking for contemporary Istanbul, get a plane ticket. This dining room hearkens to times of eating on luxurious floor cushions and sucking on hookahs like theyre Tic Tacs. You can make a meal out of the hot appetizer platterthin zucchini pancakes, tangy rolls of feta-stuffed phyllo and the Turkish pizza, a housemade pita piled with perfectly seasoned ground lamb. Or fill up on the A La Turka special: sauted beef and grilled vegetables served with a slightly sweet eggplant puree. Either way the portions are sized for big appetites, so you wont leave hungry';
           $this->existingPoiObj['price_information'] = 'Average main course: $5';
           $this->existingPoiObj['public_transport_links'] = 'El: Brown to Paulina. Bus: 9, 11, 77';
           $this->existingPoiObj['phone'] = '+1 773-935-6101';
           $this->existingPoiObj['zips'] = '60657';
           $this->existingPoiObj['geocode_look_up'] = "Somewhere Nice.";
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
           $this->existingPoiObj['poi_name'] = 'A La Turka';
           $this->existingPoiObj['street'] = '3134 N Lincoln Ave';
           $this->existingPoiObj['city'] = 'Chicago';
           $this->existingPoiObj['vendor_poi_id'] = 10040;
           $this->existingPoiObj['Vendor'] = $this->vendorObj;
           $this->existingPoiObj['country'] = 'USA';
           $this->existingPoiObj['longitude'] = '-122.805568';
           $this->existingPoiObj['latitude'] = '38.5105557';
           $this->existingPoiObj['local_language'] = 'en';
           $this->existingPoiObj['description'] = 'Modern-day Turkey isnt the idea here, so if youre looking for contemporary Istanbul, get a plane ticket. This dining room hearkens to times of eating on luxurious floor cushions and sucking on hookahs like theyre Tic Tacs. You can make a meal out of the hot appetizer platterthin zucchini pancakes, tangy rolls of feta-stuffed phyllo and the Turkish pizza, a housemade pita piled with perfectly seasoned ground lamb. Or fill up on the A La Turka special: sauted beef and grilled vegetables served with a slightly sweet eggplant puree. Either way the portions are sized for big appetites, so you wont leave hungry';
           $this->existingPoiObj['price_information'] = 'Average main course: $15';
           $this->existingPoiObj['public_transport_links'] = 'El: Brown to Paulina. Bus: 9, 11, 77';
           $this->existingPoiObj['phone'] = '+1 773-935-6101';
           $this->existingPoiObj['zips'] = '60657';
           $this->existingPoiObj['geocode_look_up'] = "Somewhere else.";
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

       <ROW MODID="102" RECORDID="3140">
		<body>Modern-day Turkey isnt the idea here, so if youre looking for contemporary Istanbul, get a plane ticket. This dining room hearkens to times of eating on luxurious floor cushions and sucking on hookahs like theyre Tic Tacs. You can make a meal out of the hot appetizer platterthin zucchini pancakes, tangy rolls of feta-stuffed phyllo and the Turkish pizza, a housemade pita piled with perfectly seasoned ground lamb. Or fill up on the A La Turka special: sauted beef and grilled vegetables served with a slightly sweet eggplant puree. Either way the portions are sized for big appetites, so you wont leave hungry.</body>
		<category/>
		<cc/>
		<cheapeats/>
		<city.state>Chicago, IL</city.state>
		<closed/>
		<crixpix/>
		<crossstreet>between Barry and Belmont Aves</crossstreet>
		<cta>El: Brown to Paulina. Bus: 9, 11, 77</cta>
		<cuisine.1>Italian: $16-24</cuisine.1>
		<cuisine.2/>
		<cuisine.3/>
		<current.menu/>
		<delivery>Take-out</delivery>
		<EatOutAwards/>
		<EatOutAwards2007/>
		<EatOutAwards2008/>
		<features>Good for groups
Vegetarian-friendly
Late-night dining
Private party room(s)
Cheap (entrees under $10)</features>
		<hood>Lakeview</hood>
		<hours>Lunch (WedSun), Dinner</hours>
		<hours.24hrs/>
		<hours.breakfast/>
		<hours.brunch/>
		<hours.dinner/>
		<hours.lunch/>
		<hours.notes>Sun 11:30am10:30pm
MonWed 4:3010:30pm
FriSat 11:30ammidnight

</hours.notes>
		<icons/>
		<ID>1004</ID>
		<location>3134 N Lincoln Ave</location>
		<meals>smoking
Dinner
Lunch</meals>
		<multipleLocations/>
		<name>A La Turka</name>
		<new/>
		<phone>773-935-6101</phone>
		<pricePoint>$</pricePoint>
		<prices>Average main course: $15</prices>
		<reservations/>
		<status/>
		<updated/>
		<url>www.turkishkitchen.us</url>
		<winelist/>
		<zip>60657</zip>
	</ROW>
	


EOF;
        return simplexml_load_string($string);
    }
}
?>
