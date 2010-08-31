<?php

class runnerTask extends sfBaseTask
{
    protected $symfonyPath;
    protected $logRootDir;
    protected $exportRootDir;
    protected $taskOptions;
    const  TASK_IMPORT = 1;
    const  TASK_EXPORT = 2;
    const  TASK_UPDATE = 3;

    protected function configure()
    {
        $this->addOptions(array(
          new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
          new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
          new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'backend'),
          new sfCommandOption('city', null, sfCommandOption::PARAMETER_OPTIONAL, 'The city to import',null)
        ));

        $this->namespace        = 'projectn';
        $this->name             = 'runner';
        $this->briefDescription = 'wrapper task to run import and export tasks for projectn';
        $this->detailedDescription = '';
        }

    protected function execute($arguments = array(), $options = array())
    {
        date_default_timezone_set( 'Europe/London' );

        $this->symfonyPath = sfConfig::get( 'sf_root_dir' );
        $this->logRootDir = sfConfig::get( 'sf_log_dir' );
        $this->exportRootDir = sfConfig::get( 'sf_root_dir' ) . '/export';
        $this->taskOptions = $options;

        $this->runImportTasks( $options[ 'city' ] );
        $this->runExportTasks( $options[ 'city' ] );
        $this->runUpdateTasks( $options[ 'city' ] );

    }

    /**
     * runs the import tasks
     *
     * @param string $city , if given runs imports for only this city
     */
    protected function runImportTasks( $city = null )
    {
        $options = $this->taskOptions;

        $importCities = $this->getCities( self::TASK_IMPORT, $city );

        if( empty( $importCities  ) )
        {
             $this->logSection( 'Runner' , "Runner will not be running any IMPORT tasks!" );
             return;
        }

        foreach ( $importCities as $importCity )
        {
            $logPath = $this->logRootDir . '/import' ;

            $this->verifyAndCreatePath( $logPath );

            foreach ( $importCity['type'] as $type )
            {
                $this->logSection( 'import', date( 'Y-m-d H:i:s' ) . ' - running import  for ' . $importCity[ 'name' ] . ' (' . $type . ')') ;

                $taskCommand = $this->symfonyPath . '/./symfony projectn:import --env="' . $options['env'] . '" --application="' . $options['application'] . '" --city="' . $importCity[ 'name' ] . '" --type="' . $type . '"';

                $logCommand  = $logPath . '/' . strtr( $importCity[ 'name' ], ' ', '_' ) . '.log';

                $this->executeCommand( $taskCommand, $logCommand );
            }

        }
    }

    /**
     * runs the update tasks
     *
     * @param string $city , if given runs imports for only this city
     */
    protected function runUpdateTasks( $city = null )
    {
        $options = $this->taskOptions;

        $importCities = $this->getCities( self::TASK_UPDATE, $city );

        if( empty( $importCities  ) )
        {
             $this->logSection( 'Runner' , "Runner will not be running any IMPORT tasks!" );
             return;
        }

        foreach ( $importCities as $importCity )
        {
            $logPath = $this->logRootDir . '/import' ;

            $this->verifyAndCreatePath( $logPath );

            foreach ( $importCity['type'] as $type )
            {
                $this->logSection( 'import', date( 'Y-m-d H:i:s' ) . ' - running import  for ' . $importCity[ 'name' ] . ' (' . $type . ')') ;

                $taskCommand = $this->symfonyPath . '/./symfony projectn:import --env="' . $options['env'] . '" --application="' . $options['application'] . '" --city="' . $importCity[ 'name' ] . '" --type="' . $type . '"';

                $logCommand  = $logPath . '/' . strtr( $importCity[ 'name' ], ' ', '_' ) . '.log';

                $this->executeCommand( $taskCommand, $logCommand );
            }

        }
    }


    /**
     * runs the export tasks
     *
     * @param string $city , if given runs exports for only this city
     */
    protected function runExportTasks(  $city = null )
    {
        $exportCities = $this->getCities( self::TASK_EXPORT , $city );

        $options = $this->taskOptions;

        if( empty( $exportCities  ) )
        {
             $this->logSection( 'Runner' , "Runner will not be running any EXPORT tasks!" );
             return;
        }

        $deleteOlderThanDays = '7 days';
        $timestamp = date( 'Ymd' );
        $exportPath = $this->exportRootDir . '/export_' . $timestamp;
        $exportPathDataEntry = $this->exportRootDir . '/export_data_entry_' . $timestamp;
        $this->verifyAndCreatePath( $exportPath );

        foreach ( $exportCities as $exportCity )
        {
            foreach ( $exportCity['type'] as $type )
            {
                $this->logSection( 'export', date( 'Y-m-d H:i:s' ) . ' - running export for ' . $exportCity[ 'name' ] . ' (' . $type . ')' );

                $logPath = $this->logRootDir . '/export';
                $this->verifyAndCreatePath( $logPath );
                $currentExportPath = $exportPath .'/'.$type;
                $this->verifyAndCreatePath( $currentExportPath );

                $taskCommand = $this->symfonyPath . '/./symfony projectn:export --env=' . $options['env'] . ' --city="' .  $exportCity[ 'name' ] . '" --application="' .  $options[ 'application' ]  . '" --language=' . $exportCity[ 'language' ] . ' --type="' . $type . '" --destination=' . $currentExportPath . '/' . str_replace( " ", "_",  $exportCity[ 'name' ] ) .'.xml';
                // When Events get-called, we pass POI XML destination
                if($type == 'event')
                {
                    $taskCommand .= ' --poi-xml=' . $exportPath .'/poi/' . str_replace( " ", "_",  $exportCity[ 'name' ] ) .'.xml';
                }
                if( isset( $exportCity['validation'] )  )
                {
                    //convert boolean validation values into string "true" or "false"
                    $exportCity[ 'validation' ] = ( $exportCity[ 'validation' ] == 1 ) ? "true" : "false";

                    $taskCommand .= ' --validation=' .  $exportCity[ 'validation' ];
                }

                $logCommand  = $logPath . '/' . strtr( $exportCity[ 'name' ], ' ', '_' ) . '.log';
                $this->executeCommand( $taskCommand, $logCommand );

                //export for the data entry forms
                if( isset( $exportCity['exportForDataEntry'] ) &&  $exportCity['exportForDataEntry'] )
                {

                     $currentExportPathDataEntry = $exportPathDataEntry .'/'.$type;
                     $this->executeCommand( 'mkdir -p '.$currentExportPathDataEntry , 'create data entry export folders' );
                     $taskCommand = $this->symfonyPath . '/./symfony projectn:prepareExportXMLsForDataEntry --env=' . $options['env'] . ' --city="' .  $exportCity[ 'name' ] . '" --application="' .  $options[ 'application' ]  . '" --language=' . $exportCity[ 'language' ] . ' --type="' . $type . '" --destination=' . $currentExportPathDataEntry . '/' . str_replace( " ", "_",  $exportCity[ 'name' ] ) .'.xml';

                     $taskCommand .= ' --xml=' . $currentExportPath . '/' . str_replace( " ", "_",  $exportCity[ 'name' ] ) .'.xml';

                    $logCommand  = $logPath . '/' . strtr( $exportCity[ 'name' ], ' ', '_' ) . '.log';
                    $this->executeCommand( $taskCommand, $logCommand );
                }
            }

        }

        $this->logSection( 'Runner' ,'tar archive for export backup' );
        $this->executeCommand( 'cd ' . $this->exportRootDir . ' && tar zcvf ' . 'exports_' . $timestamp . '.tgz ' . 'export_' . $timestamp . '/*', $logPath . '/common.log' );

        $this->logSection( 'Runner' , 'create upload.lock file' );
        $this->executeCommand( 'cd ' . $this->exportRootDir . ' && touch ' . $exportPath . '/upload.lock', $logPath . '/common.log' );

        $this->logSection( 'Runner' ,'delete exports older than ' . $deleteOlderThanDays );

        $deletedDirs = $this->_removeOldDirectoriesByPatternAndDaysInPast( $this->exportRootDir, '/^export_([0-9]{8})$/', $deleteOlderThanDays, $logPath . '/common.log' );
        $this->logSection( 'Runner' ,implode( ',', $deletedDirs )  );


    }

    /**
     * returns a list of cities to run the import/export
     *
     * @param integer $task
     * @param string $city
     * @return array
     */
    protected function getCities( $task, $city = null )
    {
        switch ( $task )
        {
        	case self::TASK_IMPORT:
        	    $citiesConfig = sfConfig::get( 'app_import_cities' );
                break;

            case self::TASK_EXPORT:
                $citiesConfig = sfConfig::get( 'app_export_cities' );
        		break;

            case self::TASK_UPDATE:
                $citiesConfig = sfConfig::get( 'app_update_cities' );
        		break;

        	default:
        	   $citiesConfig = array();
        	   break;
        }

        if( $city )
        {
            $selectedCity = array();
            foreach ($citiesConfig as $cityConfig )
            {
                if( $cityConfig['name'] == $city )
                {
                    $selectedCity = array( $cityConfig );
                }
            }
            $citiesConfig = $selectedCity;
        }

        return $citiesConfig;

    }


    protected function _removeOldDirectoriesByPatternAndDaysInPast( $dir, $pattern, $daysInPast, $logFile )
    {
      $deletedDirs = array();

      if ( is_dir( $dir ) )
      {
            $pathBeforeCall = getcwd();
            chdir( $dir );

            if ( $dh = opendir( $dir ) )
            {
                while ( ( $file = readdir( $dh ) ) !== false )
                {
                   if ( is_dir ( $file ) && preg_match( $pattern, $file, $matches ) )
                   {
                      if ( strtotime( $matches[ 1 ] ) < strtotime( '-' . $daysInPast )  )
                      {
                          $this->executeCommand( 'rm -r ' . $file, $logFile );
                          $deletedDirs[] = $file;
                      }
                   }
                }
                closedir( $dh );
            }
            chdir( $pathBeforeCall );
       }

       return $deletedDirs;
    }


    /**
     * Enter description here...
     *
     * @param unknown_type $path
     */
    protected function verifyAndCreatePath( $path )
    {
      if ( !file_exists( $path ) )
      {
            mkdir( $path, 0777, true );
      }
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $cmd
     * @param unknown_type $logfile
     */
    protected function executeCommand( $cmd, $logfile )
    {
        $this->logSection( 'EXEC', $cmd);
        $cmdOutput = shell_exec( $cmd . ' 2>&1' );
        file_put_contents( $logfile, $cmdOutput, FILE_APPEND );
    }

}
