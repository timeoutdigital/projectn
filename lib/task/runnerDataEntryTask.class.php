<?php

class runnerDataEntryTask extends runnerTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
      // add your own options here
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'runnerDataEntry';
    $this->briefDescription = '';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    date_default_timezone_set( 'Europe/London' );

    $symfonyPath = sfConfig::get( 'sf_root_dir' );
    $logRootDir = sfConfig::get( 'sf_log_dir' );
    $exportRootDir = sfConfig::get( 'sf_root_dir' ) . '/export';

    $taskArray = array (
                    'export' => array(
                            'mumbai' => array( 'language' => 'en-GB', 'type' => array( 'poi', 'event', 'movie' ) ),
                            'dehli' => array( 'language' => 'en-GB', 'type' => array( 'poi', 'event', 'movie' ) ),
                            'bangalore' => array( 'language' => 'en-GB', 'type' => array( 'poi', 'event', 'movie' ) ),
                            'pune' => array( 'language' => 'en-GB', 'type' => array( 'poi', 'event', 'movie' ) )
                    ),
                 );

    foreach ( $taskArray as $task => $command )
    {
        switch( $task )
        {
            case 'export' :
                $deleteOlderThanDays = '7 days';
                $timestamp = date( 'Ymd' );
                $exportPath = $exportRootDir . '/export_' . $timestamp;
                $this->verifyAndCreatePath( $exportPath );

                foreach ( $command as $cityName => $cityParams )
                {
                    foreach( $cityParams[ 'type' ] as $type )
                    {
                        echo date( 'Y-m-d H:i:s' ) . ' - running ' . $task . ' for ' . $cityName . ' (' . $type . ')' . PHP_EOL;

                        $logPath = $logRootDir . '/' . $task;
                        $this->verifyAndCreatePath( $logPath );
                        $currentExportPath = $exportPath .'/'.$type;
                        $this->verifyAndCreatePath( $currentExportPath );

                        $taskCommand = $symfonyPath . '/./symfony projectn:' . $task . '  --env=' . $options['env'] . ' --city="' . $cityName . '" --language=' . $cityParams[ 'language' ] . ' --type="' . $type . '" --destination=' . $currentExportPath . '/' . str_replace( " ", "_", $cityName ) .'.xml';
                        $logCommand  = $logPath . '/' . strtr( $cityName, ' ', '_' ) . '.log';
                        $this->executeCommand( $taskCommand, $logCommand );
                    }
                }

                echo 'tar archive for export backup' . PHP_EOL;
                $this->executeCommand( 'cd ' . $exportRootDir . ' && tar zcvf ' . 'exports_' . $timestamp . '.tgz ' . 'export_' . $timestamp . '/*', $logPath . '/common.log' );

                echo 'create upload.lock file' . PHP_EOL;
                $this->executeCommand( 'cd ' . $exportRootDir . ' && touch ' . $exportPath . '/upload.lock', $logPath . '/common.log' );

                echo 'delete exports older than ' . $deleteOlderThanDays . ' (';
                $deletedDirs = $this->_removeOldDirectoriesByPatternAndDaysInPast( $exportRootDir, '/^export_([0-9]{8})$/', $deleteOlderThanDays, $logPath . '/common.log' );
                echo  implode( ',', $deletedDirs ) . ')' . PHP_EOL;

                break;
        }
    }

  }

}
