<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../bootstrap.php';

/**
 * Test class for ImportLogger.
 */
class ImportLoggerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');
        ImportLogger::getInstance()->enabled( true );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        ImportLogger::getInstance()->unsetSingleton();
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }
    /**
     * This test is to makesure that data fixture is loaded and
     * ImportLogger can find the default unknown vendor
     */
    public function testNothing()
    {
        // this test us written to give enought time for the bootstrap to loard fixtures
    }


    public function testSaveRecordComputeChangesAndLogWithInvalidRecordType()
    {
        ImportLogger::getInstance()->progressive( true );

        $record = array();
        ImportLogger::saveRecordComputeChangesAndLog( $record );
        
        $logger = Doctrine::getTable("LogImport")->findAll()->getFirst();
        $logger->refresh( true ); // Call this to clear Doctrine cache

        $this->assertEquals( 1, $logger->LogImportError->count(), "Expecting An Error (record not subclass of Doctrine_Record)" );
        $this->assertEquals( 'ImportLoggerException', $logger->LogImportError[0]['exception_class'], "Expecting exception_class to be ImportLoggerException" );
        $this->assertEquals( 'Record Passed To ImportLogger::saveRecordComputeChangesAndLog is not extended from Doctrine_Record', $logger->LogImportError[0]['message'] );
        //print_r( $logger->toArray() );
    }

    public function testSaveRecordComputeChangesAndLogWithRecordValidationErrors()
    {
        ImportLogger::getInstance()->progressive( true );

        $record = new Poi;
        ImportLogger::saveRecordComputeChangesAndLog( $record );

        $logger = Doctrine::getTable("LogImport")->findAll()->getFirst();
        $logger->refresh( true ); // Call this to clear Doctrine cache

        $this->assertGreaterThan( 0, $logger->LogImportError->count(), "Expecting An Error (validation error, geocode error etc.)" );
        //print_r( $logger->toArray() );
    }

    public function testSaveRecordComputeChangesAndLogCorrectlyFindsModifications()
    {
        ImportLogger::getInstance()->progressive( true );

        $record = Doctrine::getTable("Vendor")->findOneByCity( "moscow" );
        $record['city'] = "Fantasia";
        $record['language'] = "zz";
        
        ImportLogger::saveRecordComputeChangesAndLog( $record );

        $logger = Doctrine::getTable("LogImport")->findAll()->getFirst();
        $logger->refresh( true ); // Call this to clear Doctrine cache

        $this->assertGreaterThan( 0, $logger->LogImportChange->count(), "Expecting A Change (moscow->fantasia)" );
        $this->assertEquals( "Updated Fields: \ncity: 'moscow'->'Fantasia' \nlanguage: 'ru'->'zz' \n", $logger->LogImportChange[0]['log'], "Expecting A Change (moscow->fantasia)" );
        //print_r( $logger->toArray() );
    }

    public function testAddError()
    {
        ImportLogger::getInstance()->progressive( true );

        ImportLogger::getInstance()->setVendor( Doctrine::getTable("Vendor")->findOneByCity( "moscow" ) );
        ImportLogger::getInstance()->addError( new Exception( "foo" ), new Poi );
        ImportLogger::getInstance()->addError( new MediaException( "bar" ), new Event );

        $logger = Doctrine::getTable("LogImport")->findAll()->getFirst();
        $logger->refresh( true ); // Call this to clear Doctrine cache

        $this->assertEquals( 2, $logger->LogImportCount->count(), "Expecting 2 Sets of Counts, (poi & event)" );
        $this->assertEquals( 1, $logger->LogImportCount[0]['count'], "Expecting Poi Failure Count to be 1" );
        $this->assertEquals( 1, $logger->LogImportCount[1]['count'], "Expecting Event Failure Count to be 1" );

        $this->assertEquals( 2, $logger->LogImportError->count(), "Expecting Two Sets of Errors" );
        $this->assertEquals( 'Exception', $logger->LogImportError[1]['exception_class'], "Expecting exception_class to be Exception" );
        $this->assertEquals( 'Poi', $logger->LogImportError[1]['model'], "Expecting Model to be POI" );
        $this->assertEquals( 'foo', $logger->LogImportError[1]['message'], "Expecting Message to be set." );
        $this->assertEquals( 'MediaException', $logger->LogImportError[0]['exception_class'], "Expecting exception_class to be MediaException" );
        $this->assertEquals( 'Event', $logger->LogImportError[0]['model'], "Expecting Model to be POI" );
        $this->assertEquals( 'bar', $logger->LogImportError[0]['message'], "Expecting Message to be set." );
        //print_r( $logger->toArray() );
    }

    public function testAddInsert()
    {
        ImportLogger::getInstance()->progressive( true );

        ImportLogger::getInstance()->setVendor( Doctrine::getTable("Vendor")->findOneByCity( "moscow" ) );
        ImportLogger::getInstance()->addInsert( new Poi );
        ImportLogger::getInstance()->addInsert( new Poi );
        ImportLogger::getInstance()->addInsert( new Event );
        ImportLogger::getInstance()->addInsert( new Event );
        ImportLogger::getInstance()->addInsert( new Movie );
        ImportLogger::getInstance()->addInsert( new Movie );
        ImportLogger::getInstance()->addInsert( new EventOccurrence );
        ImportLogger::getInstance()->addInsert( new EventOccurrence );

        $logger = Doctrine::getTable("LogImport")->findAll()->getFirst();
        $logger->refresh( true ); // Call this to clear Doctrine cache

        $this->assertEquals( 4, $logger->LogImportCount->count(), "Expecting One Set of Counts, (poi inserts only)" );
        $this->assertEquals( 2, $logger->LogImportCount[0]['count'], "Expecting Save Count to be 2" );
        $this->assertEquals( 'Poi', $logger->LogImportCount[0]['model'], "Expecting Model to be POI" );
        $this->assertEquals( 'insert', $logger->LogImportCount[0]['operation'], "Expecting Operation to be INSERT" );
        $this->assertEquals( 2, $logger->LogImportCount[1]['count'], "Expecting Save Count to be 2" );
        $this->assertEquals( 'Event', $logger->LogImportCount[1]['model'], "Expecting Model to be Event" );
        $this->assertEquals( 'insert', $logger->LogImportCount[1]['operation'], "Expecting Operation to be INSERT" );
        $this->assertEquals( 2, $logger->LogImportCount[2]['count'], "Expecting Save Count to be 2" );
        $this->assertEquals( 'Movie', $logger->LogImportCount[2]['model'], "Expecting Model to be Movie" );
        $this->assertEquals( 'insert', $logger->LogImportCount[2]['operation'], "Expecting Operation to be INSERT" );
        $this->assertEquals( 2, $logger->LogImportCount[3]['count'], "Expecting Save Count to be 2" );
        $this->assertEquals( 'EventOccurrence', $logger->LogImportCount[3]['model'], "Expecting Model to be EventOccurrence" );
        $this->assertEquals( 'insert', $logger->LogImportCount[3]['operation'], "Expecting Operation to be INSERT" );
        //print_r( $logger->toArray() );
    }

    public function testAddFailed()
    {
        ImportLogger::getInstance()->progressive( true );

        ImportLogger::getInstance()->setVendor( Doctrine::getTable("Vendor")->findOneByCity( "moscow" ) );
        ImportLogger::getInstance()->addFailed( new Poi );
        ImportLogger::getInstance()->addFailed( new Poi );
        ImportLogger::getInstance()->addFailed( new Event );
        ImportLogger::getInstance()->addFailed( new Event );
        ImportLogger::getInstance()->addFailed( new Movie );
        ImportLogger::getInstance()->addFailed( new Movie );
        ImportLogger::getInstance()->addFailed( new EventOccurrence );
        ImportLogger::getInstance()->addFailed( new EventOccurrence );

        $logger = Doctrine::getTable("LogImport")->findAll()->getFirst();
        $logger->refresh( true ); // Call this to clear Doctrine cache

        $this->assertEquals( 4, $logger->LogImportCount->count(), "Expecting 4 Sets of Counts, (one for each model)" );
        $this->assertEquals( 2, $logger->LogImportCount[0]['count'], "Expecting Save Count to be 2" );
        
        $this->assertEquals( 'Poi', $logger->LogImportCount[0]['model'], "Expecting Model to be POI" );
        $this->assertEquals( 'failed', $logger->LogImportCount[0]['operation'], "Expecting Operation to be FAILED" );
        $this->assertEquals( 2, $logger->LogImportCount[1]['count'], "Expecting Save Count to be 2" );
        $this->assertEquals( 'Event', $logger->LogImportCount[1]['model'], "Expecting Model to be Event" );
        $this->assertEquals( 'failed', $logger->LogImportCount[1]['operation'], "Expecting Operation to be FAILED" );
        $this->assertEquals( 2, $logger->LogImportCount[2]['count'], "Expecting Save Count to be 2" );
        $this->assertEquals( 'Movie', $logger->LogImportCount[2]['model'], "Expecting Model to be Movie" );
        $this->assertEquals( 'failed', $logger->LogImportCount[2]['operation'], "Expecting Operation to be FAILED" );
        $this->assertEquals( 2, $logger->LogImportCount[3]['count'], "Expecting Save Count to be 2" );
        $this->assertEquals( 'EventOccurrence', $logger->LogImportCount[3]['model'], "Expecting Model to be EventOccurrence" );
        $this->assertEquals( 'failed', $logger->LogImportCount[3]['operation'], "Expecting Operation to be FAILED" );
        //print_r( $logger->toArray() );
    }

    public function testAddUpdate()
    {
        ImportLogger::getInstance()->progressive( true );

        $poi    = new Poi();                $poi->id    = 1;
        $event  = new Event();              $event->id  = 1;
        $movie  = new Movie();              $movie->id  = 1;
        $occ    = new EventOccurrence();    $occ->id    = 1;

        ImportLogger::getInstance()->setVendor( Doctrine::getTable("Vendor")->findOneByCity( "moscow" ) );
        ImportLogger::getInstance()->addUpdate( new Poi );
        ImportLogger::getInstance()->addUpdate( $poi, array( "foo" => "bar" ) );
        ImportLogger::getInstance()->addUpdate( new Event );
        ImportLogger::getInstance()->addUpdate( $event, array( "foo" => "bar" ) );
        ImportLogger::getInstance()->addUpdate( new Movie );
        ImportLogger::getInstance()->addUpdate( $movie, array( "foo" => "bar" ) );
        ImportLogger::getInstance()->addUpdate( new EventOccurrence );
        ImportLogger::getInstance()->addUpdate( $occ, array( "foo" => "bar" ) );

        $logger = Doctrine::getTable("LogImport")->findAll()->getFirst();
        $logger->refresh( true ); // Call this to clear Doctrine cache        

        $this->assertEquals( 8, $logger->LogImportCount->count(), "Expecting 4 Sets of Counts, (one for each model)" );
        $this->assertEquals( 1, $logger->LogImportCount[0]['count'], "Expecting Save Count to be 2" );
        
        $this->assertEquals( 'Poi', $logger->LogImportCount[0]['model'], "Expecting Model to be POI" );
        $this->assertEquals( 'existing', $logger->LogImportCount[0]['operation'], "Expecting Operation to be EXISTING" );
        $this->assertEquals( 'updated', $logger->LogImportCount[1]['operation'], "Expecting Operation to be UPDATED" );
        $this->assertEquals( 1, $logger->LogImportCount[2]['count'], "Expecting Save Count to be 1" );
        $this->assertEquals( 'Event', $logger->LogImportCount[2]['model'], "Expecting Model to be Event" );
        $this->assertEquals( 'existing', $logger->LogImportCount[2]['operation'], "Expecting Operation to be EXISTING" );
        $this->assertEquals( 'updated', $logger->LogImportCount[3]['operation'], "Expecting Operation to be UPDATED" );
        $this->assertEquals( 1, $logger->LogImportCount[4]['count'], "Expecting Save Count to be 1" );
        $this->assertEquals( 'Movie', $logger->LogImportCount[4]['model'], "Expecting Model to be Movie" );
        $this->assertEquals( 'existing', $logger->LogImportCount[4]['operation'], "Expecting Operation to be EXISTING" );
        $this->assertEquals( 'updated', $logger->LogImportCount[5]['operation'], "Expecting Operation to be UPDATED" );
        $this->assertEquals( 1, $logger->LogImportCount[6]['count'], "Expecting Save Count to be 1" );
        $this->assertEquals( 'EventOccurrence', $logger->LogImportCount[6]['model'], "Expecting Model to be EventOccurrence" );
        $this->assertEquals( 'existing', $logger->LogImportCount[6]['operation'], "Expecting Operation to be EXISTING" );
        $this->assertEquals( 'updated', $logger->LogImportCount[7]['operation'], "Expecting Operation to be UPDATED" );

        $this->assertEquals( 4, $logger->LogImportChange->count(), "Expecting 4 Changes to be logged." );
        $this->assertEquals( 'Poi', $logger->LogImportChange[0]['model'], "Expecting First Change to be POI." );
        $this->assertEquals( "Updated Fields: \nfoo: bar \n", $logger->LogImportChange[0]['log'], "Incorrect log message" );
        $this->assertEquals( 'Event', $logger->LogImportChange[1]['model'], "Expecting Second Change to be EVENT." );
        $this->assertEquals( "Updated Fields: \nfoo: bar \n", $logger->LogImportChange[1]['log'], "Incorrect log message" );
        $this->assertEquals( 'Movie', $logger->LogImportChange[2]['model'], "Expecting Third Change to be MOVIE." );
        $this->assertEquals( "Updated Fields: \nfoo: bar \n", $logger->LogImportChange[2]['log'], "Incorrect log message" );
        $this->assertEquals( 'EventOccurrence', $logger->LogImportChange[3]['model'], "Expecting Fourth Change to be EVENTOCCURRENCE." );
        $this->assertEquals( "Updated Fields: \nfoo: bar \n", $logger->LogImportChange[3]['log'], "Incorrect log message" );
        //print_r( $logger->toArray() );
    }

    public function testLoggerEnabled()
    {
        ImportLogger::getInstance()->enabled( true );
        ImportLogger::getInstance()->progressive( true );

        ImportLogger::getInstance()->setVendor( Doctrine::getTable("Vendor")->findOneByCity( "moscow" ) );
        ImportLogger::getInstance()->addInsert( new Poi );

        $logger = Doctrine::getTable("LogImport")->findAll()->getFirst();
        $logger->refresh( true ); // Call this to clear Doctrine cache

        $this->assertEquals( 1, $logger->LogImportCount[0]['count'], "Expecting Save Count to be 1" );

        ImportLogger::getInstance()->enabled( false );
        ImportLogger::getInstance()->addInsert( new Poi );

        $logger = Doctrine::getTable("LogImport")->findAll()->getFirst();
        $logger->refresh( true ); // Call this to clear Doctrine cache

        $this->assertEquals( 1, $logger->LogImportCount[0]['count'], "Expecting Save Count to be 1" );
        //print_r( $logger->toArray() );
    }

    public function testProgressiveTrue()
    {
        ImportLogger::getInstance()->progressive( true );

        ImportLogger::getInstance()->setVendor( Doctrine::getTable("Vendor")->findOneByCity( "moscow" ) );
        ImportLogger::getInstance()->addInsert( new Poi );
        ImportLogger::getInstance()->addInsert( new Poi );

        $logger = Doctrine::getTable("LogImport")->findAll()->getFirst();
        $logger->refresh( true ); // Call this to clear Doctrine cache
        
        $this->assertEquals( 1, $logger->LogImportCount->count(), "Expecting One Set of Counts, (poi inserts only)" );
        $this->assertEquals( 2, $logger->LogImportCount[0]['count'], "Expecting Save Count to be 2" );
        $this->assertEquals( 'Poi', $logger->LogImportCount[0]['model'], "Expecting Model to be POI" );
        $this->assertEquals( 'insert', $logger->LogImportCount[0]['operation'], "Expecting Operation to be INSERT" );
        //print_r( $logger->toArray() );
    }

    public function testProgressiveFalse()
    {
        ImportLogger::getInstance()->progressive( false );

        ImportLogger::getInstance()->setVendor( Doctrine::getTable("Vendor")->findOneByCity( "moscow" ) );
        ImportLogger::getInstance()->addInsert( new Poi );
        ImportLogger::getInstance()->addInsert( new Poi );

        $logger = Doctrine::getTable("LogImport")->findAll()->getFirst();
        $logger->refresh( true ); // Call this to clear Doctrine cache

        $this->assertEquals( 0, $logger->LogImportCount->count(), "Expecting No Sets of Counts" );
        //print_r( $logger->toArray() );
    }

    public function testProgressiveFalseWithEndExplicitlyCalled()
    {
        ImportLogger::getInstance()->progressive( false );

        ImportLogger::getInstance()->setVendor( Doctrine::getTable("Vendor")->findOneByCity( "moscow" ) );
        ImportLogger::getInstance()->addInsert( new Poi );
        ImportLogger::getInstance()->addInsert( new Poi );

        // Explicitly call end (which should now save all the records)
        ImportLogger::getInstance()->end();

        $logger = Doctrine::getTable("LogImport")->findAll()->getFirst();
        $logger->refresh( true ); // Call this to clear Doctrine cache

        $this->assertEquals( 1, $logger->LogImportCount->count(), "Expecting No Sets of Counts" );
        $this->assertEquals( 2, $logger->LogImportCount[0]['count'], "Expecting Save Count to be 2" );
        $this->assertEquals( 'Poi', $logger->LogImportCount[0]['model'], "Expecting Model to be POI" );
        $this->assertEquals( 'insert', $logger->LogImportCount[0]['operation'], "Expecting Operation to be INSERT" );
        //print_r( $logger->toArray() );
    }

    public function testSaveEvery()
    {
        ImportLogger::getInstance()->progressive( false );
        ImportLogger::getInstance()->setVendor( Doctrine::getTable("Vendor")->findOneByCity( "moscow" ) );

        $goOverMaxBy = 10;
        $this->assertLessThan( ImportLogger::getInstance()->getMaxInCache(), $goOverMaxBy, 'You need to set $goOverMaxBy to less than $MaxInCache' );

        for( $x=0; $x<ImportLogger::getInstance()->getMaxInCache() + $goOverMaxBy; $x++ )
        {
            $this->assertEquals( ImportLogger::getInstance()->getRecordsInCache(), ( $x >= ImportLogger::getInstance()->getMaxInCache() ) ? $x - ImportLogger::getInstance()->getMaxInCache() : $x, "Expecting Cache to now hold x total values" );
            ImportLogger::getInstance()->addInsert( new Poi );
        }

        $logger = Doctrine::getTable("LogImport")->findAll()->getFirst();
        $logger->refresh( true ); // Call this to clear Doctrine cache

        $this->assertEquals( 1, $logger->LogImportCount->count(), "Expecting One Set of Counts" );
        $this->assertEquals( ImportLogger::getInstance()->getMaxInCache(), $logger->LogImportCount[0]['count'], 'Expecting Total Saved to be equal to $MaxInCache' );
        $this->assertEquals( ImportLogger::getInstance()->getRecordsInCache(), $goOverMaxBy, 'Expecting Cache to now hold $goOverMaxBy total values' );
        //print_r( $logger->toArray() );
    }

    public function testMultipleVendors()
    {
        ImportLogger::getInstance()->progressive( true );

        ImportLogger::getInstance()->setVendor( Doctrine::getTable("Vendor")->findOneByCity( "moscow" ) );
        ImportLogger::getInstance()->addInsert( new Poi );

        ImportLogger::getInstance()->setVendor( Doctrine::getTable("Vendor")->findOneByCity( "london" ) );
        ImportLogger::getInstance()->addInsert( new Poi );

        $collection = Doctrine::getTable("LogImport")->findAll();

        $this->assertEquals( 2, $collection->count(), "Expecting 2 Vendors" );
        
        foreach( $collection as $logger )
        {
            $logger->refresh( true ); // Call this to clear Doctrine cache
            $this->assertEquals( 1, $logger->LogImportCount[0]['count'], "Expecting Save Count to be 1" );
            $this->assertEquals( 'Poi', $logger->LogImportCount[0]['model'], "Expecting Model to be POI" );
            $this->assertEquals( 'insert', $logger->LogImportCount[0]['operation'], "Expecting Operation to be INSERT" );
        }
        //print_r( $collection->toArray() );
    }
}
?>
