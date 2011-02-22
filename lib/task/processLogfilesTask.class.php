<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class processLogfiles extends sfBaseTask
{

    private $_connection;

    protected function configure()
    {

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name','backend'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
            new sfCommandOption('log-dirs', null, sfCommandOption::PARAMETER_REQUIRED, 'The log directories to process' ),
            ));

            $this->namespace        = 'projectn';
            $this->name             = 'processLogfiles';
            $this->briefDescription = 'process the logfiles recursively and stores the information in the LogTask tables';
            $this->detailedDescription = "Use logic to select the Best Master out of foudn duplicate pois and map them in PoiReference Table";
    }

    protected function execute($arguments = array(), $options = array())
    {
        taskLogger::start( $this, __FILE__, $options );

        // Estabilish Database Connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

        $logFoldersToProcess = explode( ',', $options[ 'log-dirs' ] );
        
        foreach ( $logFoldersToProcess as $logFolder )
        {
            $this->processFolder( sfConfig::get( 'sf_log_dir' ) . '/' . $logFolder );
        }

        taskLogger::end( $this );
    }


    protected function processFolder( $sourcePath )
    {
        $logFileParser = new logFileParser();

        $logFiles = DirectoryIteratorN::iterate( $sourcePath, DirectoryIteratorN::DIR_FILES, $extension = 'log', '', false );

        $parsedFolderName = '_parsed' . date( '_Y-m-d_H-m-i-s' );

        //create parsed dir
        if ( ! mkdir( $sourcePath. '/' . $parsedFolderName ) )
        {
            taskLogger::log( $this, 'failed to create ' . $parsedFolderName );
            return false;
        }

        $conn = Doctrine_Manager::connection();

        foreach( $logFiles as $logFile )
        {
            $conn->beginTransaction();
            try
            {
                //process log file
                $logFileParser->processFile( $sourcePath. '/' .$logFile );
                //move processed files to the parsed folder
                rename( $sourcePath. '/' .$logFile, $sourcePath. '/' . $parsedFolderName . '/' . $logFile );
                $conn->commit();
            }
            catch( Exception $e )
            {
                $conn->rollback();
                taskLogger::log( $this, $e->getMessage() . ' in: ' . $sourcePath. '/' .$logFile );
            }            
        }

    }

}