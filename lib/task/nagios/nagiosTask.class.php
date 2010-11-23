<?php

/**
 * Base for Nagios check tasks
 *
 * @package projectn
 * @subpackage task
 *
 * @author Peter Johnson <peterjohnson@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 *
 */

abstract class nagiosTask extends sfBaseTask
{
    protected $warnings     = array();
    protected $errors       = array();

    protected $enableDB     = false;
    protected $description  = 'Nagios Automated Script';
    private   $appPath;

    protected function configure()
    {
        defined( 'DS' ) || define( 'DS', DIRECTORY_SEPARATOR );

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n')
        ));

        $this->namespace            = 'nagios';
        $this->name                 = str_replace( 'task', '', strtolower( get_class( $this ) ) );
        $this->briefDescription     = $this->description;
        $this->detailedDescription  = 'Nagios Automated Script';

        $this->setAppPath( sfConfig::get( 'sf_root_dir' ) );
    }

    protected function execute( $arguments = array(), $options = array() )
    {
        // Connect to DB when Database Enabled
        $this->enableDB && $this->configureDatabase( $options );
        
        $this->executeNagiosTask( $arguments, $options );
        $this->postExecute();
    }

    abstract protected function executeNagiosTask( $arguments = array(), $options = array() );

    protected function postExecute()
    {
        foreach( array_merge( $this->errors, $this->warnings ) as $message )
        {
            echo $message . PHP_EOL;
        }

        switch( false )
        {
            case empty( $this->errors )     : exit( 2 );
            case empty( $this->warnings )   : exit( 1 );
            default                         : echo 'ok'; exit( 0 );
        }
    }

    protected function configureDatabase( $options = array() )
    {
        $databaseManager = new sfDatabaseManager( $this->configuration );
        $databaseManager->getDatabase( $options['connection'] ? $options['connection'] : null )->getConnection();
    }

    protected function setAppPath( $path = '' )
    {
        if( is_string( realpath( $path ) ) && is_dir( realpath( $path ) ) ) $this->appPath = realpath( $path );
        else throw new NagiosException( 'Application Root Not Found or Not a Valid Directory' );
    }

    protected function getAppPath()
    {
        if( is_string( $this->appPath ) && is_dir( $this->appPath ) ) return $this->appPath;
        throw new NagiosException( 'Application Root Not Found or Not a Valid Directory' );
    }

    protected function getExportDirectoryPathForDate( $date )
    {
        $basePath = $this->getAppPath() . DS . 'export' . DS;
        $releases = array_reverse( DirectoryIteratorN::iterate( $basePath, DirectoryIteratorN::DIR_FOLDERS ) );
                
        foreach( $releases as $exportDirectory )
        {
            $folderDateStamp = strtotime( str_replace( 'export_', '', $exportDirectory ) );
            if( date( 'Ymd', $folderDateStamp ) === date( 'Ymd', $date ) ) return realpath( $basePath . $exportDirectory );
        }
    }

    public function addWarning( $message )
    {
        $this->warnings[] = $message;
    }

    public function addError( $message )
    {
        $this->errors[] = $message;
    }

    /**
     * set array of string messages to be MERGED with existing errors
     * @param array $warnings
     */
    protected function setError( array $errors )
    {
        $this->errors = array_merge( $this->errors, $errors );
    }

    /**
     * set array of string messages to be MERGED with existing warnings
     * @param array $warnings
     */
    protected function setWarning( array $warnings )
    {
        $this->warnings = array_merge( $this->warnings, $warnings );
    }
}

class NagiosException extends Exception {}