<?php
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../../bootstrap.php';
spl_autoload_register(array('Doctrine', 'autoload'));

/**
 * Test class for Guise
 *
 * @package test
 * @subpackage guisable.behaviours.model.lib.unit
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 * @todo make this test independent of the projectn yaml
 *
 */
class GuiseTest extends PHPUnit_Framework_TestCase
{

  protected function setUp()
  {
      ProjectN_Test_Unit_Factory::createDatabases();
      Doctrine::loadData( 'data/fixtures' );
  }

  protected function tearDown()
  {
      ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testRelation()
  {
     $originalTable = Doctrine::getTable( 'Vendor' );
     $guiseTable = Doctrine::getTable( 'VendorGuise' );

     $originalTableRelations = $originalTable->getRelation( 'Guise' );
     $guiseTableRelations = $guiseTable->getRelation( 'Vendor' );

     $this->assertEquals( 'id', $originalTableRelations->getLocal() );
     $this->assertEquals( 'id', $originalTableRelations->getForeign() );

     $this->assertEquals( 'id', $guiseTableRelations->getLocal() );
     $this->assertEquals( 'id', $guiseTableRelations->getForeign() );

     $this->assertEquals( 1, $originalTableRelations->getType() );
     $this->assertEquals( 0, $guiseTableRelations->getType() );
  }

  public function testTableDefinition()
  {
     $originalTable = Doctrine::getTable( 'Vendor' );
     $guiseTable = Doctrine::getTable( 'VendorGuise' );

     $originalTableColumns = $originalTable->getColumns();
     $guiseTableColumns = $guiseTable->getColumns();
     
     $columnDifferences = array_diff_assoc( $originalTableColumns, $guiseTableColumns );

     //make sure we got all the columns of the original table
     $this->assertEquals( 0, count( $columnDifferences ), 'found columns which are not present in the guised table' );

     //make sure we got those constraints and things removed
     foreach ( $guiseTableColumns as $column => $definition ) {
        if ( isset( $definition['autoincrement'] ) )
        {
            $this->fail('found autoincrement in guise table');
        }
        if ( isset( $definition['sequence'] )  )
        {
            $this->fail('found sequence in guise table');
        }
        if ( isset( $definition['unique'] )  )
        {
            $this->fail('found unique in guise table');
        }
     }

     //check identifier columns
     //make sure existing primary keys are carried over
     $originalIdentifierColumNames = $originalTable->getIdentifierColumnNames();
     $guiseIdentifierColumNames = $guiseTable->getIdentifierColumnNames();     
     $identifierDifferences = array_diff_assoc( $originalIdentifierColumNames, $guiseIdentifierColumNames );             
     $this->assertEquals( 0, count( $identifierDifferences ), 'found difference in identifier columns' );

     //check guise column
     $guiseColumnDefinition = $guiseTable->getColumnDefinition( 'guise' );
     $this->assertTrue( is_array( $guiseColumnDefinition ), 'guise column does not seem to exist' );
     $this->assertEquals( 3, count( $guiseColumnDefinition ), 'something is wrong with the guise column definition' );
     $this->assertTrue( isset( $guiseColumnDefinition[ 'primary' ] ) && $guiseColumnDefinition[ 'primary' ] == 'true', 'something is wrong with the guise column definition' );
     $this->assertTrue( isset( $guiseColumnDefinition[ 'type' ] ) && $guiseColumnDefinition[ 'type' ] == 'string', 'something is wrong with the guise column definition' );
     $this->assertTrue( isset( $guiseColumnDefinition[ 'length' ] ) && $guiseColumnDefinition[ 'length' ] == '20', 'something is wrong with the guise column definition' );
  }

  public function testTimestampableOption()
  {
      $guise = $this->createAGuise();

      $this->assertEquals( date( 'Y-m-d' ), date( 'Y-m-d', strtotime( $guise['created_at'] ) ) );
      $this->assertEquals( date( 'Y-m-d' ), date( 'Y-m-d', strtotime( $guise['updated_at'] ) ) );
  }

  public function testGetGuise()
  {
      $this->createAGuise();
      $vendorTable = Doctrine::getTable( 'Vendor' );
      $vendor = $vendorTable->findOneById( 1 );

      $guise = new Guise();    
      $guise->initialize( $vendorTable );

      $this->assertEquals( $vendor['city'], 'ny' );

      $guisedVendor = $guise->getGuise( $vendor, 'Shenzhen' );

      $this->assertEquals( $guisedVendor['city'], 'hong kong' );
  }

  private function createAGuise()
  {
      ProjectN_Test_Unit_Factory::add('Vendor');

      $guise = new VendorGuise();
      $guise['city'] = 'hong kong';
      $guise['language'] = 'en-HK';
      $guise['time_zone'] = 'Asia/Hong_Kong';
      $guise['inernational_dial_code'] = '+755';
      $guise['airport_code'] = 'HKG';
      $guise['country_code'] = 'hk';
      $guise['country_code_long'] = 'HKG';
      $guise['geo_boundries'] = '22.153247833252;113.837738037109;22.5597801208496;114.434761047363';
      $guise['id'] = 1;
      $guise['guise'] = 'Shenzhen';
      $guise->save();

      return $guise;
  }

}
