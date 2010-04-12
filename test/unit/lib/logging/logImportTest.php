<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../bootstrap.php';

/**
 * Test class for logImport.
 * Generated by PHPUnit on 2010-02-16 at 13:47:54.
 */
class logImportTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var logImport
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');

        $this->vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('ny', 'en-US');
        $this->object = new logImport( $this->vendorObj, logImport::MOVIE );
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
     * Test that a new insert is incremented
     */
    public function testCountNewInsert()
    {
        $this->object->countNewInsert();
        $this->object->countNewInsert();
        $this->object->countNewInsert();
        $this->object->countNewInsert();
        $this->object->countNewInsert();
        $this->assertEquals('5', $this->object->getTotalInserts(), 'Increment the total by 5');
    }

    /**
     * Test that new updates are counted
     */
    public function testCountUpdate()
    {
       //The item is modified therefore log as an update
       $poi = ProjectN_Test_Unit_Factory::add( 'Poi' );
       $modifiedFieldsArray = array( 'openingtimes' => '8am4am', 'district' => 'ChinatownLittle Italy' );

       $this->object->addSuccess( $poi, 'update', $modifiedFieldsArray );

       $this->assertEquals('1', $this->object->getTotalUpdates(), 'Increment the total updates by one');
    }


    /**
     * Test that existing entries that don't need updating are counted
     */
    public function testCountExists()
    {
        $this->object->countExisting();
        $this->object->countExisting();
        $this->object->countExisting();

        $this->assertEquals('3', $this->object->getTotalExisting(), 'Increment the total existing by one');
    }

    /**
     * Tests the save functionality
     */
    public function testSave()
    {
        $this->object->countNewInsert();
        $this->object->countNewInsert();
        $this->object->countNewInsert();
        $this->object->countNewInsert();
        $this->object->countNewInsert();

        //Add an exception
        $poi = null;
        try
        {
            //a poi with phone number less than six digits will throw an Exception
            $poi = ProjectN_Test_Unit_Factory::get('Poi', array( 'vendor_poi_id' => NULL ) );
            $poi->save();
        }
        catch(Exception $error)
        {
            //Add two for testing
            $log = "Test exception";
            $this->object->addError($error, $poi, $log);

            $log = "Test exception";
            $this->object->addError($error, $poi, $log);
        }

        //The item is modified therefore log as an update
        $poi = ProjectN_Test_Unit_Factory::add( 'Poi' );
        $modifiedFieldsArray = array( 'openingtimes' => '8am4am', 'district' => 'ChinatownLittle Italy' );
        $this->object->addSuccess( $poi, 'update', $modifiedFieldsArray );

        //save to DB
        $this->object->save();

        //Test errrors
        $this->assertEquals(2, Doctrine::getTable('ImportLoggerError')->count(), 'Testing errors are in DB');

        $this->assertEquals(2, $this->object->getTotalErrors(), 'Fetching total errors');

        //Test successes
        $this->assertEquals(1, $results = Doctrine::getTable('ImportLoggerSuccess')->count(), 'Testing changes are in DB');

        //Test the logger
        $this->assertEquals(1, $results = Doctrine::getTable('ImportLogger')->count(), 'Testing logger is in DB');
    }

    /**
     * Test that exceptions are logged
     */
    public function testAddError()
    {
        try
        {
            //a poi with phone number less than six digits will throw an Exception
            $poi = ProjectN_Test_Unit_Factory::get('Poi', array( 'vendor_poi_id' => null ) );
            $poi->save();
        }
        catch(Exception $error)
        {
            $log = "Test exception";
            $this->object->addError($error, $poi, $log);
        }

        $this->assertEquals(1, Doctrine::getTable('ImportLoggerError')->count() );

        $importLoggerError = Doctrine::getTable('ImportLoggerError')->findOneById( 1 );
        $this->assertEquals( serialize( $poi )  , $importLoggerError[ 'serialized_object' ]);

        $this->assertNotEquals( serialize( ProjectN_Test_Unit_Factory::get( 'Poi' ) ), $importLoggerError[ 'serialized_object' ] );
    }

    /**
     * Test to see that a change is logged
     */
    public function testAddSuccessUpdateChange()
    {
        //The item is modified therefore log as an update
        $poi = ProjectN_Test_Unit_Factory::add( 'Poi' );
        $modifiedFieldsArray = array( 'openingtimes' => '8am4am', 'district' => 'ChinatownLittle Italy' );
        $this->object->addSuccess( $poi, 'update', $modifiedFieldsArray);

        $this->assertEquals(1, Doctrine::getTable('ImportLoggerSuccess')->count() );
    }

    /**
     * Test end Successful
     */
     public function testEndSuccessful()
     {
        $this->object->endSuccessful();
        $importLogger = Doctrine::getTable('ImportLogger')->findOneByStatus( 'success' );

        $this->assertEquals( 'success', $importLogger[ 'status' ] );
        $this->assertFalse( $this->object->checkIfRunning() );
     }

    /**
     * Test end Failed
     */
     public function testEndFailed()
     {
        $this->object->endFailed();
        $importLogger = Doctrine::getTable('ImportLogger')->findOneByStatus( 'failed' );

        $this->assertEquals( 'failed', $importLogger[ 'status' ] );
        $this->assertFalse( $this->object->checkIfRunning() );
     }

     /**
      * Test checkIfRunning
      */
      public function testCheckIfRunning()
      {
          $this->assertTrue( $this->object->checkIfRunning() );

          $this->object->endSuccessful();

          $this->assertFalse( $this->object->checkIfRunning() );
      }
}
?>
