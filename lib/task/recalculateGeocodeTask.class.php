<?php

class recalculateGeocodeTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
      // add your own options here
      new sfCommandOption('id', null, sfCommandOption::PARAMETER_OPTIONAL, 'The POI id'),
      new sfCommandOption('method', null, sfCommandOption::PARAMETER_OPTIONAL, 'The method to retrieve POIs with'),
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'recalculate-geocode';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [same-geocodes|INFO] task does things.
Call it with:

  [php symfony fetch-geocode|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    //initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    $job = new resavePoisWithGeocodesOutsideOfBoundingBox();
    $job->run();
  }
}
