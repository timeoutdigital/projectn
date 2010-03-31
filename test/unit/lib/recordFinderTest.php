<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../bootstrap.php';

/**
 * Test class for the record checker
 *
 * @package test
 * @subpackage lib.unit
 *
 * @author Clarence Lee <timbowler@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class recordFinderTest extends PHPUnit_Framework_TestCase
{
  protected function setUp()
  {    
    ProjectN_Test_Unit_Factory::destroyDatabases();
    ProjectN_Test_Unit_Factory::createDatabases();
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testReturnsNullIfNoRecordsFound()
  {
    $aRecord = $this->createARecord();
    $this->assertEquals( 0, Doctrine::getTable( 'VendorEventCategory' )->count() );

    $recordFinder = new recordFinder();

    $equivalentRecord = $recordFinder->findEquivalentOf( $aRecord )
                                     ->comparingAllFieldsExcept( 'id' )
                                     ->go();

    $this->assertTrue( is_null( $equivalentRecord ), 'No matches should be found.' );
  }

  public function testFindsRecordUsingAllFieldsExceptThoseSpecified()
  {
    $this->putAVendorEventCategoryRecordInDatabase();
    $record = $this->createAnotherVendorEventCategoryRecordWithTheSameFields();

    $recordFinder = new recordFinder();
    $equivalentRecord = $recordFinder->findEquivalentOf( $record )
                                     ->comparingAllFieldsExcept( 'id' )
                                     ->go();
    
    $this->assertTrue( $equivalentRecord instanceof VendorEventCategory );
  }

  public function testFindEquivalentRecordDoesntFindIdenticalRecord()
  {
    $this->putAVendorEventCategoryRecordInDatabase();
    $record = Doctrine::getTable( 'VendorEventCategory' )->findOneById( 1 );

    $recordFinder = new recordFinder();
    $equivalentRecord = $recordFinder->findEquivalentOf( $record )
                                     ->comparingAllFieldsExcept( 'id' )
                                     ->go();

    $this->assertNull( $equivalentRecord );
  }

  public function testGetUniqueRecord()
  {
    $this->putAVendorEventCategoryRecordInDatabase();
    $record = Doctrine::getTable( 'VendorEventCategory' )->findOneById( 1 );

    $recordFinder = new recordFinder();
    $equivalentRecord = $recordFinder->findEquivalentOf( $record )
                                     ->comparingAllFieldsExcept( 'id' )
                                     ->getUniqueRecord();

    $this->assertEquals( $equivalentRecord->toArray(), $record->toArray() );

    $record = $this->createAnotherVendorEventCategoryRecordWithTheSameFields();
    $record->save();

    $recordFinder = new recordFinder();
    $equivalentRecord = $recordFinder->findEquivalentOf( $record )
                                     ->comparingAllFieldsExcept( 'id' )
                                     ->getUniqueRecord();

    $this->assertEquals( $equivalentRecord->toArray(), $record->toArray() );
  }

  private function createARecord()
  {
    return ProjectN_Test_Unit_Factory::get( 'VendorEventCategory' );
  }

  private function putAVendorEventCategoryRecordInDatabase()
  {
    ProjectN_Test_Unit_Factory::add( 'VendorEventCategory' );
  }

  private function createAnotherVendorEventCategoryRecordWithTheSameFields()
  {
    return ProjectN_Test_Unit_Factory::get( 'VendorEventCategory' );
  }

}
