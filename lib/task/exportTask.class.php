<?php

class exportTask extends sfBaseTask
{

  /**
   *
   * @var Vendor
   */
  private $_vendor;

  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name' ,'backend'),
      new sfCommandOption('type', null, sfCommandOption::PARAMETER_REQUIRED, 'The type of data we want to export (e.g. poi, event, movies'),
      new sfCommandOption('destination', null, sfCommandOption::PARAMETER_REQUIRED, 'The destination file where the output is written into'),
      new sfCommandOption('city', null, sfCommandOption::PARAMETER_REQUIRED, 'The city which we want to export'),
      new sfCommandOption('language', null, sfCommandOption::PARAMETER_REQUIRED, 'The language of the city we want to export', 'en-GB'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
      new sfCommandOption('poi-xml', null, sfCommandOption::PARAMETER_REQUIRED, 'Location of poi xml to check this export against', 'poop'),
      new sfCommandOption('validation', null, sfCommandOption::PARAMETER_REQUIRED, 'switch to decide if the exports will be validated', true),
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
     date_default_timezone_set( 'Europe/London' );
     $timer = sfTimerManager::getTimer('importTimer');

    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    $this->_vendor = Doctrine::getTable('Vendor')->findOneByCityAndLanguage( $options['city'], $options['language'] );

    ExportLogger::getInstance()->setVendor( $this->_vendor )->start();
    $this->getExporter( $options )->run();
    ExportLogger::getInstance()->end();

    $timer->addTime();
    $totalTime = $timer->getElapsedTime();

    echo "Total time: ". round($totalTime/60,2) . "\n";
  }

  /**
   *
   * @param string $type poi|event|movie
   */
  protected function getExporter( $options )
  {
    switch( strtolower($options['type']) )
    {
      case 'poi':
        $exportClass = 'XMLExportPOI';
        break;
      case 'event':

        //The poi's xml file contain no spaces
        $city = str_replace(' ', '_', $this->_vendor['city']);

        if( $options[ 'poi-xml' ] == 'poop' )
		      $location = 'export/export_'.date('Ymd').'/poi/'. $city .'.xml';
        else
          $location = $options[ 'poi-xml' ];

        return new XMLExportEvent( $this->_vendor, $options['destination'], $location , (boolean) $options[ 'validation' ]);
        break;
      case 'movie':
        $exportClass = 'XMLExportMovie';
        break;
      default:
        throw new Exception( 'No exporter available for type: "' . $options['type'] . '"' );
        break;
    }

    //$this->_vendor = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage( $options['city'], $options['language']);

    return new $exportClass( $this->_vendor, $options['destination'], (boolean) $options[ 'validation' ] );
  }
}
