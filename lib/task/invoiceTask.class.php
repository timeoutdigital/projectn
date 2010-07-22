<?php

class invoiceTask extends sfBaseTask
{
  protected function configure()
  {

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
      new sfCommandOption('type', null, sfCommandOption::PARAMETER_REQUIRED, 'poi, event, movie', 'poi'),
      new sfCommandOption('csv', null, sfCommandOption::PARAMETER_REQUIRED, 'produce as csv', 'false'),
      // add your own options here
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'invoice';
    $this->briefDescription = 'Produce invoice from exports';
    $this->detailedDescription = <<<EOF
The [invoice|INFO] task does things.
Call it with:

  [php symfony invoice|INFO]
EOF;

    define( 'DIR_ALL', 'all' );
    define( 'DIR_FILES', 'files' );
    define( 'DIR_FOLDERS', 'folders' );
  }

  protected function execute($arguments = array(), $options = array())
  {
    echo PHP_EOL;

    $this->storeVendorPoiIds = array();
    $this->storeVendorEventIds = array();
    $this->storeVendorMovieIds = array();
    $this->csv = ( $options['csv'] == "true" );
    $this->delim = ",";
    $this->existingPoiCount = array();
    $this->existingEventCount = array();
    $this->existingMovieCount = array();

    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

//    $poiVendorCategoryMappings = Doctrine::getTable('UiCategory')
//            ->createQuery('vc')
//                ->leftJoin('vc.LinkingVendorPoiCategoryUiCategory link ON vc.id = link.vendor_poi_category_id')
//                    ->leftJoin('link.UiCategory ui ON ui.id = vc.ui_category_id')
//            ->where("vc.name IS NOT NULL")
//            //->addWhere("ui.name IS NOT NULL")
//            ->execute();

    $uiCats = Doctrine::getTable('UiCategory')->findAll();

//SELECT vc.name, ui.name
//FROM vendor_poi_category vc
//LEFT JOIN linking_vendor_poi_category_ui_category pui
//	ON vc.id = pui.vendor_poi_category_id
//LEFT JOIN ui_category ui
//	ON pui.ui_category_id = ui.id
//WHERE vc.name IS NOT NULL
//AND ui.name IS NOT NULL

    $this->uiCatsSimpleArrayNamesOnly = array();
    foreach( $uiCats as $map )
        $this->uiCatsSimpleArrayNamesOnly[] = $map['name'];

    $poiUiCategoryMap = array();

    foreach( $uiCats as $map )
        foreach( $map['VendorPoiCategory'] as $m )
            $poiUiCategoryMap[ $m['name'] ] = $map['name'];

    $eventUiCategoryMap = array();

    foreach( $uiCats as $map )
        foreach( $map['VendorEventCategory'] as $m )
            $eventUiCategoryMap[ $m['name'] ] = $map['name'];

    $baseDir = "invoice-test/exports";

    $folders = $this->readDir( $baseDir, DIR_FOLDERS );

    $this->reportHeader();

    sort( $folders );

    foreach( $folders as $folder )
    {
        switch( $options['type'] )
        {
            case 'poi':
                
                $poiFiles = $this->readDir( $baseDir."/".$folder."/poi", DIR_FILES );
                sort( $poiFiles );

                foreach( $poiFiles as $city_xml_file )
                {
                    $xml = simplexml_load_file( $baseDir."/".$folder."/poi/".$city_xml_file );
                    $totalPois = count( $xml->xpath( '/vendor-pois/entry' ) );

                    $cutCityName = explode( ".", $city_xml_file );
                    $date = strtotime( str_replace( "export_", "", $folder ) );

                    $uiCategories = array();

                    if( !array_key_exists( $cutCityName[ 0 ], $this->existingPoiCount ) )
                        $this->existingPoiCount[ $cutCityName[ 0 ] ] = 0;

                    $droppedCount = 0;

                    foreach( $xml->entry as $node )
                    {
                        $vendorPoiId = (int) substr( $node['vpid'], 25 );
                        if( in_array( $vendorPoiId, $this->storeVendorPoiIds ) ) continue;

                        foreach( $node->version->content->{'vendor-category'} as $cat )
                        {
                            $catName = stringTransform::mb_trim( (string) $cat );

                            if( in_array( $catName, array_keys( $poiUiCategoryMap ) ) )
                            {
                                if( !array_key_exists( $poiUiCategoryMap[ $catName ], $uiCategories ) )
                                    $uiCategories[ $poiUiCategoryMap[ $catName ] ] = 0;

                                $this->storeVendorPoiIds[] = $vendorPoiId;
                                $uiCategories[ $poiUiCategoryMap[ $catName ] ]++;
                                $this->existingPoiCount[ $cutCityName[ 0 ] ]++;
                                continue 2;
                            }
                        }
                    }

                    echo $this->report( date( "Y-m-d", $date ), ucfirst( $cutCityName[0] ), $totalPois, $uiCategories, $this->existingPoiCount[ $cutCityName[ 0 ] ] );
                }

            break;

            case 'event':
                
                $eventFiles = $this->readDir( $baseDir."/".$folder."/event", DIR_FILES );
                sort( $eventFiles );

                foreach( $eventFiles as $city_xml_file )
                {
                    $xml = simplexml_load_file( $baseDir."/".$folder."/event/".$city_xml_file );
                    $totalPois = count( $xml->xpath( '/vendor-events/event' ) );

                    $cutCityName = explode( ".", $city_xml_file );
                    $date = strtotime( str_replace( "export_", "", $folder ) );

                    $uiCategories = array();

                    if( !array_key_exists( $cutCityName[ 0 ], $this->existingEventCount ) )
                        $this->existingEventCount[ $cutCityName[ 0 ] ] = 0;

                    $droppedCount = 0;

                    foreach( $xml->event as $node )
                    {
                        $vendorEventId = (int) substr( $node['id'], 25 );

                        if( in_array( $vendorEventId, $this->storeVendorEventIds ) ) continue;

                        foreach( $node->version->{'vendor-category'} as $cat )
                        {
                            $catName = stringTransform::mb_trim( (string) $cat );

                            if( in_array( $catName, array_keys( $eventUiCategoryMap ) ) )
                            {
                                if( !array_key_exists( $eventUiCategoryMap[ $catName ], $uiCategories ) )
                                    $uiCategories[ $eventUiCategoryMap[ $catName ] ] = 0;

                                $this->storeVendorEventIds[] = $vendorEventId;
                                $uiCategories[ $eventUiCategoryMap[ $catName ] ]++;
                                $this->existingEventCount[ $cutCityName[ 0 ] ]++;
                                continue 2;
                            }
                        }
                    }

                    echo $this->report( date( "Y-m-d", $date ), ucfirst( $cutCityName[0] ), $totalPois, $uiCategories, $this->existingEventCount[ $cutCityName[ 0 ] ] );
                }

           break;

            case 'movie':

                $eventFiles = $this->readDir( $baseDir."/".$folder."/movie", DIR_FILES );
                sort( $eventFiles );

                foreach( $eventFiles as $city_xml_file )
                {
                    $xml = simplexml_load_file( $baseDir."/".$folder."/movie/".$city_xml_file );
                    $totalPois = count( $xml->xpath( '/vendor-movies/movie' ) );

                    $cutCityName = explode( ".", $city_xml_file );
                    $date = strtotime( str_replace( "export_", "", $folder ) );

                    $uiCategories = array();

                    if( !array_key_exists( $cutCityName[ 0 ], $this->existingMovieCount ) )
                        $this->existingMovieCount[ $cutCityName[ 0 ] ] = 0;

                    $droppedCount = 0;

                    foreach( $xml->movie as $node )
                    {
                        $vendorMovieId = (int) substr( $node['id'], 25 );

                        if( in_array( $vendorMovieId, $this->storeVendorMovieIds ) ) continue;

                        if( !array_key_exists( 'Film', $uiCategories ) ) $uiCategories[ 'Film' ] = 0;
                        $this->storeVendorMovieIds[] = $vendorMovieId;
                        $uiCategories[ 'Film' ]++;
                        $this->existingMovieCount[ $cutCityName[ 0 ] ]++;
                    }

                    echo $this->report( date( "Y-m-d", $date ), ucfirst( $cutCityName[0] ), $totalPois, $uiCategories, $this->existingMovieCount[ $cutCityName[ 0 ] ] );
                }

           break;
        }
    }
  }

