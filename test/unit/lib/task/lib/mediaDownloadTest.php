<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 *
 * @package test
 * @subpackage task.lib.unit.test
 *
 * @author Peter Johnson <peterjohnson@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class mediaDownloadTest extends PHPUnit_Framework_TestCase
{
    protected $options;

    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();

        $this->task = new mediaDownloadTask( new sfEventDispatcher, new sfFormatter );

        $this->options['connection'] = 'project_n';
        $this->options['env'] = 'test';
        $this->options['existing'] = 'true';

        $this->downloadDirectory = '/n/import/test_city/poi/media/';

        $this->testUrls = array();
        $this->testUrls[] = 'http://www.toimg.net/travel/images/logos/home.gif'; // GIF of Timeout Logo
        $this->testUrls[] = 'http://www.toimg.net/managed/images/10019813/w647/h298/image.jpg'; // JPEG from Timeout Home Page
    }

    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    protected function runTask()
    {
        foreach( $this->options as $k => $v ) $options[] = "--$k=$v";
        $this->task->runFromCLI( new sfCommandManager, $options );
    }

    public function populateDatabase()
    {
        $p = ProjectN_Test_Unit_Factory::get( 'Poi' );
        $p->save();
        
        $pm = new PoiMedia;
        $pm['url']      = $this->testUrls[0];
        $pm['ident']    = md5( $this->testUrls[0] );
        $pm['poi_id']   = $p['id'];
        $pm->save();

        $pm = new PoiMedia;
        $pm['url']      = $this->testUrls[1];
        $pm['ident']    = md5( $this->testUrls[1] );
        $pm['poi_id']   = $p['id'];
        $pm->save();

        $this->assertEquals( 2, Doctrine::getTable('PoiMedia')->findAll()->count() );
    }

    public function testInvalidResponseCode()
    {
        $this->populateDatabase();
        $this->runTask();
        
        $downloadFile = $this->downloadDirectory . md5( $this->testUrls[1] ) . ".jpg";

        $sampleRecord = Doctrine::getTable('PoiMedia')->findOneById( 2 );
        
        $this->setExpectedException( 'MediaException', "Invalid HTTP Code: '555' for ".get_class( $sampleRecord )." id:2" );
        $this->task->validateDownload( array( 'http_code' => 555 ), $downloadFile, $sampleRecord );
    }

    public function testFailedToSave()
    {
        $this->populateDatabase();
        $this->runTask();

        $downloadFile = $this->downloadDirectory . md5( $this->testUrls[1] ) . ".jpg";

        $sampleRecord = Doctrine::getTable('PoiMedia')->findOneById( 2 );

        $this->setExpectedException( 'MediaException', "Failed to Save to Destination: '{$downloadFile}' for ".get_class( $sampleRecord )." id:2" );

        @unlink( $downloadFile );
        $this->task->validateDownload( array( 'http_code' => 200 ), $downloadFile, $sampleRecord );
    }

    public function testInvalidMimeTypeOnDisk()
    {
        $this->populateDatabase();
        $this->runTask();

        $downloadFile = $this->downloadDirectory . md5( $this->testUrls[1] ) . ".jpg";

        $sampleRecord = Doctrine::getTable('PoiMedia')->findOneById( 2 );

        copy( '/n/test/unit/data/images/google.png', $downloadFile );
        $this->setExpectedException( 'MediaException', "Invalid MIME Type: 'image/png' for ".get_class( $sampleRecord )." id:2" );
        $this->task->validateDownload( array( 'http_code' => 200 ), $downloadFile, $sampleRecord );
    }

    public function testInvalidMimeInResponseHeader()
    {
        $this->populateDatabase();
        $this->runTask();

        $downloadFile = $this->downloadDirectory . md5( $this->testUrls[0] ) . ".jpg";

        $sampleRecord = Doctrine::getTable('PoiMedia')->findOneById( 2 );

        $this->setExpectedException( 'MediaException', "Failed to Save to Destination: '{$downloadFile}' for ".get_class( $sampleRecord )." id:2" );
        $this->task->validateDownload( array( 'http_code' => 200 ), $downloadFile, $sampleRecord );
    }

    public function testDownload()
    {
        $this->populateDatabase();
        $this->runTask();

        $poiMedia = Doctrine::getTable('PoiMedia')->findAll();

        $this->assertEquals( 'error',       $poiMedia[0]['status'] );
        $this->assertEquals( 'image/gif',   $poiMedia[0]['mime_type'] );

        $this->assertEquals( 'valid',       $poiMedia[1]['status'] );
        $this->assertEquals( '88187',       $poiMedia[1]['content_length'] );
        $this->assertEquals( 'image/jpeg',  $poiMedia[1]['mime_type'] );

        $this->assertEquals( 2, Doctrine::getTable('PoiMedia')->findAll()->count() );
    }

    public function testMediaHasChanged()
    {
        $this->populateDatabase();
        $this->runTask();

        $this->task->schedule = array( date('l') => array( 2 ) );
        $this->assertTrue( $this->_changePropertyAndCheckRecordUpdated( 'PoiMedia', 2, array( 'content_length' => '999' ) ) );
    }

    public function testScheduler()
    {
        $this->populateDatabase();
        $this->runTask();

        // Nothing scheduled for today.
        $this->task->schedule = array( date('l') => array( false ) );
        $this->assertFalse( $this->_changePropertyAndCheckRecordUpdated( 'PoiMedia', 2, array( 'content_length' => '999' ) ) );

        // Schedule ( id % 7 ) == 2 for today.
        $this->task->schedule = array( date('l') => array( 2 ) );
        $this->assertTrue( $this->_changePropertyAndCheckRecordUpdated( 'PoiMedia', 2, array( 'content_length' => '999' ) ) );
    }

    public function testNotChangedDontReDownload()
    {
        $this->populateDatabase();
        $this->runTask();

        $this->assertFalse( $this->_changePropertyAndCheckRecordUpdated( 'PoiMedia', 2, array() ) );
    }

    /**
     * _changePropertyAndCheckRecordUpdated
     *
     * @param string $model
     * @param integer $id
     * @param array $properties
     * @return boolean
     */
    private function _changePropertyAndCheckRecordUpdated( $model, $id, $properties = array() )
    {
        $r = Doctrine::getTable( $model )->findOneById( $id );
        $r->merge( $properties );
        $r->save();
        $lastUpdated = (string) $r['updated_at'];

        sleep( 1 );
        $this->runTask();

        return (string) Doctrine::getTable( $model )->findOneById( $id )->updated_at !== $lastUpdated;
    }
}