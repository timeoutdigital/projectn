<?php

class mediaDownloadTask extends sfBaseTask
{
  protected function configure()
  {

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'media-download';
    $this->briefDescription = 'Download & Update Media Files.';
    $this->detailedDescription = <<<EOF
The [media-download|INFO] task does things.
Call it with:

  [php symfony media-download|INFO]
EOF;
  }

  protected function setUp( $options = array() )
  {
    // Configure Database.
    $databaseManager = new sfDatabaseManager( $this->configuration );
    $connection = $databaseManager->getDatabase( $options['connection'] ? $options['connection'] : null )->getConnection();
  }

  protected function execute( $arguments = array(), $options = array())
  {
    $this->setUp( $options );
  }
}