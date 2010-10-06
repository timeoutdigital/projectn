<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';
/**
 * Test of beijing Venue Mapper
 *
 * @package test
 * @subpackage beijing.import.lib.unit
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */

class beijingZHFeedPoiMapperTest extends PHPUnit_Framework_TestCase
{

    protected $vendor;
  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    Doctrine::loadData('data/fixtures');

    $this->vendor = Doctrine::getTable( 'Vendor' )->findOneByCity('beijing_zh');
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
      $this->pdoDB = null;
      ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testMapVenue()
  {
      $params = array( 'datasource' => array( 'classname' => 'CurlMock', 'url' => TO_TEST_DATA_PATH . '/beijing/beijing_zh.venue.xml', 'username' => 'tolondon' , 'password' => 'to3rjk&e*8dsfj9' ) );

      $dataMapper = new beijingZHFeedVenueMapper( $this->vendor, $params );

      $importer = new Importer();
      $importer->addDataMapper($dataMapper);
      $importer->run();

      // Get all the POI's
      $pois =  Doctrine::getTable( 'Poi' )->findAll();

      $this->assertEquals( 6, $pois->count(), 'There should be 6 Pois added' );

      // get 1 and Test
      $poi = $pois[0];

      $this->assertEquals( '3', $poi['vendor_poi_id'], 'Invalid vendor Poi ID');
      $this->assertEquals( '俏江南', $poi['name'], 'Wrong Poi Name');
      $this->assertEquals( '1号', $poi['house_no'], 'Wrong Poi House No');
      $this->assertEquals( '东城区东长安街', $poi['street'], 'Wrong Poi street');
      $this->assertEquals( '东城', $poi['district'], 'Wrong Poi district');
      $this->assertEquals( '100010', $poi['zips'], 'Wrong Poi zips');
      $this->assertEquals( '116.414518', $poi['latitude'], 'Wrong Poi latitude');
      $this->assertEquals( '39.909807', $poi['longitude'], 'Wrong Poi longitude');
      $this->assertEquals( '东城区东长安街1号东方广场B1楼BB88号', $poi['short_description'], 'Wrong Poi short_description');
      $this->assertEquals( '东城区东长安街1号东方广场B1楼BB88号', $poi['description'], 'Wrong Poi description');

      $this->assertEquals( '+86 8 518 6971', $poi['phone'], 'Wrong Poi Phone Number');

      // timeout URL
      $this->assertEquals( 1, $poi['PoiProperty']->count() );
      $this->assertEquals( 'http://www.timeoutcn.com/Articles_11_14.htm', $poi['PoiProperty'][0]['value'] );

      // assert another
      $poi = $pois[4];

      $this->assertEquals( '7', $poi['vendor_poi_id'], 'Invalid vendor Poi ID');
      $this->assertEquals( '玉', $poi['name'], 'Wrong Poi Name');
      $this->assertEquals( '甲83号', $poi['house_no'], 'Wrong Poi House No');
      $this->assertEquals( '朝阳区建国路', $poi['street'], 'Wrong Poi street');
      $this->assertEquals( '朝阳', $poi['district'], 'Wrong Poi district');
      $this->assertEquals( '朝阳区建国路甲83号丽思卡尔顿酒店', $poi['additional_address_details'], 'Wrong Poi additional_address_details');
      $this->assertEquals( '100020', $poi['zips'], 'Wrong Poi zips');
      $this->assertEquals( '116.5416677', $poi['latitude'], 'Wrong Poi latitude');
      $this->assertEquals( '39.9081711', $poi['longitude'], 'Wrong Poi longitude');
      $this->assertEquals( '以前，酒店里的中餐馆都给人又贵又不靠谱的印象，从半岛的凰庭、君悦的长安一号开始，酒店里的中餐馆也有了...', $poi['short_description'], 'Wrong Poi short_description');
      $this->assertStringStartsWith( '以前，酒店里的中餐馆都给', $poi['description'], 'Wrong Poi description');

      $this->assertEquals( '+86 5 908 8888', $poi['phone'], 'Wrong Poi Phone Number');

      // timeout URL
      $this->assertEquals( 1, $poi['PoiProperty']->count() );
      $this->assertEquals( 'http://www.timeoutcn.com/Articles_12_15.htm', $poi['PoiProperty'][0]['value'] );
      
  }

  public function _testFeedDownload()
  {
        $cookieFile = tempnam ("/tmp", "CURLCOOKIE");

      // Get the FORM
        $curl = new Curl( "http://www.timeoutcn.com/Account/Login.aspx?ReturnUrl=/admin/n/london/Default.aspx" );
        $curl->setCurlOption(CURLOPT_COOKIEJAR, $cookieFile);
        $curl->exec();
        $loginPageHTML = $curl->getResponse();

        // Remove all news lines, as it's easier to Preg match
        $newlines = array("\t","\n","\r","\x20\x20","\0","\x0B");
        $loginPageHTML = str_replace($newlines, "", html_entity_decode($loginPageHTML));

        // Get the Inputs
        preg_match_all("|<input.*/>|U",$loginPageHTML, $Loginfields);

        // Extract the name & value of the Fields
        $fields = array( '__EVENTTARGET' => '', '__EVENTARGUMENT' => '', '__EVENTVALIDATION' => '', 'ctl00$CM$Login1$UserName' => '', 'ctl00$CM$Login1$Password' => '', 'ctl00$CM$Login1$LoginButton' => '', 'ctl00$CM$Login1$RememberMe' => '' );
        foreach( $Loginfields[0] as $input )
        {
            preg_match( '/<input.*?name\\s*=\\s*"?([^\\s>"]*).*?value\\s*=\\s*"?([^\\s"]*).*?/i', $input, $match);
            if( count($match) == 3 )
            {
                $fields[ $match[1] ] = $match[2];
            }
        }

        // Set username / Password
        $fields[ 'ctl00$CM$Login1$UserName' ] = 'tolondon';
        $fields[ 'ctl00$CM$Login1$Password' ] = 'to3rjk&e*8dsfj9';

        // Overriders
        $fields[ '__EVENTTARGET' ] = 'ctl00$CM$Login1$LoginButton';
        $fields[ '__EVENTARGUMENT' ] = '';
        
        echo 'Downloading XML' . PHP_EOL;
        // Send POST request
        $curl = new Curl( "http://www.timeoutcn.com/Account/Login.aspx?ReturnUrl=/admin/n/london/Default.aspx", $fields, "POST");
        $curl->setCurlOption(CURLOPT_COOKIEFILE, $cookieFile);
        $curl->exec();

        var_dump( $curl->getHeader() );
        unlink($cookieFile);
  }
}