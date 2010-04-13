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
class ImportUaeRestaurantsTest extends PHPUnit_Framework_TestCase
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
     * Creates the object that is being tested
     */
    private function createObject()
    {

        if( $this->xmlObj == '' || $this->vendorObj == '' )
        {            
            $xmlFile = file_get_contents( TO_TEST_DATA_PATH . DIRECTORY_SEPARATOR . "dubai_restaurants_12-04-10.xml" );
            $xmlObj = new ValidateUaeXmlFeed( $xmlFile );
            
            $this->xmlObj = $xmlObj->getXmlFeed();
            $this->vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('dubai', 'en-US');
        }

        $this->object = new ImportUaeRestaurants( $this->xmlObj, $this->vendorObj );
        $this->object->importPois();

    }
}
?>
