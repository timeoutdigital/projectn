<?php

class updateCategoryMappingsTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
      new sfCommandOption('type', null, sfCommandOption::PARAMETER_REQUIRED, 'Update mappings for the following entity', 'all'),
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

    //create category mapper instance
    $categoryMapper = new CategoryMap();

    //Select the task
    if ( in_array( $options['type'], array( 'all', 'poi') ) )
    {
          $pois = Doctrine::getTable( 'Poi' )->findAll();

          foreach ( $pois as $poi )
          {

              $vendor = Doctrine::getTable( 'Vendor' )->findOneById( $poi[ 'vendor_id'] );

              $categoriesToBeMapped = array();

              foreach ( $poi[ 'VendorPoiCategories' ] as $poisVendorPoiCategory )
              {
                  $categoriesToBeMapped[] = $poisVendorPoiCategory[ 'name'];
              }

              $poi->unlink( 'PoiCategories' );

              if ( 0 < count( $categoriesToBeMapped ) )
              {
                  $poi[ 'PoiCategories' ] = $categoryMapper->mapCategories( $vendor, $categoriesToBeMapped, 'Poi' );
              }

              $poi->setGeoEncodeByPass( true );
              $poi->save();
          }
    }
    if ( in_array( $options['type'], array( 'all', 'event') ) )
    {
          $events = Doctrine::getTable( 'Event' )->findAll();

          foreach ( $events as $event )
          {

              $vendor = Doctrine::getTable( 'Vendor' )->findOneById( $event[ 'vendor_id'] );

              $categoriesToBeMapped = array();

              foreach ( $event[ 'VendorEventCategories' ] as $eventsVendorEventCategory )
              {
                  $categoriesToBeMapped[] = $eventsVendorEventCategory[ 'name'];
              }

              $event->unlink( 'EventCategories' );

              if ( 0 < count( $categoriesToBeMapped ) )
              {
                  $event[ 'EventCategories' ] = $categoryMapper->mapCategories( $vendor, $categoriesToBeMapped, 'Event' );
              }

              $event->save();
          }
    }


  }
}
