<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test class for LondonImporter.
 * Generated by PHPUnit on 2010-01-21 at 15:51:00.
 */
class LondonImporterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var LondonImporter
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
    	// create london connection
    	Doctrine_Manager::connection( new PDO( 'sqlite::memory:' ), 'searchlight_london' );

    	// load project n
        ProjectN_Test_Unit_Factory::createSqliteMemoryDb( 'project_n' );
        Doctrine::loadData( 'data/fixtures' );

        // load london data
        Doctrine::loadData( dirname( __FILE__ ) . '/../../../../../plugins/toLondonPlugin/data/fixtures/fixtures.yml' );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroySqliteMemoryDb( 'searchlight_london' );
        ProjectN_Test_Unit_Factory::destroySqliteMemoryDb( 'project_n' );
    }

    /**
     * @todo Implement testRun().
     */
    public function testRun()
    {
    }

    /**
     *
     * @uses /plugins/toLondonPlugin/data/fixtures/fixtures.yml
     */
    public function testProcessImportedVenue()
    {
        $this->object = new LondonImporter( );

        $this->object->run( );

        $poi = Doctrine::getTable( 'Poi' )->findOneByVendorPoiId( 1 );

        $this->assertTrue( $poi instanceof Doctrine_Record );

		$this->assertEquals( 'Dummy Building Name 1', $poi[ 'house_no' ]  );
	    $this->assertEquals( 'Dummy Address 1',       $poi[ 'street' ] );
		$this->assertEquals( 'London',                $poi[ 'city' ] );
		$this->assertEquals( '',                      $poi[ 'district' ] );
	    $this->assertEquals( 'GBR',                   $poi[ 'country' ] );
		$this->assertEquals( '',                      $poi[ 'additional_address_details' ] );
		$this->assertEquals( 'Dummy Postcode 1',      $poi[ 'zips' ] );
		$this->assertEquals( 'GB',                    $poi[ 'country_code' ] );
		$this->assertEquals( '',                      $poi[ 'extension' ] );
		$this->assertEquals( '51.0000000',            $poi[ 'latitude' ] );
		$this->assertEquals( '-0.10000000',           $poi[ 'longitude' ] );
		$this->assertEquals( 'Dummy Email 1',         $poi[ 'email' ] );
		$this->assertEquals( 'Dummy Url 1',           $poi[ 'url' ] );
		$this->assertEquals( 'Dummy Phone 1',         $poi[ 'phone' ] );
		$this->assertEquals( '',                      $poi[ 'phone2' ] );
		$this->assertEquals( '',                      $poi[ 'fax' ] );
		$this->assertEquals( '',                      $poi[ 'vendor_category' ] );
		$this->assertEquals( '',                      $poi[ 'keywords' ] );
		$this->assertEquals( '',                      $poi[ 'short_description' ] );
		$this->assertEquals( '',                      $poi[ 'description' ] );
		$this->assertEquals( 'Dummy Travel 1',        $poi[ 'public_transport_links' ] );
		$this->assertEquals( '',                      $poi[ 'price_information' ] );
		$this->assertEquals( 'Dummy Opening Times 1', $poi[ 'openingtimes' ] );
		$this->assertEquals( '',                      $poi[ 'star_rating' ] );
		$this->assertEquals( '',                      $poi[ 'rating' ] );
		$this->assertEquals( '',                      $poi[ 'provider' ] );
    }

    /**
     *
     * @uses /plugins/toLondonPlugin/data/fixtures/fixtures.yml
     */
    public function testProcessImportedEvent()
    {
        $this->object = new LondonImporter( );

        $this->object->run( );

        $event = Doctrine::getTable( 'Event' )->findOneByVendorEventId( 1 );

        $this->assertTrue( $event instanceof Doctrine_Record );

        $this->assertEquals( 'Dummy Title 1', $event[ 'name' ]  );
    }

    /**
     *
     * @uses /plugins/toLondonPlugin/data/fixtures/fixtures.yml
     */
    public function testProcessImportedOccurrence()
    {
        $this->object = new LondonImporter( );

        $this->object->run( );

        $occurrence = Doctrine::getTable( 'EventOccurrence' )->findOneByVendorEventOccurrenceId( 1 );

        $this->assertTrue( $occurrence instanceof Doctrine_Record );

        $this->assertEquals( '2010-10-01', $occurrence[ 'start' ]  );
        $this->assertEquals( '1', $occurrence[ 'utc_offset' ]  );
    }

}
