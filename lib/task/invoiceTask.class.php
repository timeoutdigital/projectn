<?php

class invoiceTask extends sfBaseTask
{
  private $storeVendorPoiIds = array();
  private $storeVendorEventIds = array();
  private $storeVendorMovieIds = array();
  private $csv = false;
  private $delim = ',';
  private $existingPoiCount = array();
  private $existingEventCount = array();
  private $existingMovieCount = array();
  private $useSTDERR = true;
  private $poiUiCategoryMap = array();
  private $eventUiCategoryMap = array();

  protected function configure()
  {

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
      new sfCommandOption('path', null, sfCommandOption::PARAMETER_REQUIRED, 'Location of Datstamped Export Files', '/n/invoice-test/exports'),
      new sfCommandOption('type', null, sfCommandOption::PARAMETER_REQUIRED, 'poi, event, movie', 'poi'),
      new sfCommandOption('csv', null, sfCommandOption::PARAMETER_REQUIRED, 'Produce as csv', 'false'),
      new sfCommandOption('city', null, sfCommandOption::PARAMETER_REQUIRED, 'Only for one city', ''),
      new sfCommandOption('dump_ids', null, sfCommandOption::PARAMETER_REQUIRED, 'Only dump the ids', 'false'),
      new sfCommandOption('STDERR', null, sfCommandOption::PARAMETER_REQUIRED, 'Show output in STDERR', 'true'),
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'invoice';
    $this->briefDescription = 'Produce invoice from exports';
    $this->detailedDescription = <<<EOF
The [invoice|INFO] task does things.
Call it with:

  [php symfony invoice|INFO]
EOF;
  }

