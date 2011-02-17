<?php

class reSaveUpdateGeocodeLookUpInRussiaTask extends sfBaseTask
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
      new sfCommandOption('city', null, sfCommandOption::PARAMETER_REQUIRED, 'Enter City name', null),
      // add your own options here
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'reSaveUpdateGeocodeLookUpInRussia';
    $this->briefDescription = 'Resave Russia Vendors, update Geocode Lookup String to have Cityname First';
    $this->detailedDescription = <<<EOF
The [reSaveUpdateGeocodeLookUpInRussia|INFO] task does things.
Call it with:

  [php symfony reSaveUpdateGeocodeLookUpInRussia|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    if( $options['city'] == null )
    {
        throw new Exception( 'Invalid City, please use --city=city_name to specify vendor city' );
    }

    $vendor = Doctrine::getTable( 'Vendor' )->findOneByCity( $options['city'] );
    if( $vendor === false )
    {
        throw new Exception( 'Invalid City name specified, No vendor found for city "'.$options['city'].'"' );
    }

    // Load all POI's and Update geocode Lookup string if latitude / longitude is null
    $vendorPois = Doctrine::getTable( 'Poi' )->findByVendorId( $vendor['id'] );
    foreach( $vendorPois as $poi )
    {
        if( $poi['latitude'] != null && $poi['longitude'] != null )
        {
            continue; // No need to change as It's already have a valid geocode!
        }

        try{
            $this->logSection( 'Save', 'Updating Poi: ' . $poi['id'] );
            // Update geocode lookup string
            $poi['geocode_look_up'] = stringTransform::concatNonBlankStrings(', ', array( $poi['city'], $poi['house_no'], $poi['street'], $poi['zips'] ) );

            // Set geocoder as Default is google
            $geocoder = new yandexGeocoder();
            $geocoder->setApiKey( sfConfig::get('app_yandex_api_key') );
            $poi->setgeocoderr( $geocoder );
            
            $poi->save(); // This should Trigger Geocode Look up

        } catch ( Exception $e ){
            $this->logSection('Save', $e->getMessage(), null, 'ERROR' );
        }
    }
  }
}