  private function reportHeader()
  {
      if( $this->csv != true)
      {
          $buffer = str_repeat( "-", 170 ) . PHP_EOL;
          $buffer .= str_pad( "DATE", 15, " " );
          $buffer .= str_pad( "CITY", 20, " " );

          foreach( $this->uiCatsSimpleArrayNamesOnly as $name )
          {
              if( strtoupper($name) == "EATING & DRINKING" ) $name = "E & D";
              $buffer .= str_pad(strtoupper($name), 14, " " );
          }

          $buffer .= str_pad( "PROVIDED", 10, " " );
          $buffer .= str_pad( "NEW", 15, " " );
          $buffer .= str_pad( "EXISTING", 12, " " );

          $buffer .= PHP_EOL . str_repeat( "-", 170 );

          echo $buffer . PHP_EOL;
      }
      else {
          $buffer = "DATE" . $this->delim;
          $buffer .= "CITY" . $this->delim;

          foreach( $this->uiCatsSimpleArrayNamesOnly as $name )
                $buffer .= $name . $this->delim;

          $buffer .= "PROVIDED" . $this->delim;
          $buffer .= "NEW" . $this->delim;
          $buffer .= "EXISTING" . $this->delim;

          echo substr( $buffer, 0, -1 ) . PHP_EOL;
      }
  }

