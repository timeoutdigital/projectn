<?php

class fixVendorCategoriesTask extends sfBaseTask
{
  protected function configure()
  {

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
      new sfCommandOption('city', null, sfCommandOption::PARAMETER_REQUIRED, 'Vendor City name', null),
      new sfCommandOption('model', null, sfCommandOption::PARAMETER_REQUIRED, 'Model poi / event', null),
      new sfCommandOption('fix', null, sfCommandOption::PARAMETER_REQUIRED, 'Clean options [duplicate-unused, unused or blacklist-duplicate-unused]', null),
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'fix-vendor-categories';
    $this->briefDescription = 'Delete duplicate and un-used categories and remove black listed categories';
  }

  protected function execute( $arguments = array(), $options = array() )
  {
      // validate parameters
      if( !isset($options['city']) || empty($options['city']) )
      {
          throw new Exception( 'No city name provided, use --city="city name"' );
      }
      if( $options['model'] == null )
      {
          throw new Exception( 'Model required, use --model="poi OR event"' );

      } else if( !in_array(strtolower( $options['model'] ), array( 'poi', 'event')  ) )
      {
          throw new Exception( 'Invalid model found, use --model="poi OR event"' );
      }
      if( $options['fix'] == null )
      {
          throw new Exception( 'fix required, use --fix="unused OR duplicate-unused OR blacklist-duplicate-unused"' );

      }else if( !in_array( strtolower( $options['fix'] ), array( 'duplicate-unused', 'unused', 'blacklist-duplicate-unused' ) ) )
      {
          throw new Exception( 'Invalid fix option, use --fix="unused OR duplicate-unused OR blacklist-duplicate-unused"' );
      }

    // Configure Database.
    $databaseManager = new sfDatabaseManager( $this->configuration );
    $connection = $databaseManager->getDatabase( $options['connection'] ? $options['connection'] : null )->getConnection();

    // get vendor By city name
    $vendor =  Doctrine::getTable( 'Vendor' )->findOneByCity( $options['city'] );
    if( $vendor === false )
    {
        throw new Exception('Invalid City name, No vendor details found using ' . $options['city'] );
    }



    $this->_deleteUnusedPoiCategories();
    $this->_deleteUnusedEventCategories();

    $this->_mergeDuplicatePoiCategories();
    $this->_mergeDuplicateEventCategories();

    $this->_deleteUnusedPoiCategories();
    $this->_deleteUnusedEventCategories();
  }

  private function _mergeDuplicatePoiCategories()
  {
    $duplicatePoiCategoriesArray = Doctrine::getTable( 'VendorPoiCategory' )
        ->createQuery()
        ->select('GROUP_CONCAT( id ) as dupeIds')
        ->groupBy('name, vendor_id')
        ->having('count(*) > 1')
        ->execute( array(), Doctrine::HYDRATE_ARRAY );

    if( $duplicatePoiCategoriesArray === false ) return;
    
    foreach( $duplicatePoiCategoriesArray as $duplicateCategory )
    {
        $dupeIds = explode( ',', $duplicateCategory['dupeIds'] );

        Doctrine_Query::create()
            ->update( 'LinkingVendorPoiCategory' )
            ->set( 'vendor_poi_category_id', '?', $dupeIds[0] )
            ->where( 'vendor_poi_category_id IN ('.implode( ',', $dupeIds ).')' )
            ->execute();
    }
  }

  private function _mergeDuplicateEventCategories()
  {
    $duplicateEventCategoriesArray = Doctrine::getTable( 'VendorEventCategory' )
        ->createQuery()
        ->select('GROUP_CONCAT( id ) as dupeIds')
        ->groupBy('name, vendor_id')
        ->having('count(*) > 1')
        ->execute( array(), Doctrine::HYDRATE_ARRAY );

    if( $duplicateEventCategoriesArray === false ) return;

    foreach( $duplicateEventCategoriesArray as $duplicateCategory )
    {
        $dupeIds = explode( ',', $duplicateCategory['dupeIds'] );

        Doctrine_Query::create()
            ->update( 'LinkingVendorEventCategory' )
            ->set( 'vendor_event_category_id', '?', $dupeIds[0] )
            ->where( 'vendor_event_category_id IN ('.implode( ',', $dupeIds ).')' )
            ->execute();
    }
  }

  private function _deleteUnusedPoiCategories()
  {
    $unusedPoiCategories = Doctrine::getTable( 'VendorPoiCategory' )->createQuery('vpc')
        ->where('vpc.id NOT IN ( SELECT lvpc.vendor_poi_category_id FROM LinkingVendorPoiCategory lvpc )')
        ->execute();

    foreach( $unusedPoiCategories as $poiCategory )
    {
        foreach( $poiCategory[ 'LinkingPoiCategoryMapping' ] as $unusedMapping )
        {
            $unusedMapping->delete();
        }

        foreach( $poiCategory[ 'LinkingVendorPoiCategoryUiCategory' ] as $unusedMapping )
        {
            $unusedMapping->delete();
        }
        
        $poiCategory->delete();
    }
  }
  
  private function _deleteUnusedEventCategories()
  {
    $unusedEventCategories = Doctrine::getTable( 'VendorEventCategory' )->createQuery('vec')
        ->where('vec.id NOT IN ( SELECT lvpc.vendor_event_category_id FROM LinkingVendorEventCategory lvpc )')
        ->execute();

    foreach( $unusedEventCategories as $eventCategory )
    {
        foreach( $eventCategory[ 'LinkingEventCategoryMapping' ] as $unusedMapping )
        {
            $unusedMapping->delete();
        }

        foreach( $eventCategory[ 'LinkingVendorEventCategoryUiCategory' ] as $unusedMapping )
        {
            $unusedMapping->delete();
        }

        $eventCategory->delete();
    }
  }
}