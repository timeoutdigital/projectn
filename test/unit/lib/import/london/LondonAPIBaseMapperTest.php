
<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test class for London API Bars And Pubs Mapper.
 *
 * @package test
 * @subpackage london.import.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 *
 * @version 1.0.1
 */
class LondonAPIBaseMapperTest extends PHPUnit_Framework_TestCase
{

    private $vendor;
  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    $this->vendor = ProjectN_Test_Unit_Factory::add( 'Vendor', array(
      'city' => 'london',
      'language' => 'en-GB',
      ) );
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
   * @see unfuddled ticket #253
   */
  public function testPoiUrlIsNotTimeoutUrlAndTimeoutLinkIsStoredAsAProperty()
  {
    $mock = new MockLondonAPIBaseMapper($this->vendor , array() );
    $poi = new Poi();
    $xml = simplexml_load_string( '<xml><url>http://www.google.com</url><webUrl>http://www.timeout.com</webUrl><lat>0.5</lat><lng>0.5</lng><postcode>E84QY</postcode></xml>' ); //need these nasty lat lng tags until we mock reverseGeocoder
    $mock->map( $poi, $xml );

    $this->assertEquals( 'http://www.google.com', $poi['url'] );
    $this->assertEquals( 'Timeout_link', $poi['PoiProperty'][0]['lookup'] );
    $this->assertEquals( 'http://www.timeout.com', $poi['PoiProperty'][0]['value'] );
  }

  /**
   * @see unfuddled ticket #250
   */
  public function testCommaLondonNotInEndOfAddressField()
  {
    $mock = new MockLondonAPIBaseMapper( $this->vendor , array()  );

    $poi = new Poi();
    $xml = simplexml_load_string( '<xml><address>foo, London </address><lat>51.5079</lat><lng>-0.3049</lng><postcode>E84QY</postcode></xml>' ); //need these nasty lat lng tags until we mock reverseGeocoder
    $mock->map( $poi, $xml );
    $this->assertEquals( 'foo', $poi['street'], "Street cannot end with a space or a comma." );

    $poi = new Poi();
    $xml = simplexml_load_string( '<xml><address>foo, </address><lat>51.5079</lat><lng>-0.3048</lng><postcode>E84QY</postcode></xml>' ); //need these nasty lat lng tags until we mock reverseGeocoder
    $mock->map( $poi, $xml );
    $this->assertEquals( 'foo', $poi['street'], "Street cannot end with a space or a comma." );

    $poi = new Poi();
    $xml = simplexml_load_string( '<xml><address>foo </address><lat>51.5079</lat><lng>-0.3048</lng><postcode>E84QY</postcode></xml>' ); //need these nasty lat lng tags until we mock reverseGeocoder
    $mock->map( $poi, $xml );
    $this->assertEquals( 'foo', $poi['street'], "Street cannot end with a space or a comma." );

    $poi = new Poi();
    $xml = simplexml_load_string( '<xml><address>56 Artillery Lane, London, </address><lat>51.5079</lat><lng>-0.3048</lng><postcode>E84QY</postcode></xml>' ); //need these nasty lat lng tags until we mock reverseGeocoder
    $mock->map( $poi, $xml );
    $this->assertEquals( '56 Artillery Lane', $poi['street'], "Street cannot end with a space or a comma." );
  }

  public function testCriticsChoiceFlagIsSavedAsNormalisedProperty()
  {
    $mock = new MockLondonAPIBaseMapperCriticsChoice( $this->vendor , array() );
    $mock->setImporter( new Importer() );
    $poi = new Poi();
    $xml = simplexml_load_string( '
      <xml>
        <details>
          <detail name="Facilities">foo</detail>
          <detail name="Flags">Critic\'s choice</detail>
        </details>
        <lat>51.5079</lat><lng>-0.3048</lng>
      </xml>' );
    $mock->map( $poi, $xml );

    $this->assertEquals( 2, $poi['PoiProperty']->count() );

    $criticsChoiceCount = 0;
    foreach( $poi['PoiProperty'] as $property )
    {
      //var_dump( $property['lookup'] );
      if( $property['lookup'] == 'Critics_choice' )
        $criticsChoiceCount++;
    }

    $this->assertEquals( 1, $criticsChoiceCount, 'Should have Critics_choice property');
  }
}

class MockLondonAPIBaseMapper extends LondonAPIBaseMapper
{
  public function __construct( Vendor $vendor, array $params )
  {
    parent::__construct( $vendor, $params );
  }
  public function map( Poi $poi, SimpleXMLElement $xml )
  {
    $this->mapCommonPoiMappings( $poi, $xml );
  }
  public function getDetailsUrl(){}
  public function getApiType(){ return 'restaurant'; }
  public function doMapping( SimpleXMLElement $xml ){}
}
class MockLondonAPIBaseMapperCriticsChoice extends LondonAPIBaseMapper
{
  public function __construct( Vendor $vendor, array $params )
  {
    parent::__construct( $vendor, $params );
  }
  public function map( Poi $poi, SimpleXMLElement $xml )
  {
    foreach( $this->getDetails( $xml ) as $detail )
    {
      $this->addDetailAsProperty( $poi, $detail );
    }
  }
  public function getDetailsUrl(){}
  public function getApiType(){}
  public function doMapping( SimpleXMLElement $xml ){}
}
