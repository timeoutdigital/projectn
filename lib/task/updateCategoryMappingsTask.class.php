<?php

class updateCategoryMappingsTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
      new sfCommandOption('type', null, sfCommandOption::PARAMETER_REQUIRED, 'Update mappings for the following entity', 'all'),
      new sfCommandOption('verbose', null, sfCommandOption::PARAMETER_OPTIONAL, 'Output processing of nodes', 'false'),
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'update-mapping';
    $this->briefDescription = 'Updates the mapping on the entities (poi, event or all (default))';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    //Connect to the database.
    $databaseManager = new sfDatabaseManager($this->configuration);
    Doctrine_Manager::getInstance()->setAttribute( Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_ALL );
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();
    
    $timer = sfTimerManager::getTimer('importTimer');

    //create category mapper instance
    $categoryMapper = new CategoryMap();

    $vendors = Doctrine::getTable( 'Vendor' )->findAll();

    //Select the task
    if ( in_array( $options['type'], array( 'all', 'poi') ) )
    {
        foreach( $vendors as $vendor )
        {
          $pois = Doctrine::getTable( 'Poi' )->findByVendorId( $vendor[ 'id' ] );

          $totalPois = $pois->count();

          $poiCounter = $totalPois;

          foreach ( $pois as $poi )
          {

              $poi->unlink( 'PoiCategory' );

              if ( 0 < count( $poi[ 'VendorPoiCategory' ] ) )
              {
                  $poi[ 'PoiCategory' ] = $categoryMapper->mapCategories( $vendor, $poi[ 'VendorPoiCategory' ], 'Poi' );
              }

              $poi->setgeocoderByPass( true );
              $poi->save();
              $poiCounter--;

              if ( $options['verbose'] == 'true' )
              {
                  echo "vendor " . $vendor[ 'city' ] . " / poi " . $poiCounter . ' of ' . $totalPois . PHP_EOL;
              }
          }
        }        
    }

    if ( in_array( $options['type'], array( 'all', 'event') ) )
    {
        foreach( $vendors as $vendor )
        {
          $events = Doctrine::getTable( 'Event' )->findByVendorId( $vendor[ 'id' ] );

          $totalEvents = $events->count();

          $eventCounter = $totalEvents;

          foreach ( $events as $event )
          {

              $event->unlink( 'EventCategory' );

              if ( 0 < count( $event[ 'VendorEventCategory' ] ) )
              {
                  $event[ 'EventCategory' ] = $categoryMapper->mapCategories( $vendor, $event[ 'VendorEventCategory' ], 'Event' );
              }

              $event->save();
              $eventCounter--;

              if ( $options['verbose'] == 'true' )
              {
                  echo "vendor " . $vendor[ 'city' ] . " / event " . $eventCounter . ' of ' . $totalEvents . PHP_EOL;
              }
          }
        }
    }

    $timer->addTime();
    $totalTime = $timer->getElapsedTime();

    echo "Total time: ". $totalTime . "\n";

  }
}