  protected function setUp( $options = array() )
  {
    $this->csv       = (bool)( $options['csv'] === 'true' );
    $this->useSTDERR = (bool)( $options['STDERR'] === 'true' );

    $databaseManager = new sfDatabaseManager( $this->configuration );
    $connection = $databaseManager->getDatabase( $options['connection'] ? $options['connection'] : null )->getConnection();
    
    foreach( Doctrine::getTable('UiCategory')->findAll() as $map )
    {
        $this->uiCatsSimpleArrayNamesOnly[] = $map['name'];
        foreach( $map['VendorPoiCategory'] as $m )   $this->poiUiCategoryMap[ html_entity_decode( $m['name'] ) ] = $map['name'];
        foreach( $map['VendorEventCategory'] as $m ) $this->eventUiCategoryMap[ html_entity_decode( $m['name'] ) ] = $map['name'];
    }
    
    // Output Header
    if( $options['dump_ids'] == 'false' ) $this->reportHeader();
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->setUp( $options );

    foreach( DirectoryIteratorN::iterate( $options['path'], DirectoryIteratorN::DIR_FOLDERS ) as $folder )
    {
        switch( $options['type'] )
        {
            case 'poi':
                
                $poiFiles = DirectoryIteratorN::iterate( $options['path'].'/'.$folder.'/poi', DirectoryIteratorN::DIR_FILES, 'xml' );
                
                foreach( $poiFiles as $city_xml_file )
                {
                    $uiCategories = $noVendorCats = array();
                    
                    $cutCityName = explode( '.', $city_xml_file );
                    $date = strtotime( str_replace( 'export_', '', $folder ) );
                    $cityName = $cutCityName[ 0 ];

                    if( strlen( $options['city'] ) > 0 && strtolower( $cityName ) != $options['city'] ) continue;

                    $xml = simplexml_load_file( $options['path'].'/'.$folder.'/poi/'.$city_xml_file );
                    $totalPois = count( $xml->xpath( '/vendor-pois/entry' ) );
                    
                    if( !array_key_exists( $cityName, $this->existingPoiCount ) )
                        $this->existingPoiCount[ $cityName ] = 0;

                    foreach( $xml->entry as $node )
                    {
                        $vendorPoiId = (int) substr( $node['vpid'], 25 );
                        if( in_array( $vendorPoiId, $this->storeVendorPoiIds ) ) continue;

                        foreach( $node->version->content->{'vendor-category'} as $cat )
                        {
                            $catName = str_replace( PHP_EOL, ' ', html_entity_decode( stringTransform::mb_trim( (string) $cat ) ) );

                            if( in_array( $catName, array_keys( $this->poiUiCategoryMap ) ) )
                            {
                                if( !array_key_exists( $this->poiUiCategoryMap[ $catName ], $uiCategories ) )
                                    $uiCategories[ $this->poiUiCategoryMap[ $catName ] ] = 0;

                                $this->storeVendorPoiIds[] = $vendorPoiId;
                                $uiCategories[ $this->poiUiCategoryMap[ $catName ] ]++;
                                $this->existingPoiCount[ $cityName ]++;

                                if( $options['dump_ids'] == 'true' ) echo $vendorPoiId . PHP_EOL;
                                continue 2;
                            }
                        }

                        $noVendorCats[] = $vendorPoiId;
                    }

                    if( $options['dump_ids'] == 'false' )
                        echo $this->report( date( 'Y-m-d', $date ), ucfirst( $cityName ), $totalPois, $uiCategories, $this->existingPoiCount[ $cityName ], count( $noVendorCats ) );
                }

            break;

            case 'event':
                
                $eventFiles = DirectoryIteratorN::iterate( $options['path'].'/'.$folder.'/event', DirectoryIteratorN::DIR_FILES, 'xml' );

                foreach( $eventFiles as $city_xml_file )
                {
                    $cutCityName = explode( '.', $city_xml_file );
                    $date = strtotime( str_replace( 'export_', '', $folder ) );
                    $cityName = $cutCityName[ 0 ];

                    if( strlen( $options['city'] ) > 0 && strtolower( $cityName ) != $options['city'] ) continue;

                    $xml = simplexml_load_file( $options['path'].'/'.$folder.'/event/'.$city_xml_file );
                    $totalPois = count( $xml->xpath( '/vendor-events/event' ) );

                    $uiCategories = array();

                    if( !array_key_exists( $cityName, $this->existingEventCount ) )
                        $this->existingEventCount[ $cityName ] = 0;

                    foreach( $xml->event as $node )
                    {
                        $vendorEventId = (int) substr( $node['id'], 25 );
                        if( in_array( $vendorEventId, $this->storeVendorEventIds ) ) continue;

                        if( $options['dump_ids'] == 'true' ) echo $vendorEventId . PHP_EOL;

                        foreach( $node->version->{'vendor-category'} as $cat )
                        {
                            $catName = html_entity_decode( stringTransform::mb_trim( (string) $cat ) );

                            if( in_array( $catName, array_keys( $this->eventUiCategoryMap ) ) )
                            {
                                if( !array_key_exists( $this->eventUiCategoryMap[ $catName ], $uiCategories ) )
                                    $uiCategories[ $this->eventUiCategoryMap[ $catName ] ] = 0;

                                $this->storeVendorEventIds[] = $vendorEventId;
                                $uiCategories[ $this->eventUiCategoryMap[ $catName ] ]++;
                                $this->existingEventCount[ $cityName ]++;
                                continue 2;
                            }
                        }

                        $moo = array_keys( $this->eventUiCategoryMap );
                        sort( $moo );
                        foreach( $moo as $cow )
                        {
                            if( strpos( $cow, 'Хип-хоп' ) !== false )
                            {
                                echo 'FOUND: ' . $cow . PHP_EOL;
                            }
                        }

                        echo '*** CATEGORY "$catName" NOT FOUND ***' . PHP_EOL;
                        die;
                    }

                    if( $options['dump_ids'] == 'false' )
                        echo $this->report( date( 'Y-m-d', $date ), ucfirst( $cityName ), $totalPois, $uiCategories, $this->existingEventCount[ $cityName ] );
                }

           break;

            case 'movie':

                $eventFiles = DirectoryIteratorN::iterate( $options['path'].'/'.$folder.'/movie', DirectoryIteratorN::DIR_FILES, 'xml' );

                foreach( $eventFiles as $city_xml_file )
                {
                    $cutCityName = explode( '.', $city_xml_file );
                    $date = strtotime( str_replace( 'export_', '', $folder ) );
                    $cityName = $cutCityName[ 0 ];

                    if( strlen( $options['city'] ) > 0 && strtolower( $cutCityName ) != $options['city'] ) continue;
                    
                    $xml = simplexml_load_file( $options['path'].'/'.$folder.'/movie/'.$city_xml_file );
                    $totalPois = count( $xml->xpath( '/vendor-movies/movie' ) );

                    $uiCategories = array();

                    if( !array_key_exists( $cityName, $this->existingMovieCount ) )
                        $this->existingMovieCount[ $cityName ] = 0;

                    foreach( $xml->movie as $node )
                    {
                        $vendorMovieId = (int) substr( $node['id'], 25 );
                        if( in_array( $vendorMovieId, $this->storeVendorMovieIds ) ) continue;

                        if( $options['dump_ids'] == 'true' ) echo $vendorMovieId . PHP_EOL;

                        if( !array_key_exists( 'Film', $uiCategories ) ) $uiCategories[ 'Film' ] = 0;
                        $this->storeVendorMovieIds[] = $vendorMovieId;
                        $uiCategories[ 'Film' ]++;
                        $this->existingMovieCount[ $cityName ]++;
                    }

                    if( $options['dump_ids'] == 'false' )
                        echo $this->report( date( 'Y-m-d', $date ), ucfirst( $cityName ), $totalPois, $uiCategories, $this->existingMovieCount[ $cityName ] );
                }

           break;
        }
    }

//    echo 'No Vendor Cats: ' . PHP_EOL;
//    echo( implode( ',', $noVendorCats ) );
  }

