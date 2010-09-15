<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for the prepareExportXMLsForDataEntryTask
 *
 *
 * @package test
 * @subpackage task.lib.unit.test
 *
 * @author Emre Basala <emrebasala@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class prepareExportXMLsForDataEntryTaskTest extends PHPUnit_Framework_TestCase
{
    private $testDirectory ;

    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();

        $this->testDirectory =  sfConfig::get( 'sf_test_dir') .DIRECTORY_SEPARATOR . 'unit'.DIRECTORY_SEPARATOR.'data'. DIRECTORY_SEPARATOR . 'data_entry' .DIRECTORY_SEPARATOR;
    }

    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();

        $genetatedFiles = array(
            'poi.xml',
            'poi_updated.xml',
            'event.xml',
            'event_updated.xml',
            'movie.xml',
            'movie_updated.xml'
        );

        foreach ($genetatedFiles as $file)
        {
            if( file_exists( $this->testDirectory . $file ) )
            {
                unlink( $this->testDirectory . $file  );
            }
        }
    }

    public function testTaskForPoiXml()
    {
        $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>
        <vendor-pois vendor="timeout" modified="2010-08-31T19:48:38">
          <entry vpid="BOM000000000000000000000000054800" lang="en" modified="2010-08-31T19:48:38">
            <geo-position>
              <longitude>72.82902520</longitude>
              <latitude>18.93647680</latitude>
            </geo-position>
            <name><![CDATA[American Centre]]></name>
            <category><![CDATA[theatre-music-culture]]></category>
            <address>
              <street><![CDATA[Near Nirmala Niketan, New Marine Lines]]></street>
              <zip><![CDATA[400020]]></zip>
              <city><![CDATA[Mumbai]]></city>
              <country>IND</country>
            </address>
            <contact>
              <phone>+91 222 262 4590</phone>
            </contact>
            <version lang="en">
              <name><![CDATA[American Centre]]></name>
              <address>
                <street><![CDATA[Near Nirmala Niketan, New Marine Lines]]></street>
                <zip><![CDATA[400020]]></zip>
                <city><![CDATA[Mumbai]]></city>
                <country>IND</country>
              </address>
              <content>
                <vendor-category><![CDATA[Cultural center]]></vendor-category>
                <property key="UI_CATEGORY"><![CDATA[Around Town]]></property>
              </content>
            </version>
          </entry>
          </vendor-pois>
          ';

         //add poi with the id  54800 and add a poiMeta for it
          $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );
          
          $vendorPoiId =777;

          $poi = ProjectN_Test_Unit_Factory::add( 'Poi', array( 'vendor_poi_id' => $vendorPoiId, 'vendor_id' => 1  ,'id' => 54800 ) ); //fixture event happens at this venue

          $poi->addMeta( 'vendor_poi_id' , $vendorPoiId );

          $poi->save();

          $sourceLocation       =   $this->testDirectory. 'poi.xml' ;

          $destinationLocation  =   $this->testDirectory. 'poi_updated.xml' ;

          file_put_contents( $sourceLocation , $xmlContent );

          // #658 Provide overide to update ID
          $override = new RecordFieldOverridePoi();
          $override[ 'record_id' ] = $poi['id'];
          $override[ 'field' ] = 'provider';
          $override[ 'received_value' ] = 'a';
          $override[ 'edited_value' ] = 'b';
          $override[ 'is_active' ] = true;
          $override->save();
          
          $task = new prepareExportXMLsForDataEntryTask( $this->getEventDispatcher() ,new sfFormatter() );

          $arguments = array();

          $options = array(
            'type'          =>'poi',
            'env'           =>'test',
            'xml'           => $sourceLocation,
            'destination'   => $destinationLocation
          );

          $task->run( $arguments, $options);

          $updatedXml = simplexml_load_file( $destinationLocation );

          $this->assertEquals( $vendorPoiId , (string) $updatedXml->entry [ 0 ][ 'vpid' ]);

    }


    public function testTaskForEventXml()
    {
        $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>
        <vendor-events vendor="timeout" modified="2010-09-01T11:10:55">
          <event id="BOM000000000000000000000000063906" modified="2010-09-01T11:10:55">
            <name><![CDATA[Hornbill Festival]]></name>
            <category>other</category>
            <version lang="en">
              <name><![CDATA[Hornbill Festival]]></name>
              <vendor-category><![CDATA[Treks]]></vendor-category>
              <description><![CDATA[Wild Escapes visits the Hornbill festival in Nagaland on a seven-day trip which includes a trek through Kaziranga National Park – by jeep or elephant – and an exploration of the Shankardeva Kalakshetra. Trip dates: Sun Nov 28-Sat Dec 04. To register, call Wild Escapes on 6663-5228 or 2412-2030 or email wildescapes@gmail.com. Rs 29,600 (from Delhi)]]></description>
              <price><![CDATA[Rs 29,600]]></price>
              <property key="UI_CATEGORY"><![CDATA[Around Town]]></property>
            </version>
            <showtimes>
              <place place-id="BOM000000000000000000000000054870">
                <occurrence>
                  <time>
                    <start_date>2010-11-28</start_date>
                    <end_date>2010-12-04</end_date>
                    <utc_offset>+05:30</utc_offset>
                  </time>
                </occurrence>
              </place>
            </showtimes>
          </event>
         </vendor-events>
          ';

        $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );

        // some random number
        // after running the task we are expecting
        // that "<event id="BOM000000000000000000000000063906" modified="2010-09-01T11:10:55">" will be replaced by
        // "<event id="666" modified="2010-09-01T11:10:55">"

        $vendorEventId = 666;

        $event  = ProjectN_Test_Unit_Factory::get( 'Event' , array( 'vendor_poi_id' => $vendorEventId ,'id' => 63906 ) );

        $event->addMeta( 'vendor_event_id' , $vendorEventId );

        $event->save();


        $sourceLocation       =   $this->testDirectory. 'event.xml' ;

        $destinationLocation  =   $this->testDirectory. 'event_updated.xml' ;

        //saving the source XML to the file system
        file_put_contents( $sourceLocation , $xmlContent );

        // #658 Provide overide to update ID
          $override = new RecordFieldOverrideEvent();
          $override[ 'record_id' ] = $event['id'];
          $override[ 'field' ] = 'provider';
          $override[ 'received_value' ] = 'a';
          $override[ 'edited_value' ] = 'b';
          $override[ 'is_active' ] = true;
          $override->save();
        $task = new prepareExportXMLsForDataEntryTask( $this->getEventDispatcher() ,new sfFormatter() );

        $arguments = array();

        $options = array(
            'type'          =>'event',
            'env'           =>'test',
            'xml'           => $sourceLocation,
            'destination'   => $destinationLocation
        );

        $task->run( $arguments, $options);

        //after the task is ran, we are expecting the modified XML in $destinationLocation
        $updatedXml = simplexml_load_file( $destinationLocation );

        //check if the id is changed to 666
        $this->assertEquals( $vendorEventId , (string) $updatedXml->event [ 0 ][ 'id' ]);

    }




    public function testTaskForMovieXml()
    {
        $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>
        <vendor-movies modified="2010-09-01T11:11:01" vendor="timeout">
          <movie id="BOM000000000000000000000000006684" link-id="tt1013743" modified="2010-09-01T11:11:01">
            <name><![CDATA[Knight and Day]]></name>
            <version lang="en">
              <name><![CDATA[Knight and Day]]></name>
              <plot><![CDATA[Knight and Day is an action movie of the old school before everything was about massive digital battles royal. Superspy Tom Cruise fends off trained assassins without ever losing his trademark grin. Cameron Diaz is the innocent bystander swept up into his world of international intrigue when he uses her to get something past airport customs.]]></plot>
              <review><![CDATA[Knight and Day is an action movie of the old school before everything was about massive digital battles royal. Superspy Tom Cruise fends off trained assassins without ever losing his trademark grin. Cameron Diaz is the innocent bystander swept up into his world of international intrigue when he uses her to get something past airport customs. They careen from Kansas to Boston to Switzerland to Spain with Central Intelligence Agency agents who think he’s gone rogue in hot pursuit. Between the magical cell phones that can deactivate car alarms, the surviving of an airliner crash in a cornfield and the astonishingly bad aim of the machine-gun-wielding baddies, this is a film that asks us to skip past suspending disbelief and go straight to expulsion.It’s a measure of how lumbering action movies have been of late that this works at all. Cruise’s weird mix of earnest concern and roguish flirtation gives his character an amusing topspin. Diaz does what she can, but she’s asked to be both a resourceful, classic-car-fixin’ mechanic and the screaming girl of so many action movies. There are some inspired moments thrown in amid all the running and shooting, but they feel like truffle shavings on a Big Mac. - Hank Sartin]]></review>
              <rating>2.0</rating>
              <director><![CDATA[James Mangold]]></director>
              <cast>
                <actor>
                  <actor-name><![CDATA[Tom Cruise]]></actor-name>
                </actor>
                <actor>
                  <actor-name><![CDATA[ Cameron Diaz]]></actor-name>
                </actor>
                <actor>
                  <actor-name><![CDATA[ Paul Dano]]></actor-name>
                </actor>
                <actor>
                  <actor-name><![CDATA[ Peter Sarsgaard]]></actor-name>
                </actor>
              </cast>
              <media mime-type="image/jpeg"><![CDATA[http://projectn.s3.amazonaws.com/mumbai/movie/media/4223fdbd5298a6b3f25fe9edc73d223b.jpg]]></media>
              <property key="UI_CATEGORY"><![CDATA[Film]]></property>
              <property key="UI_CATEGORY"><![CDATA[Film]]></property>
            </version>
            <additional-details>
              <duration><![CDATA[1 hours 49  mins]]></duration>
              <language><![CDATA[English]]></language>
            </additional-details>
          </movie>
          </vendor-movies>
          ';

        $vendor = ProjectN_Test_Unit_Factory::add( 'Vendor' );

        $vendorMovieId =999 ;

        $movie  = ProjectN_Test_Unit_Factory::get( 'Movie' , array( 'vendor_movie_id' => $vendorMovieId ,'id' => 6684 ) );

        $movie->addMeta( 'vendor_movie_id' , $vendorMovieId );

        $movie->save();

        $sourceLocation       =   $this->testDirectory. 'movie.xml' ;

        $destinationLocation  =   $this->testDirectory. 'movie_updated.xml' ;

        file_put_contents( $sourceLocation , $xmlContent );

        // #658 Provide overide to update ID
          $override = new RecordFieldOverrideMovie();
          $override[ 'record_id' ] = $movie['id'];
          $override[ 'field' ] = 'provider';
          $override[ 'received_value' ] = 'a';
          $override[ 'edited_value' ] = 'b';
          $override[ 'is_active' ] = true;
          $override->save();
        $task = new prepareExportXMLsForDataEntryTask( $this->getEventDispatcher() ,new sfFormatter() );

        $arguments = array();

        $options = array(
            'type'          =>'movie',
            'env'           =>'test',
            'xml'           => $sourceLocation,
            'destination'   => $destinationLocation
        );

        $task->run( $arguments, $options);

        $updatedXml = simplexml_load_file( $destinationLocation );

        $this->assertEquals( $vendorMovieId , (string) $updatedXml->movie [ 0 ][ 'id' ]);

    }

    /**
     * returns dispatcher for the task
     *
     * @return EventDispatcher
     */
    private function getEventDispatcher()
    {
        $configuration = ProjectConfiguration::hasActive() ? ProjectConfiguration::getActive() : new ProjectConfiguration( realpath($_test_dir . "/.."));

        return $configuration->getEventDispatcher();

    }

}