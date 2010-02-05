<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../lib/import/dubai/dubaiImportBars.class.php';
require_once dirname(__FILE__).'/../../../../../lib/processXml.class.php';
require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
spl_autoload_register(array('Doctrine', 'autoload'));


/**
 * Test class for logger.
 *
 * @package test
 * @subpackage import.lib.unit
 *
 * @author Timmy Bowler <timbowler@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 * @todo Finish as the feeds are not working from Dubai
 *
 *
 */
class dubaiImportBarsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var dubaiImportBars
     */
    protected $barObject;
    protected $restaurantObj;

    protected $barXmlObj;
    protected $restaurantXmlObj;

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


 //         $poiCategoryObj = new PoiCategory();
 //         $poiCategoryObj[ 'name' ] = 'bar-pub';
 //         $poiCategoryObj->save();

          //Regression tests
          $this->curlObj = new curlImporter();
          //$this->barXmlObj =  $this->curlObj->pullXml('http://www.timeoutdubai.com/', 'nokia/bars')->getXml();
           $this->restaurantXmlObj =  $this->curlObj->pullXml('http://www.timeoutdubai.com/', 'nokia/restaurants')->getXml();
          
          //$this->barObject = new dubaiImportBars( $this->barXmlObj, $this->vendorObj, 'bar' );
          $this->restaurantObj =  new dubaiImportBars( $this->restaurantXmlObj, $this->vendorObj, 'restaurant' );

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
     * Regression test for bars feed.
     */
    public function testBarsFeed()
    {
       //$this->assertType('object', $this->restaurantXmlObj);
    }


    public function testImportPoi()
    {
      //  $this->object->importBars();
    }

    public function testAddRestaurantPoi()
    {
        foreach($this->restaurantXmlObj as $poi)
        {
             if($poi->title != '')
            {
                $poiReturned = $this->restaurantObj->addRestaurantPoi($poi);
                $this->assertType('array', $poiReturned, 'Poi added') ;
           
                break;
            }
        }

        
        
    }
}
?>