  private function reportHeader()
  {
      if( $this->csv != true) echo PHP_EOL;
      
      $buffer = str_repeat( '-', 170 ) . PHP_EOL;
      $buffer .= str_pad( 'DATE', 15, ' ' );
      $buffer .= str_pad( 'CITY', 20, ' ' );

      foreach( $this->uiCatsSimpleArrayNamesOnly as $name )
      {
          if( strtoupper($name) == 'EATING & DRINKING' ) $name = 'E & D';
          $buffer .= str_pad(strtoupper($name), 14, ' ' );
      }

      $buffer .= str_pad( 'PROVIDED', 10, ' ' );
      $buffer .= str_pad( 'NEW', 15, ' ' );
      $buffer .= str_pad( 'EXISTING', 12, ' ' );
      $buffer .= str_pad( 'NOCAT', 12, ' ' );

      $buffer .= PHP_EOL . str_repeat( '-', 170 );

      if( $this->csv == true) {
          $buffer2 = 'DATE' . $this->delim;
          $buffer2 .= 'CITY' . $this->delim;

          foreach( $this->uiCatsSimpleArrayNamesOnly as $name )
                $buffer2 .= $name . $this->delim;

          $buffer2 .= 'PROVIDED' . $this->delim;
          $buffer2 .= 'NEW' . $this->delim;
          $buffer2 .= 'EXISTING' . $this->delim;
          $buffer2 .= 'NOCAT' . $this->delim;

          echo substr( $buffer2, 0, -1 ) . PHP_EOL;
          if( $this->useSTDERR ) fwrite( STDERR, $buffer . PHP_EOL );
      }

      else echo $buffer . PHP_EOL;
  }

  private function report( $date, $city, $total, $catTotals, $existingPoiCount, $noCat = 0 )
  {
      $buffer = str_pad( $date, 15, ' ' );
      $buffer .= str_pad( $city, 20, ' ' );

      foreach( $this->uiCatsSimpleArrayNamesOnly as $name )
        if( array_key_exists( $name, $catTotals ) )
            $buffer .= str_pad( $catTotals[ $name ], 14, ' ' );
        else $buffer .= str_pad( '0', 14, ' ' );

      $buffer .= str_pad( $total, 10, ' ' );
      $buffer .= str_pad( array_sum( $catTotals ), 15, ' ' );
      $buffer .= str_pad( $existingPoiCount - array_sum( $catTotals ), 12, ' ' );
      $buffer .= str_pad( $noCat, 12, ' ' );

      if( $this->csv == true) {

          $buffer2 = $date . $this->delim;
          $buffer2 .= $city . $this->delim;

          foreach( $this->uiCatsSimpleArrayNamesOnly as $name )
            if( array_key_exists( $name, $catTotals ) )
                $buffer2 .= $catTotals[ $name ] . $this->delim;
            else $buffer2 .= '0' . $this->delim;

          $buffer2 .= $total . $this->delim;
          $buffer2 .= array_sum( $catTotals ) . $this->delim;
          $buffer2 .= $existingPoiCount - array_sum( $catTotals ) . $this->delim;
          $buffer2 .= $noCat . $this->delim;

          echo substr( $buffer2, 0, -1 ) . PHP_EOL;
          if( $this->useSTDERR ) fwrite( STDERR, $buffer . PHP_EOL );
      }

      else echo $buffer . PHP_EOL;
  }
}