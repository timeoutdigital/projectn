<?php

class geoCodeFetchTask extends sfBaseTask
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
      new sfCommandOption('id', null, sfCommandOption::PARAMETER_REQUIRED, 'The POI id'),
      // add your own options here
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'fetch-geocode';
    $this->briefDescription = 'Fetch a geocode for the specified POI id.';
    $this->detailedDescription = <<<EOF
The [same-geocodes|INFO] task does things.
Call it with:

  [php symfony fetch-geocode|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    $poiObj = Doctrine::getTable('Poi')->findById( $options['id'] );

    //print_r( $poiObj->toArray(true) );
    $address = stringTransform::concatNonBlankStrings( ', ', array( $poiObj[0][ 'street' ], $poiObj[0][ 'additional_address_details' ], $poiObj[0][ 'zips' ], $poiObj[0][ 'city' ]  ) );
    $g = new googleGeocoder();
    $g->setAddress( $address );
    $g->getGeoCode();

    echo "====================================" . PHP_EOL;
    echo "Finding POI by ID: " . $options['id'] . PHP_EOL;
    echo "====================================" . PHP_EOL;
    echo "Current values in Database:" . PHP_EOL;
    echo "------------------------------------" . PHP_EOL;
    echo( "Latitude: '" . $poiObj[0]['latitude'] . "'" . PHP_EOL );
    echo( "Longitude: '" . $poiObj[0]['longitude'] . "'" . PHP_EOL );
    echo "====================================" . PHP_EOL;
    echo "Querying Google API:" . PHP_EOL;
    echo "------------------------------------" . PHP_EOL;
    echo( "Latitude: '" . $g->getLatitude() . "'" . PHP_EOL );
    echo( "Longitude: '" . $g->getLongitude() . "'" . PHP_EOL );
    echo( "Accuracy: '" . $g->getAccuracy() . "'" . PHP_EOL );
    echo( "Address String: '" . $address . "'" . PHP_EOL );
    echo( "Google Url: '" . $g->getLookupUrl() . "'" . PHP_EOL );
    echo "====================================" . PHP_EOL;

  }
}
