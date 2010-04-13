<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';



/**
 * Test for the UAE base class
 *
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
class importBaseUaeBarsRestaurantsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var importBaseUaeBarsRestaurants
     */
    protected $object;

    /**
     *
     * @var SimpleXMLElement
     */
    protected $xmlObj;

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

         // $poiCategoryObj = new PoiCategory();
       //   $poiCategoryObj[ 'name' ] = 'theatre-music-culture';
       //   $poiCategoryObj->save();

         // $this->xmlObj = new processNyXml( dirname(__FILE__).'/../../../data/uae_bars.xml' );
         // $this->xmlObj->setEvents('/body/event')->setVenues('/body/address');
          //$XmlObj =  $curlObj->pullXml('http://v7.test.timeoutdubai.com/', 'nokia/restaurants')->getXml();

        }
        catch( Exception $e )
        {
          echo $e->getMessage();
        }

       // $this->categoryMap = new CategoryMap();

        
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
     * Get an existing POi
     * @todo has no assertations.
     */
    public function testGetCurrentPois()
    {
        $this->createXMLObject();
        $this->createExistingUnchangedPoi();
        //$this->xmlObj = $this->getXMLString();

        //$this->object->getCurrentPois($this->xmlObj);
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testAddCommonElements().
     */
    public function testAddCommonElements()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }



    /**
     * Creates the object that is being tested
     */
    private function createXMLObject()
    {

        if($this->xmlObj == '')
        {
            $feed = new Curl('http://www.timeoutdubai.com/nokia/bars');
            $feed->exec();
                       
            $xmlObj = new ValidateUaeXmlFeed($feed->getResponse());
            $this->xmlObj = $xmlObj->getXmlFeed();
        }

        $this->object = new importBaseUaeBarsRestaurants( $this->xmlObj , $this->vendorObj);
        $this->object->importPoi($this->xmlObj);
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
           $this->existingPoiObj['vendor_poi_id'] = 20191;
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
<?xml version="1.0" encoding="utf-8"?>
            <outlets>
            <title>Time Out - Dubai Homepage </title>
            <pubDate>Sun, 21 Feb 2010 13:55:44 +0000</pubDate>
            <item>
            <title>Bartini</title>
            <id>12930</id>
            <link>http://www.timeoutdubai.com/bars/reviews/12930-bartini</link>
            <description>You can almost see what they were trying to do with Bartini</description>
            <location>Al Sufouh Road, Dubai Marina, Dubai</location>

            <telephone>04 399 5000</telephone>
            <pubDate>2010-01-26 13:07:33</pubDate>
            <type>Bars</type>
            <email></email>
            <timings>Open daily 6pm-1.30am</timings>
            <website>www.habtoorhotels.com</website>
            </item>
            <item>
            <title>SKYYline & Bar</title>
            <id>12805</id>

            <link>http://www.timeoutdubai.com/bars/reviews/12805-skyyline-bar</link>
            <description>It may not be the fanciest hotel in Dubai, but Festival City’s Crowne Plaza has one of its most popular bars</description>
            <location>Al Rebat Street, Festival City, Dubai</location>
            <telephone>04 701 2222</telephone>
            <pubDate>2010-01-18 10:36:09</pubDate>
            <type>Bars</type>
            <email>crowneplaza@cpdfc.ae</email>
            <timings>Open Mon-Sat 7pm till late</timings>
            <website>www.crowneplaza.com</website>
            </item>
            </outlets>
EOF;


        return simplexml_load_string($string);
    }
}
?>
