<?php

class duplicateExportedMovieFeedTask extends sfBaseTask
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
      new sfCommandOption('source', null, sfCommandOption::PARAMETER_REQUIRED, 'source vendor City name (Copy from) --source=moscow', null),
      new sfCommandOption('dest', null, sfCommandOption::PARAMETER_REQUIRED, 'destination vendor City name (Copy to) --dest=omsk', null),
      new sfCommandOption('directory', null, sfCommandOption::PARAMETER_REQUIRED, 'Export directory for movies folder --directory=export_20110218', 'export_' . date( 'Ymd') ),
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'duplicateExportedMovieFeed';
    $this->briefDescription = 'Duplicate exported Movie from one vendor to another by coping and updating details';
    $this->detailedDescription = <<<EOF
The [duplicateExportedMovieFeed|INFO] copy movie feed from source -> destination and update details to destination specific vendor
Call it with:

  [php symfony duplicateExportedMovieFeed|INFO]
EOF;
  }

    protected function execute($arguments = array(), $options = array())
    {
        // Validate parameters
        if( $options['source'] == null || empty( $options['source'] ) )
        {
            throw new DuplicateExportedMovieFeedTaskException( 'Invalid parameter source [source vendor City name (Copy from) --source="vendor city"]' );
        }
        
        if( $options['dest'] == null || empty( $options['dest'] ) )
        {
            throw new DuplicateExportedMovieFeedTaskException( 'Invalid parameter destination [destination vendor City name (Copy to) --dest=destinationcity]' );
        }
        
        if( $options['directory'] == null || empty( $options['directory'] ) )
        {
            throw new DuplicateExportedMovieFeedTaskException( 'Invalid parameter directory [Export directory for movies folder --directory=export_20110218]' );
        }

        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();
        
        // validate vendors
        $sourceVendor = Doctrine::getTable( 'Vendor' )->findOneByCity( $options['source'] );
        if( $sourceVendor === false )
        {
            throw new DuplicateExportedMovieFeedTaskException( 'Invalid source vendor city "'.$options['source'].'"' );
        }
        
        $destVendor = Doctrine::getTable( 'Vendor' )->findOneByCity( $options['dest'] );
        if( $destVendor === false )
        {
            throw new DuplicateExportedMovieFeedTaskException( 'Invalid destination vendor city "'.$options['dest'].'"' );
        }

        // vaidate export dir and movies
        //if(is_dir( ))
print_r( sfConfig::getAll() );
        // vaidate Export directory exists


        // add your code here
    }
}

class DuplicateExportedMovieFeedTaskException extends Exception{}