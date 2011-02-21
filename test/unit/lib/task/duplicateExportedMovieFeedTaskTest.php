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
        
        // Change the projectn_export DIR in sfConfig to test Path
        sfConfig::set( 'projectn_export', sfConfig::get( 'sf_test_dir' ) . '/export' );

        // create the Task
        $this->task = new duplicateExportedMovieFeedTask( new sfEventDispatcher, new sfFormatter );
    }

    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
    }

    public function testDuplicatingMovies()
    {
        $options = ' --end=prod --source="istanbul_en" --dest=istanbul --directory="duplicates"';
        $this->task->runFromCLI( new sfCommandManager, $options );
    }
}