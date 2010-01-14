<?php

require_once 'PHPUnit/Extensions/Database/TestCase.php';
require_once dirname( __FILE__ ).'/../../../bootstrap.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
spl_autoload_register(array('Doctrine', 'autoload'));

$configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'test', true);
new sfDatabaseManager($configuration);

/**
 * Test class for LondonVenues.
 * Generated by PHPUnit on 2010-01-06 at 15:07:35.
 */
class LondonImportTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var LondonImport
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    parent::setUp();
    $this->object = new LondonImport;
    //Doctrine::getTable( 'Poi' )->deleteByVendorCityAndLanguage( 'london', 'english' );
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    parent::tearDown();
  }

  /**
   * @todo re-implement after inspection of 4d fields
   *
   * loadFromSource( $limit, $offset ) should throws an exception if parameters
   * are not integers
   */
  public function testLoadFromSourceThrowsExceptionIfParamsNotIntegers()
  {
    try
    {
      $this->object->loadFromSource( 1.2 );
      $this->fail();
    }catch( LondonImportException $e ){}

    try
    {
      $this->object->loadFromSource( 1, 'foo' );
      $this->fail();
    }catch( LondonImportException $e ){}
  }

  /**
   * @todo reimplement after checking 4d feeds
   * 
   * Checks that results pulled from London Venues table has all required fields
   */
  public function testLoadFromSourceHasRequiredFields()
  {
    $this->assertTrue( true );
    return;
    
    $this->object->loadFromSource();
    $results = $this->object->getData();

    $this->assertGreaterThan( 0, count( $results ) );

    $this->assertArrayHasKey( 'poi_name', $results[ 0 ], 'Source has poi_name field' );
    $this->assertTrue( strlen( $results[ 0 ][ 'poi_name' ] ) > 0, 'Source poi_name is not blank' );

    $this->assertArrayHasKey( 'alternative_name', $results[ 0 ], 'Source has alternative name field' );
    $this->assertTrue( strlen( $results[ 0 ][ 'alternative_name' ] ) > 0, 'Source alternative name is not blank' );

    //should use fixtures for this...
    $this->assertArrayHasKey( 'street', $results[ 0 ], 'Source has street field'  );

    $this->assertArrayHasKey( 'city', $results[ 0 ], 'Source has city field' );
    $this->assertEquals( 'London', $results[ 0 ][ 'city' ], 'Source city field is London' );

    $this->assertArrayHasKey( 'country', $results[ 0 ], 'Source has country field' );
    $this->assertEquals( 'GBR', $results[ 0 ][ 'country' ], 'Source country field is GBR' );

    $results = $this->object->getAllFromSource();
    $this->assertArrayHasKey( 'country_code', $results[0] );

    $this->assertArrayHasKey( 'longitude', $results[ 0 ], 'Source has longitude field' );
    $this->assertGreaterThan( -180, $results[ 0 ][ 'longitude' ], 'Source longitude field over -180' );
    $this->assertLessThan( 180, $results[ 0 ][ 'longitude' ], 'Source longitude field less than 180' );

    $this->assertArrayHasKey( 'latitude', $results[ 0 ], 'Source has latitude field' );
    $this->assertGreaterThan( -180, $results[ 0 ][ 'latitude' ], 'Source latitude field over -180' );
    $this->assertLessThan( 180, $results[ 0 ][ 'latitude' ], 'Source latitude field less than 180' );

    //possibility of logic duplication?
    $this->assertArrayHasKey( 'vendor_category_names', $results[ 0 ], 'Source vendor_category field is not empty' );
    $this->assertType( PHPUnit_Framework_Constraint_IsType::TYPE_STRING, $results[0]['vendor_category_names'] );

    $this->assertArrayHasKey( 'vendor_poi_id', $results[ 0 ], 'Source has vendor_poi_id field' );
    //$this->assertType( PHPUnit_Framework_Constraint_IsType::TYPE_INT, $results[0]['vendor_poi_id'] );
    $this->assertNotNull( $results[ 0 ][ 'vendor_poi_id' ], 'Source has vendor_poi_id value' );

    $this->assertArrayHasKey( 'language', $results[ 0 ], 'Source has language field' );
    //$this->assertType( PHPUnit_Framework_Constraint_IsType::TYPE_INT, $results[0]['vendor_poi_id'] );
    $this->assertNotNull( $results[ 0 ][ 'language' ], 'Source has language value' );

    $this->assertArrayHasKey( 'public_transport', $results[ 0 ], 'Source has public_transport field' );

    $this->assertArrayHasKey( 'opening_times', $results[ 0 ], 'Source has opening_times field' );
  }

  /**
   * @todo re-implement after inspection of 4d fields
   *
   * data should be available after calling loadFromSource()
   */
  public function testLoadFromSourceCreatesData()
  {
    $this->assertTrue( true );
    return;
    $this->assertNull( $this->object->getData() );
    $this->object->loadFromSource();
    $this->assertTrue( is_array( $this->object->getData() ) );
  }

  /**
   * setData() should throw an ImportException if no data loaded
   */
  public function testSaveThrowsExceptionIfNoData()
  {
    try
    {
      $this->object->save();
      $this->fail();
    }
    catch( ImportException $e ){};
  }

  /**
   * @todo re-implement after inspection of 4d fields
   *
   * save() 
   */
  public function testSave()
  {
    return;
    $this->object->loadFromSource( 1 );
    $this->assertTrue( is_array( $this->object->getData() ) );
    $this->assertTrue( $this->object->save() );
    $this->assertEquals( 0, count( $this->object->getValidationErrors() ) );
  }

  /**
   * @todo find a way to inject error so that we can check validation errors
   *
   * getValidationErrors() should return an errors object
   * after a failed save()
   */
  public function testGetValidationErrors()
  {
    return;
    $this->object->loadFromSource();
    $this->assertTrue( is_array( $this->object->getData() ) );
    $this->assertFalse( $this->object->save() );
    
    $errors = $this->object->getValidationErrors();
    $this->assertType( PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $errors );
    $this->assertGreaterThan( 0, count( $errors ) );
    foreach( $errors as $errorStack )
    {
      $this->assertEquals('Doctrine_Validator_ErrorStack', get_class( $errorStack ) );
    }
  }
}
?>
