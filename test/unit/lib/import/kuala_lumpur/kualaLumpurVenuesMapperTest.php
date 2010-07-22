<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test of Kuala Lumpur Venues mapper
 *
 * @package test
 * @subpackage london.import.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class kualaLumpurVenuesMapperTest extends PHPUnit_Framework_TestCase
{
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    $importer = new Importer();

    $this->vendor = ProjectN_Test_Unit_Factory::add( 'Vendor', array( 
      'city'=>'kuala lumpur', 
      'language'=>'en',
      'inernational_dial_code' => '+60',
      ) );

    $this->xml = simplexml_load_file( TO_TEST_DATA_PATH . '/kuala_lumpur_venues.xml' );

    $importer->addDataMapper( new kualaLumpurVenuesMapper( $this->vendor, $this->xml ) );
    $importer->run();

    $this->pois = Doctrine::getTable( 'Poi' )->findAll();
  }

  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  /**
   * Check that the latitude & longitude values won't fail Nokia validation.
   */
  public function testLatLongAroundTheRightWay()
  {
      foreach( $this->pois as $poi )
      {
          $this->assertLessThan( 90, $poi['latitude'] );
          $this->assertGreaterThan( -90, $poi['latitude'] );

          $this->assertLessThan( 180, $poi['longitude'] );
          $this->assertGreaterThan( -180, $poi['longitude'] );
      }
  }

  public function testExtractInfoFromStreetField()
  {
    // Someone didn't finish waht they were doing.
    $this->markTestIncomplete();
    
    $file = file_get_contents(  TO_TEST_DATA_PATH . '/kl_street_fields.csv' );
    $array = explode( '"'."\n".'"', $file );

//    foreach( $array as $k => $val )
//        $array[ $k ] = str_replace( "\n", "", $val );

    foreach( $array as $k => $val )
        $array[ $k ] = preg_replace( "/(,?\s?(KL))$/m", "", $val );

    print_r( $array[538] . PHP_EOL );
    die;
  }

  public function testMapping()
  {
    $this->assertEquals( count( $this->xml->venueDetails ),
                         count( $this->pois ),
                         'Should have same number of Pois in db as in xml.');

    $this->assertEquals( 101.746359,
                         $this->pois[0]['longitude'],
                         'Checking latitude'
                         );

    $this->assertEquals( 3.209707,
                         $this->pois[0]['latitude'],
                         'Checking longitude'
                         );

    $this->assertEquals( 'xyz@foo.bar',
                         $this->pois[0]['email'],
                         'Checking email'
                         );

    $this->assertEquals( 'http://www.tmsart.com.my',
                         $this->pois[0]['url'],
                         'Checking url'
                         );

    $this->assertEquals( '+60 3 4107 5154',
                         $this->pois[0]['phone'],
                         'Checking phone'
                         );

    $this->assertEquals( '+60 3 4107 5154',
                         $this->pois[0]['phone'],
                         'Checking phone'
                         );

    $this->assertEquals( 'No 301, Jalan Bandar 11, Taman Melawati, KL',
                         $this->pois[0]['geocode_look_up'],
                         'Checking geocode_look_up'
                         );
  }

  public function testVendorPoiCategory()
  {
    $this->assertEquals( 'Art | Gallery',
                         $this->pois[0]['VendorPoiCategory'][0]['name'],
                         'Checking vendor poi category'
                         );

    $this->assertEquals( 'Food | European',
                         $this->pois[1]['VendorPoiCategory'][0]['name'],
                         'Checking vendor poi category'
                         );
  }

  public function testImage()
  {
    $this->assertEquals( 'http://www.timeoutkl.com/uploadfiles/image/Venues/Art/Big/bigimg_TMSArtGallery.jpg',
                          $this->pois[0]['PoiMedia'][0]['url'],
                          'Checking poi media'
                          );
  }

  public function testDescriptions()
  {
    $this->assertEquals( 'Hankering for real German food to accompany that cold beer of yours? Then Stadt is the place just for you....',
                          $this->pois[1]['short_description'],
                          'Checking short description'
                         );
    $desc = <<<EOF
<p>Hankering for real German food to accompany that cold beer of yours? Then Stadt is the place just for you. <br />
<br />
Stadt specializes in authentic German cuisine, featuring original recipe of German favourites such as German Grilled steak, sausages, and their specialty, crispy pork knuckle. <br />
<br />
Stadt also serves a wonderful selection of German and local beers. So, beers and meat, heavenly, ain't it so?</p>
<p><strong>Stadt Puchong</strong>: <em>(see side bar)</em></p>
<p><strong>Stadt Kepong</strong>: <em>No 2, Jalan Metro Perdana 8, Taman Usahawan Kepong, Kepong Utara, 52100 KL. 03 6250 1016 </em></p>
EOF;
    $this->assertEquals( 'Hankering for real German food to accompany that cold beer of yours? Then Stadt is the place just for you....',
                          $this->pois[1]['short_description'],
                          'Checking short description'
                         );
  }
}
