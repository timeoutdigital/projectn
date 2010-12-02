<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../../bootstrap.php';

/**
 * Test class for Sqlite Geocode Caching
 *
 * @package test
 * @subpackage lib.unit
 *
 * @author Peter Johnson <peterjohnson@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class SqliteGeoCacheTest extends PHPUnit_Framework_TestCase
{
    private $testData = array();

    protected function setUp()
    {
        SqliteGeoCache::$persistent = false; // This cause I/O error when files are deleted in code bellow
        $this->assertTrue( class_exists( 'SqliteGeoCache' ) );

        file_exists( SqliteGeoCache::$sqlpath ) && unlink( SqliteGeoCache::$sqlpath );
        $this->assertFalse( file_exists( SqliteGeoCache::$sqlpath ) );

        $this->testData[ 'http://www.google.com/'  ] = '<html>"Google?"</html>';
        $this->testData[ 'http://www.timeout.com/' ] = 'Hello &amp; World!';
        $this->testData[ 'http://maps.google.com/' ] = '200,4,57.1549492,65.5156404';
    }
    
    public function testConnect()
    {
        $this->assertTrue( method_exists( 'SqliteGeoCache', 'connect' ) );
        
        $this->assertTrue( SqliteGeoCache::connect() );
        $this->assertTrue( file_exists( SqliteGeoCache::$sqlpath ) );
    }

    public function testEnabled()
    {
        $this->assertTrue( method_exists( 'SqliteGeoCache', 'enabled' ) );

        $this->assertEquals( SqliteGeoCache::$enabled, SqliteGeoCache::enabled() );
    }

    public function testCountGetPutAndDel()
    {
        $this->assertTrue( method_exists( 'SqliteGeoCache', 'get' ) );
        $this->assertTrue( method_exists( 'SqliteGeoCache', 'put' ) );
        $this->assertTrue( method_exists( 'SqliteGeoCache', 'del' ) );
        $this->assertTrue( method_exists( 'SqliteGeoCache', 'count' ) );

        $initialCount = SqliteGeoCache::count();
        // Apparently some of previous test are calling google and creating cache records, which mess with be bellow count...
        // this initialCount should fix this issue...
        foreach( $this->testData as $url => $response )
        {
            // Test Count and Premature Get & Del
            $this->assertEquals( ($initialCount + 0), SqliteGeoCache::count() );
            $this->assertFalse( SqliteGeoCache::del( $url ) );
            $this->assertNull( SqliteGeoCache::get( $url ) );

            // Test Put
            $this->assertTrue( SqliteGeoCache::put( $url, $response ) );
            $this->assertEquals( ($initialCount + 1), SqliteGeoCache::count() );

            // Test Get
            $this->assertEquals( $response, SqliteGeoCache::get( $url ) );

            // Test Del
            $this->assertTrue( SqliteGeoCache::del( $url ) );
            $this->assertEquals( ($initialCount + 0), SqliteGeoCache::count() );

            // Test Double Del
            $this->assertFalse( SqliteGeoCache::del( $url ) );

            // Test Add Twice ( Must be a unique $url )
            $this->assertTrue( SqliteGeoCache::put( $url, $response ) );
            $this->assertFalse( SqliteGeoCache::put( $url, $response ) );
            $this->assertEquals( ($initialCount + 1), SqliteGeoCache::count() );

            // Clean Up
            $this->assertTrue( SqliteGeoCache::del( $url ) );
        }
    }
}