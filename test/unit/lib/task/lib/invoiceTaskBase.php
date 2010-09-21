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

class invoiceTaskBase extends PHPUnit_Framework_TestCase
{
    protected $options;

    protected function setUp()
    {
        parent::setUp();
        $this->task = new invoiceTask( new sfEventDispatcher, new sfFormatter );
        
        $this->options['connection'] = 'project_n';
        $this->options['env'] = 'test';
        $this->options['csv'] = 'true';
        $this->options['path'] = TO_TEST_DATA_PATH . '/invoice/';
        $this->options['STDERR'] = 'false';
        
        $this->populateDatabase();
    }

    protected function tearDown()
    {
        parent::tearDown();
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    protected function addModel( $model, array $properties = array(), array $links = array() )
    {
        $m = new $model;
        $m->merge( $properties );
        foreach( $links as $linkModel => $idArray ) $m->link( $linkModel, $idArray );
        $m->save();
    }

    protected function populateDatabase()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
        ProjectN_Test_Unit_Factory::createDatabases();
        
        $v = ProjectN_Test_Unit_Factory::add( 'Vendor' );

        $vendorSampleCats = array( 'A','B','C','D','E','F','G' );
        foreach( $vendorSampleCats as $catName )
        {
            $this->addModel( 'VendorPoiCategory',   array( 'name' => $catName, 'vendor_id' => $v['id'] ) );
            $this->addModel( 'VendorEventCategory', array( 'name' => $catName, 'vendor_id' => $v['id'] ) );
        }

        $this->uiCatgeories = array( 'Film', 'Eating & Drinking', 'Around Town', 'Music', 'Stage', 'Nightlife', 'Art' );
        for( $x=1; $x<count( $this->uiCatgeories )+1; $x++ )
        {
            $this->addModel( 'UiCategory',
                array( 'name' => $this->uiCatgeories[$x-1] ),
                array( 'VendorPoiCategory'   => array( $x ),
                       'VendorEventCategory' => array( $x )
                ));
        }
    }

    protected function runTask()
    {
        foreach( $this->options as $k => $v ) $options[] = "--$k=$v";
        
        ob_start();
        $this->task->runFromCLI( new sfCommandManager, $options );
        return ob_get_clean();
    }

    protected function parseCSV( $csv )
    {
        $data = explode( PHP_EOL, trim( $csv ) );
        $headers = explode( ',', array_shift( $data ) );

        foreach( $data as $datarow )
        {
            $columns = explode( ',', $datarow );
            for( $x=0; $x<count( $columns ); $x++ )
                $row[ $headers[ $x ] ] = $columns[ $x ];

            $sheet[] = $row;
        }

        if( !isset( $sheet ) ) throw new Exception( 'Failed to parse CSV, did the task produce output?' );

        return $sheet;
    }
}