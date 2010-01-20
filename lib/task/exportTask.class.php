<?php

class exportTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('type', null, sfCommandOption::PARAMETER_REQUIRED, 'The type of data we want to export (e.g. poi, event, movies'),
      new sfCommandOption('destination', null, sfCommandOption::PARAMETER_REQUIRED, 'The destination file where the output is written into'),
      new sfCommandOption('city', null, sfCommandOption::PARAMETER_REQUIRED, 'The city which we want to export'),
      new sfCommandOption('language', null, sfCommandOption::PARAMETER_REQUIRED, 'The language of the city we want to export', 'english'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'export';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [export|INFO] task does things.
Call it with:

  [php symfony export|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();



    //Select the city
    switch( $options['type'] )
    {
      case 'poi':
                  $vendor = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage( $options['city'], $options['language']);
                  $export = new XMLExportPOI( $vendor, $options['destination'] );
                  $export->run();
                  break;
      case 'event':
                  $vendor = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage( $options['city'], $options['language']);
                  $export = new XMLExportEvent( $vendor, $options['destination'] );
                  $export->run();
                  break;

    }





  }
}