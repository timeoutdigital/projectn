<?php

class fixDuplicateCategoriesTask extends sfBaseTask
{
  protected function configure()
  {

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n')
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'fix-duplicate-categories';
    $this->briefDescription = 'Merge duplicate categories';
    $this->detailedDescription = <<<EOF
The [fix-duplicate-categories|INFO] fixes duplicate categories by merging duplicate categories then removing unused categories.
Call it with:

  [php symfony fix-duplicate-categories|INFO]
EOF;
  }

  protected function execute( $arguments = array(), $options = array() )
  {
    // Configure Database.
    $databaseManager = new sfDatabaseManager( $this->configuration );
    $connection = $databaseManager->getDatabase( $options['connection'] ? $options['connection'] : null )->getConnection();

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
        $eventCategory->delete();
    }
  }
}