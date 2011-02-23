<?php

class chicagoFtpArchiverTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
      // add your own options here
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'chicagoFtpArchiver';
    $this->briefDescription = 'Download Chicago FTP files into local import folder';
    $this->detailedDescription = <<<EOF
The [chicagoFtpArchiver|INFO] Download available files from chicago FTP to load/import/chicago folder
Call it with:

  [php symfony chicagoFtpArchiver|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    //print_r(sfConfig::getAll());
    // Load Chicago Yaml File
    $chicagoSettings = sfYaml::load( sfConfig::get('sf_config_dir') . '/projectn/chicago.yml' );

    $this->logSection('FTP Download', 'Start: ' . date('d/m/Y H:i:s ') );
    // go-through each of the import task and Download Files using FTP into local folder...
    $processedFiles = array(); // Since Split-running feed introduced, some files are repeated. This array will help prevent download old files again
    $downloadDestination = sfConfig::get('projectn_import') . '/chicago';

    foreach( $chicagoSettings['import'] as $importModel )
    {
        $ftpSettings = isset( $importModel['class']['params']['ftp'] ) ? $importModel['class']['params']['ftp'] : NULL;

        if( $ftpSettings === NULL || in_array( $ftpSettings['file'], $processedFiles ) )
        {
            continue;
        }

        $processedFiles[] = $ftpSettings['file'];

        try
        {
            // Init FTP client and Download
            $ftpClient = new FTPClient( $ftpSettings['ftp'], $ftpSettings['username'], $ftpSettings['password'], 'chicago' );
            $ftpClient->fetchLatestFileByPattern( $ftpSettings['file'] );
        } catch (Exception $ex ) {
            $this->logSection('FTP Download', 'File:' . $ftpSettings['file'] . ' @ ' . $ex->getMessage(), null, 'ERROR' );
        }
        
    }
    $this->logSection('FTP Download', 'End: ' . date('d/m/Y H:i:s ') );
  }
}
