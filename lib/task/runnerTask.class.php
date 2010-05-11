<?php

class runnerTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'backend'),
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'runner';
    $this->briefDescription = 'wrapper task to run import and export tasks for projectn';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    //$databaseManager = new sfDatabaseManager($this->configuration);
    //$connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    $symfonyPath = sfConfig::get( 'sf_root_dir' );
    $logRootDir = sfConfig::get( 'sf_log_dir' );
    $exportRootDir = sfConfig::get( 'sf_root_dir' ) . '/export';


    $taskArray = array (
                    'import' => array(
                                    'singapore' => array( 'poi-event', 'movie' ),
                                    'ny' => array( 'poi-event', 'eating-drinking', 'bars-clubs', 'movie' ),
                                    'chicago' => array( 'poi-event', 'eating-drinking', 'bars-clubs', 'movie' ),
                                    'london' => array( 'poi-event', 'movie' ),
                                    'lisbon' => array( 'poi', 'event', 'movie' ),
                    ),
                    'export' => array(
                                    'singapore' => array( 'language' => 'en-US', 'type' => array( 'poi', 'event', 'movie' ) ),
                                    'ny' => array( 'language' => 'en-US', 'type' => array( 'poi', 'event', 'movie' ) ),
                                    'chicago' => array( 'language' => 'en-US', 'type' => array( 'poi', 'event', 'movie' ) ),
                                    'london' => array( 'language' => 'en-GB', 'type' => array( 'poi', 'event', 'movie' ) ),
                                    'lisbon' => array( 'language' => 'pt', 'type' => array( 'poi', 'event', 'movie' ) ),
                    ),
                 );

    foreach ( $taskArray as $task => $command )
    {
        switch( $task )
        {
            case 'import' :

                foreach ( $command as $cityName => $cityParams )
                {
                    foreach( $cityParams as $type )
                    {
                        echo 'running ' . $task . ' for ' . $cityName . ' (' . $type . ')' . PHP_EOL;

                        $logPath = $logRootDir . '/' . $task;
                        $this->verifyAndCreatePath( $logPath );                        
                        $this->executeCommand( $symfonyPath . '/./symfony projectn:' . $task . '  --env=' . $options['env'] . ' --city=' . $cityName . ' --type=' . $type, $logPath . '/' . $cityName . '.log' );
                    }
                }

                break;

            case 'export' :
                $timestamp = date( 'Ymd' );
                $exportPath = $exportRootDir . '/export_' . $timestamp;
                $this->verifyAndCreatePath( $exportPath );

                foreach ( $command as $cityName => $cityParams )
                {
                    foreach( $cityParams[ 'type' ] as $type )
                    {
                        echo 'running ' . $task . ' for ' . $cityName . ' (' . $type . ')' . PHP_EOL;

                        $logPath = $logRootDir . '/' . $task;
                        $this->verifyAndCreatePath( $logPath );
                        $currentExportPath = $exportPath .'/'.$type;
                        $this->verifyAndCreatePath( $currentExportPath );
                        $this->executeCommand( $symfonyPath . '/./symfony projectn:' . $task . '  --env=' . $options['env'] . ' --city=' . $cityName . ' --language=' . $cityParams[ 'language' ] . ' --type=' . $type . ' --destination=' . $currentExportPath . '/' . $cityName .'.xml', $logPath . '/' . $cityName . '.log' );
                    }
                }

                echo 'zipping it all up ' . PHP_EOL;               
                $this->executeCommand( 'cd ' . $exportRootDir . ' && touch ' . $exportPath . '/upload.lock && tar zcvf ' . 'exports_' . $timestamp . '.tgz ' . 'export_' . $timestamp . '/*', $logPath . '/common.log' );

                break;
        }
    }

  }

  private function verifyAndCreatePath( $path ) {
      if ( !file_exists( $path ) ) {
            mkdir( $path, 0777, true );
      }
  }

  private function executeCommand( $cmd, $logfile )
  {
    $cmdOutput = shell_exec( $cmd . ' 2>&1' );
    file_put_contents( $logfile, $cmdOutput, FILE_APPEND );
  }

}
