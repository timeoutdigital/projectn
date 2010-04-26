<?php
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';

/**
 * Test class for kk
 *
 * @package test
 * @subpackage doctrine.model.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class EventCategoryMappingTableTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var EventCategoryMappingTable
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    try {
      $pDB = Doctrine_Manager::connection(new PDO('sqlite::memory:'));
      Doctrine::createTablesFromModels( dirname(__FILE__).'/../../../../../lib/model/doctrine' );
      Doctrine::loadData('data/fixtures');

      $this->object = Doctrine::getTable('EventCategoryMapping');
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
    Doctrine_Manager::getInstance()->closeConnection(Doctrine_Manager::connection());
  }

  /**
   * tests the findByVendorId method
   */
  public function testFindByVendorId()
  {
    $EventCategoryies =  $this->object->findByVendorId( 1 );

    $this->assertGreaterThan( 1, count( $EventCategoryies ) );
    $this->assertTrue( $EventCategoryies instanceof Doctrine_Collection );    
  }
}
?>