  private function report( $date, $city, $total, $catTotals, $existingPoiCount )
  {
      if( $this->csv != true)
      {
          $buffer = str_pad( $date, 15, " " );
          $buffer .= str_pad( $city, 20, " " );

          foreach( $this->uiCatsSimpleArrayNamesOnly as $name )
            if( array_key_exists( $name, $catTotals ) )
                $buffer .= str_pad( $catTotals[ $name ], 14, " " );
            else $buffer .= str_pad( "0", 14, " " );

          $buffer .= str_pad( $total, 10, " " );
          $buffer .= str_pad( array_sum( $catTotals ), 15, " " );
          $buffer .= str_pad( $existingPoiCount - array_sum( $catTotals ), 12, " " );

          echo $buffer . PHP_EOL;
      }
      else {
          $buffer = $date . $this->delim;
          $buffer .= $city . $this->delim;

          foreach( $this->uiCatsSimpleArrayNamesOnly as $name )
            if( array_key_exists( $name, $catTotals ) )
                $buffer .= $catTotals[ $name ] . $this->delim;
            else $buffer .= "0" . $this->delim;

          $buffer .= $total . $this->delim;
          $buffer .= array_sum( $catTotals ) . $this->delim;
          $buffer .= $existingPoiCount - array_sum( $catTotals ) . $this->delim;

          echo substr( $buffer, 0, -1 ) . PHP_EOL;
      }
  }

  protected function readDir( $dir = ".", $which = DIR_ALL )
  {
    $filesArray = array();

    $path = realpath( $dir );
    if( $path === false || !is_dir( $path ) ) throw new Exception( "Folder Not Found '" . $dir . "'" );
    
    $d = dir( $path );

    while ( false !== ( $entry = @$d->read() ) )
    {
        if( $entry == '.' || $entry == '..' )
            continue;

        if( $which === DIR_FOLDERS )
        {
            if( is_dir( realpath( $d->path . "/" . $entry ) ) )
                $filesArray[] = $entry;
        }
                
        elseif( $which === DIR_FILES )
        {
            if( is_file( realpath( $d->path . "/" . $entry ) ) )
                $filesArray[] = $entry;
        }

        else $filesArray[] = $entry;
    }
        
    $d->close();

    return $filesArray;
  }
}