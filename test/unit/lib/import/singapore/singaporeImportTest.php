<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ).'/../../../bootstrap.php';

/**
 * Test class for singaporeImport.
 * Generated by PHPUnit on 2010-01-28 at 13:53:13.
 */
class singaporeImportTest extends PHPUnit_Framework_TestCase {
  /**
   * @var singaporeImport
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp() {

    ProjectN_Test_Unit_Factory::createSqliteMemoryDb();

    $this->object = new singaporeImport;
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown() {
  }


  public function test()
  {
    
  }

}
?>
