<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../../bootstrap.php';

/**
 * Description
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Rajeevan kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class duplicateExportedMovieFeedTaskTest extends PHPUnit_Framework_TestCase
{

    private $task;
    
    protected function setUp()
    {
        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');
        
        // create the Task
        $this->task = new duplicateExportedMovieFeedTask( new sfEventDispatcher, new sfFormatter );
    }

    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    public function testDuplicatingMovies()
    {
        $istanbul_en = Doctrine::getTable( 'Vendor' )->findOneByCity( 'istanbul_en' );
        $istanbul = Doctrine::getTable( 'Vendor' )->findOneByCity( 'istanbul' );

        // ensure that file are there for _en and not for istanbul
        if( file_exists( sfConfig::get( 'app_projectn_export' ) . '/export_duplicates/movie/istanbul.xml' ) )
            unlink( sfConfig::get( 'app_projectn_export' ) . '/export_duplicates/movie/istanbul.xml' );

        $this->assertEquals( true, file_exists( sfConfig::get( 'app_projectn_export' ) . '/export_duplicates/movie/istanbul_en.xml' ));
        $this->assertEquals( false, file_exists( sfConfig::get( 'app_projectn_export' ) . '/export_duplicates/movie/istanbul.xml' ));

        // Delete if File exists
        $this->runTask( array(
            'source' =>'istanbul_en',
            'dest' => 'istanbul',
            'directory' => 'export_duplicates'
        ) );

        // check for files
        $this->assertEquals( true, file_exists( sfConfig::get( 'app_projectn_export' ) . '/export_duplicates/movie/istanbul_en.xml' ));
        $this->assertEquals( true, file_exists( sfConfig::get( 'app_projectn_export' ) . '/export_duplicates/movie/istanbul.xml' ));

        // Open XML and assert
        $xml = simplexml_load_file( sfConfig::get( 'app_projectn_export' ) . '/export_duplicates/movie/istanbul.xml' );
        $this->assertEquals(8, count($xml));
        $this->assertEquals('Tron: Legacy', (string)$xml->movie[0]->name );
        $this->assertEquals($istanbul['airport_code'] . '000000000000000000000000018910', (string)$xml->movie[0]['id'], 'Movie IDs Airport code should be updated to Istanbul' );

        // Delete the test file
        unlink( sfConfig::get( 'app_projectn_export' ) . '/export_duplicates/movie/istanbul.xml' );        
    }

    public function testDuplicatingMoviesTestOveride()
    {
        $istanbul_en = Doctrine::getTable( 'Vendor' )->findOneByCity( 'istanbul_en' );
        $istanbul = Doctrine::getTable( 'Vendor' )->findOneByCity( 'istanbul' );

        // ensure that file are there for _en and not for istanbul
        if( file_exists( sfConfig::get( 'app_projectn_export' ) . '/export_duplicates/movie/istanbul.xml' ) )
            unlink( sfConfig::get( 'app_projectn_export' ) . '/export_duplicates/movie/istanbul.xml' );

        // Put empty content, whn runner task executed this fiel should be replaced
        file_put_contents( sfConfig::get( 'app_projectn_export' ) . '/export_duplicates/movie/istanbul.xml' , '');

        $this->assertEquals( true, file_exists( sfConfig::get( 'app_projectn_export' ) . '/export_duplicates/movie/istanbul_en.xml' ));
        $this->assertEquals( true, file_exists( sfConfig::get( 'app_projectn_export' ) . '/export_duplicates/movie/istanbul.xml' ));

        // Delete if File exists
        $this->runTask( array(
            'source' =>'istanbul_en',
            'dest' => 'istanbul',
            'directory' => 'export_duplicates',
            'override' => 'true',
        ) );

        // check for files
        $this->assertEquals( true, file_exists( sfConfig::get( 'app_projectn_export' ) . '/export_duplicates/movie/istanbul_en.xml' ));
        $this->assertEquals( true, file_exists( sfConfig::get( 'app_projectn_export' ) . '/export_duplicates/movie/istanbul.xml' ));

        // Open XML and assert
        $xml = simplexml_load_file( sfConfig::get( 'app_projectn_export' ) . '/export_duplicates/movie/istanbul.xml' );
        $this->assertEquals(8, count($xml));
        $this->assertEquals('Tron: Legacy', (string)$xml->movie[0]->name );
        $this->assertEquals($istanbul['airport_code'] . '000000000000000000000000018910', (string)$xml->movie[0]['id'], 'Movie IDs Airport code should be updated to Istanbul' );

        // Delete the test file
        unlink( sfConfig::get( 'app_projectn_export' ) . '/export_duplicates/movie/istanbul.xml' );
    }

    protected function runTask( $options )
    {
        $default = array(
            'connection' => 'project_n',
            'env' => 'test',
        );

        $options = array_merge( $default, $options );

        $args = array();
        foreach( $options as $k => $v )
        {
                $args[] = "--$k=$v";
        }
        
        $this->task->run( array(), $args );
    }
}