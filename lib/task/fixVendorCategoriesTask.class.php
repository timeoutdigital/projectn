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

    $model = strtolower( trim( $options['model'] ) );
    switch( $options['fix'] )
    {
        case 'unused':
            ( $model == 'poi' ) ? $this->_deleteUnusedPoiCategories( $vendor ) : $this->_deleteUnusedEventCategories( $vendor );
            break;
        
        case 'duplicate-unused': // You cannot remove duplicates and leave them as 
            // Before Mapping Duplicates to one and un-linking other, we should remove un-used categories
            // and again after to clean newly generated un-used categories
            ( $model == 'poi' ) ? $this->_deleteUnusedPoiCategories( $vendor ) : $this->_deleteUnusedEventCategories( $vendor );
            ( $model == 'poi' ) ? $this->_mergeDuplicatePoiCategories( $vendor ) : $this->_mergeDuplicateEventCategories( $vendor );
            ( $model == 'poi' ) ? $this->_deleteUnusedPoiCategories( $vendor ) : $this->_deleteUnusedEventCategories( $vendor );
            break;

        case 'blacklist-duplicate-unused':
            ( $model == 'poi' ) ? $this->_deleteUnusedPoiCategories( $vendor ) : $this->_deleteUnusedEventCategories( $vendor );
            $this->_cleanBlackListedCategories( $vendor, $model );
            ( $model == 'poi' ) ? $this->_mergeDuplicatePoiCategories( $vendor ) : $this->_mergeDuplicateEventCategories( $vendor );
            ( $model == 'poi' ) ? $this->_deleteUnusedPoiCategories( $vendor ) : $this->_deleteUnusedEventCategories( $vendor );
            break;
    }
  }

  private function _mergeDuplicatePoiCategories( Vendor $vendor )
  {
    $duplicatePoiCategoriesArray = Doctrine::getTable( 'VendorPoiCategory' )->findConcatDuplicateCategoryIdBy( $vendor['id'], Doctrine::HYDRATE_ARRAY );

    if( $duplicatePoiCategoriesArray === false ) return;
    
    foreach( $duplicatePoiCategoriesArray as $duplicateCategory )
    {
        $dupeIds = explode( ',', $duplicateCategory['dupeIds'] );
        Doctrine::getTable( 'LinkingVendorPoiCategory' )->mapCategoriesTo( $dupeIds[0], $dupeIds );
    }
  }

  private function _mergeDuplicateEventCategories( Vendor $vendor )
  {
    $duplicateEventCategoriesArray = Doctrine::getTable( 'VendorEventCategory' )->findConcatDuplicateCategoryIdBy( $vendor['id'], Doctrine::HYDRATE_ARRAY );

    if( $duplicateEventCategoriesArray === false ) return;

    foreach( $duplicateEventCategoriesArray as $duplicateCategory )
    {
        $dupeIds = explode( ',', $duplicateCategory['dupeIds'] );
        Doctrine::getTable( 'LinkingVendorEventCategory' )->mapCategoriesTo( $dupeIds[0], $dupeIds );
    }
  }

  private function _deleteUnusedPoiCategories( Vendor $vendor )
  {
    $unusedPoiCategories = Doctrine::getTable( 'VendorPoiCategory' )->createQuery('vpc')
        ->where('vpc.id NOT IN ( SELECT lvpc.vendor_poi_category_id FROM LinkingVendorPoiCategory lvpc )')
        ->andWhere('vpc.vendor_id = ? ', $vendor['id'] )
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
  
  private function _deleteUnusedEventCategories( Vendor $vendor )
  {
    $unusedEventCategories = Doctrine::getTable( 'VendorEventCategory' )->createQuery('vec')
        ->where('vec.id NOT IN ( SELECT lvpc.vendor_event_category_id FROM LinkingVendorEventCategory lvpc )')
        ->andWhere('vec.vendor_id = ? ', $vendor['id'] )
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

  private function _cleanBlackListedCategories( Vendor $vendor, $model )
  {
      // This require to get All vendor related categories and filter for black listed categories
      $vendorModelRecords = Doctrine::getTable( ucfirst( $model ) )->findByVendorId( $vendor['id'] );

      if( $vendorModelRecords === false ) return;

      $vendorModelCategory = "Vendor".ucfirst( $model)."Category";
      $tblBlackList = Doctrine::getTable( 'VendorCategoryBlackList' );
      
      foreach( $vendorModelRecords as $record )
      {
          $this->logSection( $model, 'Processing Record: ' . $record['id'] );
          
          foreach( $record[$vendorModelCategory] as $vendorCategory )
          {              
              // split them into single category as BlackList takes array of categories
              $categoryArray = explode('|', $vendorCategory['name'] );
              $cleanCategories = $tblBlackList->filterByCategoryBlackList( $vendor['id'], $categoryArray );

              // ublink when Nothing found
              if( is_array( $cleanCategories ) && empty( $cleanCategories ) )
              {
                  $this->logSection( $model, 'Removing Category ID : ' . $vendorCategory['id'] );
                  $record->unlink( $vendorModelCategory, $vendorCategory['id'] );

              }elseif( is_array( $cleanCategories ) )
              {
                  // there were some categorids that duplicated it self like (Theatre | Theatre),
                  // array_uniqe ensure that duplicated categories in one record separated by |
                  $vendorCategory['name'] = stringTransform::concatNonBlankStrings(' | ', array_unique( $cleanCategories ) );
              }

          }
          $this->logSection( $model, 'Updating Record: ' . $record['id'] );
          // save record to Update changes
          $record->save(); // any exception throws will STOP the process as this is crusial to just Log and ignore!
      }
  }
}