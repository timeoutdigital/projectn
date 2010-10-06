<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test class for IstanbulMapper.
 >*
 * @package test
 * @subpackage instabul.import.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 *
 * @version 1.0.1
 */
class IstanbulVenueMapperTest extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    ProjectN_Test_Unit_Factory::add( 'Vendor', array(
      'city' => 'istanbul',
      'inernational_dial_code' => '+90',
      'language' => 'tr',
      'country_code' => 'tr',
      'country_code_long' => 'TUR',
    ) );
  }

  public function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testMap()
  {
    $importer = new Importer();
    $xml = simplexml_load_file( TO_TEST_DATA_PATH . '/istanbul/venues.xml' );
    $importer->addDataMapper( new istanbulVenueMapper( $xml ) );
    $importer->run();

    $this->assertEquals( Doctrine::getTable( 'poi' )->count(), $xml->count() );
    $firstPoi = Doctrine::getTable( 'poi' )->findOneById( 1 );
    
    $this->assertEquals( 'Adem Baba',  $firstPoi['name'] );
    $this->assertEquals( '2',          $firstPoi['house_no'] );
    $this->assertEquals( 'Satış Meydanı Sk', 
                                       $firstPoi['street'] );
    $this->assertEquals( 'Arnavutköy', $firstPoi['district'] );
    $this->assertEquals( 'TUR',        $firstPoi['country'] );
    $this->assertEquals( '',           $firstPoi['zips'] );
    $this->assertEquals( '',           $firstPoi['email'] );
    $this->assertEquals( 'http://www.adembaba.com', 
                                       $firstPoi['url'] );
    $this->assertEquals( '+90 212 263 2933',
                                       $firstPoi['phone'] );
    $this->assertEquals( '',           $firstPoi['fax'] );
    $this->assertEquals('http://www.timeoutistanbul.com/p111/yemeicme/adem_baba', 
                                       $firstPoi->getTimeoutLinkProperty() );
    $this->assertEquals('Yeme&İçme – Balık Restoranları', 
                                       $firstPoi['VendorPoiCategory'][0]['name'] );
    $this->assertEquals(1,             $firstPoi['VendorPoiCategory']->count() );

    $secondPoi = Doctrine::getTable( 'poi' )->findOneById( 2 );
    $this->assertEquals('http://www.timeoutistanbul.com/images/uploadedimages/standart/10064.jpg',
                                       $secondPoi['PoiMedia'][0]['url'] );
    $this->assertEquals('4',           $secondPoi['rating'] );
    
    
    $poiWithMSWordProblem = Doctrine::getTable( 'poi' )->findOneByVendorPoiId( 4632 );
    $this->assertEquals( 'Vizyon Görüntüleme Merkezi',  $poiWithMSWordProblem['name'] );
    $this->assertEquals( null,          $poiWithMSWordProblem['house_no'] );
    $this->assertEquals( 'Hacı Emin Efendi Sokak Seçkin Apartmanı 48 , Nişantaşı',
                                       $poiWithMSWordProblem['street'] );
$description = <<<EOF
TR
X-NONE
AR-SA

/* Style Definitions */
table.MsoNormalTable
{mso-style-name:Table Normal;
mso-tstyle-rowband-size:0;
mso-tstyle-colband-size:0;
mso-style-noshow:yes;
mso-style-priority:99;
mso-style-qformat:yes;
mso-style-parent:;
mso-padding-alt:0cm 5.4pt 0cm 5.4pt;
mso-para-margin-top:0cm;
mso-para-margin-right:0cm;
mso-para-margin-bottom:10.0pt;
mso-para-margin-left:0cm;
line-height:115%;
mso-pagination:widow-orphan;
font-size:11.0pt;
font-family:Calibri,sans-serif;
mso-ascii-font-family:Calibri;
mso-ascii-theme-font:minor-latin;
mso-fareast-font-family:Times New Roman;
mso-fareast-theme-font:minor-fareast;
mso-hansi-font-family:Calibri;
mso-hansi-theme-font:minor-latin;}

Pazar hariç her gün 10.00-18.00 arasında açık.
EOF;

    $this->assertEquals( $description,          $poiWithMSWordProblem['description'] );


  }
}
