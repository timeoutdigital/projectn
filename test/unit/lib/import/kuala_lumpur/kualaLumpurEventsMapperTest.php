<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test of Kuala Lumpur Events mapper
 *
 * @package test
 * @subpackage london.import.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class kualaLumpurVenuesMapperTest extends PHPUnit_Framework_TestCase
{
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    $importer = new Importer();

    $this->vendor = ProjectN_Test_Unit_Factory::add( 'Vendor', array( 
      'city'=>'kuala lumpur', 
      'language'=>'en',
      'inernational_dial_code' => '+60',
      ) );

    $this->xml = simplexml_load_file( TO_TEST_DATA_PATH . '/kuala_lumpur_events.xml' );

    $importer->addDataMapper( new kualaLumpurEventsMapper( $this->vendor, $this->xml ) );
    $importer->addLogger( new echoingLogger( ));
    $importer->run();

    $this->events = Doctrine::getTable( 'Event' )->findAll();
  }

  protected function tearDown()
  {
    ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testMapping()
  {
    $this->assertEquals( count( $this->xml ),
                         $this->events->count(),
                         'check import count'
                         );

    $this->assertEquals( 5158,
                         $this->events[0]['vendor_event_id'],
                         'Check id'
                         );
  }

  public function testVendorCategories()
  {
    $this->assertEquals( 'Film | Screenings',
                                                                //should be able to use integer index!
                         $this->events[0]['VendorEventCategory']['Film | Screenings']['name'],
                         'Check vendor category'
                         );

    $this->assertEquals( 'Music | Gigs',
                                                                //should be able to use integer index!
                         $this->events[1]['VendorEventCategory']['Music | Gigs']['name'],
                         'Check vendor category'
                         );
  }

  public function testDescriptions()
  {
    $this->assertEquals( 'A preview of the festival, right here in the Klang Valley. Featuring an awesome mash up of musical talents, the night will start will songstress...',
                         $this->events[1]['short_description'],
                         'Checking short description'
                          );

    $desc = <<<EOF
<p>It's finally here. The Miri International Jazz Festival make its highly anticipated return. To whet your appetite, Laundry Bar brings to you a preview of the festival, right here in the Klang Valley.<br />
<br />
Featuring an awesome mash up of musical talents, the night will start will songstress Diandra Arjunaidi, followed by Isaac Entry and Jeremy Tordjman (pic). Catch these amazing talents in action live at Laundry Bar on May 7, 9.30pm.</p>
EOF;

    $this->assertEquals( $desc,
                         $this->events[1]['description'],
                         'Checking long description'
                          );
  }
}
