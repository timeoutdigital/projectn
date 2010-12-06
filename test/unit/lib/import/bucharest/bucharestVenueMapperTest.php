<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';


/**
 * Test for the Bucharest Venue Mapper
 *
 * @package test
 * @subpackage instabul.import.lib.unit
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 *
 * @version 1.0.0
 *
 * <b>Example</b>
 * <code>
 * </code>
 *
 */
class bucharestVenueMapperTest extends PHPUnit_Framework_TestCase
{

    private $vendor;
    private $params;
    
  public function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();
    Doctrine::loadData('data/fixtures');

    $this->vendor = Doctrine::getTable( 'Vendor' )->findOneByCity( 'bucharest' );
    $this->params = array( 'type' => 'poi', 'curl' => array( 'classname' => 'CurlMock', 'src' => TO_TEST_DATA_PATH . '/bucharest/venues.xml' ) );

  }

  public function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testMap()
  {
    $xml = simplexml_load_file( TO_TEST_DATA_PATH . '/bucharest/venues.xml' );
    // Import 
    $importer = new Importer();
    $importer->addDataMapper( new bucharestVenueMapper( $this->vendor, $this->params ) );
    $importer->run();

    // Assert
    $this->assertEquals( $xml->count(), Doctrine::getTable( 'Poi' )->count() );
    $firstPoi = Doctrine::getTable( 'Poi' )->findOneById( 1 );

    $this->assertEquals( 'Champions',  $firstPoi['poi_name'] );
    $this->assertEquals( 'Calea 13 septembrie 90', $firstPoi['street'] );
    $this->assertEquals( 'Bucharest', $firstPoi['city'] );
    $this->assertEquals( 'ROU',        $firstPoi['country'] );
    $this->assertEquals( ' 050713',    $firstPoi['zips'] );
    $this->assertEquals( '',           $firstPoi['email'] );
    $this->assertEquals( '44.423268', $firstPoi['latitude'] );
    $this->assertEquals( '26.072917', $firstPoi['longitude'] );
    $this->assertEquals( 'ÃŽmi tot "ameninÅ£" prietenii cÄƒ am sÄƒ merg la Marriott ÅŸi am sÄƒ comand hamburgerul de one pound ÅŸi am sÄƒ mÄƒ pozez muÅŸcÃ¢nd din el.', $firstPoi['short_description'] );
    $this->assertEquals( $this->getDescription(), $firstPoi['description'] );
    $this->assertEquals( '+40 2 1403 1917', $firstPoi['phone'] ); //021.403.19.17
    $this->assertEquals( 'Autobuz 385', $firstPoi['public_transport_links'] );
    $this->assertEquals( 'baruri, cluburi, bucuresti, sports bars, pubs', $firstPoi['keywords'] );
    $this->assertEquals('Cluburi si baruri', $firstPoi['VendorPoiCategory'][0]['name'] );
    $this->assertEquals( 1, $firstPoi['VendorPoiCategory']->count() );

    $secondPoi = Doctrine::getTable( 'poi' )->findOneById( 2 );
    $this->assertEquals( 'Hot Shots City Bar',  $secondPoi['name'] );
    $this->assertEquals( 'bar@hotshots.ro',  $secondPoi['email'] );
    $this->assertEquals( '+40 7 2435 5554', $secondPoi['phone'] ); //0724.355.554
  }

  private function getDescription()
  {
$description = <<<EOF
&lt;hr /&gt;
&lt;p&gt;ÃƒÂŽmi tot "ameninÃ…Â£" prietenii cÃ„Âƒ am sÃ„Âƒ merg la Marriott Ã…ÂŸi am sÃ„Âƒ
comand hamburgerul de one pound Ã…ÂŸi am sÃ„Âƒ mÃ„Âƒ pozez muÃ…ÂŸcÃƒÂ¢nd din el.
FireÃ…ÂŸte cÃ„Âƒ nu am fÃ„Âƒcut asta, dar e un (alt) bun motiv sÃ„Âƒ merg mai
des la Champions. Oriunde ai sta se vede foarte bine la tv, care
sunt multe, cu diferite diagonale Ã…ÂŸi la fel de multe transmisiuni.
Eu nu am mÃƒÂ¢ncat ceva sÃ„Âƒ nu-mi placÃ„Âƒ, dar fiÃ…Â£i atenÃ…Â£i: toate
porÃ…Â£iile sunt respectabile, so you need exercise. Before &amp;
after, my friends!&lt;/p&gt;
&lt;p&gt; &lt;/p&gt;
EOF;
    return html_entity_decode( $description, ENT_QUOTES, 'UTF-8' );
  }
}
