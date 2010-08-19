<?php

class invoiceTask extends sfBaseTask
{
  // Arrays that hold the category mappings.
  private $poiUiCategoryMap = array();
  private $eventUiCategoryMap = array();

  // Arrays that hold ids of entries that have
  // already been included in a bill.
  private $storeVendorPoiIds = array();
  private $storeVendorEventIds = array();
  private $storeVendorMovieIds = array();

  // Return CSV format.
  private $csv = false;
  private $delim = ',';

  // When in CSV mode, output a pretty table to STDERR.
  private $useSTDERR = true;

  // Counters, these keep an individual count per city.
  private $existingPoiCount = array();
  private $existingEventCount = array();
  private $existingMovieCount = array();

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
    // Set Options.
    $this->csv       = (bool)( $options['csv'] === 'true' );
    $this->useSTDERR = (bool)( $options['STDERR'] === 'true' );

    // Configure Database.
    $databaseManager = new sfDatabaseManager( $this->configuration );
    $connection = $databaseManager->getDatabase( $options['connection'] ? $options['connection'] : null )->getConnection();

    // Get mapping data from external database (usually prod).
    foreach( Doctrine::getTable('UiCategory')->findAll() as $map )
    {
        $this->uiCatsSimpleArrayNamesOnly[] = $map['name'];
        foreach( $map['VendorPoiCategory'] as $m )   $this->poiUiCategoryMap[ html_entity_decode( $m['name'] ) ] = $map['name'];
        foreach( $map['VendorEventCategory'] as $m ) $this->eventUiCategoryMap[ html_entity_decode( $m['name'] ) ] = $map['name'];
    }

    if( empty( $this->uiCatsSimpleArrayNamesOnly ) ) throw new Exception( 'Could not get category mappings from database, please specify a live data source.' );
    
    // Output Header.
    if( $options['dump_ids'] == 'false' ) $this->reportHeader();
  }

  private function getDateFromFolderName( $folderName )
  {
      return strtotime( str_replace( 'export_', '', $folderName ) );
  }

  private function getCityNameFromFileName( $fileName )
  {
      $cutCityName = explode( '.', $fileName );
      return $cutCityName[ 0 ];
  }

  private function tidyCategoryName( $catName )
  {
      return str_replace( PHP_EOL, ' ', html_entity_decode( stringTransform::mb_trim( (string) $catName ) ) );
  }

  /**
   * Pick UI Category with highest business value.
   * @return string of highest UI Category or false on failure.
   */

  protected function pickHighestValueCategory( array $cats )
  {
    if( empty( $cats ) ) return false;

    $priority = array( 'Eating & Drinking', 'Film', 'Art', 'Around Town', 'Nightlife', 'Music', 'Stage' );
    $highestCategory = 99999;

    foreach( $cats as $cat )
    {
        $priorityValue = array_search( $cat, $priority );
        if( is_numeric( $priorityValue ) && $priorityValue < $highestCategory )
            $highestCategory = $priorityValue;

        if( $highestCategory === 0 ) break;
    }

    return ( array_key_exists( $highestCategory, $priority ) ) ? $priority[ $priorityValue ] : false;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->setUp( $options );

    // Cycle through directories.
    foreach( DirectoryIteratorN::iterate( $options['path'], DirectoryIteratorN::DIR_FOLDERS ) as $folder )
    {
        switch( $options['type'] )
        {
            case 'poi':

                // Get a list of files in directory.
                $poiFiles = DirectoryIteratorN::iterate( $options['path'].'/'.$folder.'/poi', DirectoryIteratorN::DIR_FILES, 'xml' );

                // Cycle through poi files.
                foreach( $poiFiles as $city_xml_file )
                {                   
                    $date = $this->getDateFromFolderName( $folder );
                    $city = $this->getCityNameFromFileName( $city_xml_file );

                    // Skip if we specified only one city and this isn't it.
                    if( strlen( $options['city'] ) > 0 && strtolower( $city ) != $options['city'] ) continue;

                    // Array to hold counts for each UI category
                    $uiCategories = array();

                    // Array to hold count of entries with invalid categories.
                    $noVendorCats = array();

                    // Load XML file.
                    $xml = simplexml_load_file( $options['path'].'/'.$folder.'/poi/'.$city_xml_file );
                    $totalPois = count( $xml->xpath( '/vendor-pois/entry' ) );

                    // Initialise existingPoiCount for this city (only happens once per city).
                    if( !array_key_exists( $city, $this->existingPoiCount ) ) $this->existingPoiCount[ $city ] = 0;

                    // Cycle through entries.
                    foreach( $xml->entry as $node )
                    {
                        // Extract vpid and continue if we've previously billed for it.
                        $vendorPoiId = (int) substr( $node['vpid'], 25 );
                        if( in_array( $vendorPoiId, $this->storeVendorPoiIds ) ) continue;

                        // Store a list of matching UI Categories
                        $thisRecordUiCats = array();

                        // Cycle through categories.
                        foreach( $node->version->content->{'vendor-category'} as $cat )
                        {
                            // Get category name
                            $catName = $this->tidyCategoryName( (string) $cat );

                            // Check that we have the category in the mapping array.
                            if( in_array( $catName, array_keys( $this->poiUiCategoryMap ) ) )
                            {
                                // Initialise uiCategories for this UI category (only happens once per UI category).
                                // Eg. This sets uiCategories['Film'] to 0, where the key 'Film' did not previously exist.
                                if( !array_key_exists( $this->poiUiCategoryMap[ $catName ], $uiCategories ) )
                                    $uiCategories[ $this->poiUiCategoryMap[ $catName ] ] = 0;

                                // List of matching UI Categories.
                                $thisRecordUiCats[] = $this->poiUiCategoryMap[ $catName ];
                            }
                        }

                        // If at least UI Category was matched.
                        if( count( $thisRecordUiCats ) > 0 )
                        {
                            // Find category with highest business value.
                            $highestCat = $this->pickHighestValueCategory( array_unique( $thisRecordUiCats ) );

                            // Increment the UI category count. Eg. poiUiCategoryMap['Film']++
                            $uiCategories[ $highestCat ]++;

                            // Mark this vpid as billed for.
                            $this->storeVendorPoiIds[] = $vendorPoiId;

                            // Increment the count of total billed for per city.
                            $this->existingPoiCount[ $city ]++;

                            // Dump id to screen if option enabled.
                            if( $options['dump_ids'] == 'true' ) echo $vendorPoiId . PHP_EOL;
                        }

                        // We couldn't find a single matching UI category for this entry.
                        else $noVendorCats[] = $vendorPoiId;
                    }

                    // Write new report line.
                    if( $options['dump_ids'] == 'false' )
                        echo $this->report( date( 'Y-m-d', $date ), ucfirst( $city ), $totalPois, $uiCategories, $this->existingPoiCount[ $city ], count( $noVendorCats ) );
                }

            break;

            case 'event':

                // Get a list of files in directory.
                $eventFiles = DirectoryIteratorN::iterate( $options['path'].'/'.$folder.'/event', DirectoryIteratorN::DIR_FILES, 'xml' );

                // Cycle through event files.
                foreach( $eventFiles as $city_xml_file )
                {
                    $date = $this->getDateFromFolderName( $folder );
                    $city = $this->getCityNameFromFileName( $city_xml_file );

                    // Skip if we specified only one city and this isn't it.
                    if( strlen( $options['city'] ) > 0 && strtolower( $city ) != $options['city'] ) continue;

                    // Load XML file.
                    $xml = simplexml_load_file( $options['path'].'/'.$folder.'/event/'.$city_xml_file );
                    $totalPois = count( $xml->xpath( '/vendor-events/event' ) );

                    // Array to hold counts for each UI category
                    $uiCategories = array();

                    // Array to hold count of entries with invalid categories.
                    $noVendorCats = array();

                    // Initialise existingEventCount for this city (only happens once per city).
                    if( !array_key_exists( $city, $this->existingEventCount ) ) $this->existingEventCount[ $city ] = 0;

                    // Cycle through entries.
                    foreach( $xml->event as $node )
                    {
                        // Extract vpid and continue if we've previously billed for it.
                        $vendorEventId = (int) substr( $node['id'], 25 );
                        if( in_array( $vendorEventId, $this->storeVendorEventIds ) ) continue;

                        // Store a list of matching UI Categories
                        $thisRecordUiCats = array();

                        // Cycle through categories.
                        foreach( $node->version->{'vendor-category'} as $cat )
                        {
                            // Get category name
                            $catName = $this->tidyCategoryName( (string) $cat );

                            // Check that we have the category in the mapping array.
                            if( in_array( $catName, array_keys( $this->eventUiCategoryMap ) ) )
                            {
                                // Initialise uiCategories for this UI category (only happens once per UI category).
                                // Eg. This sets uiCategories['Film'] to 0, where the key 'Film' did not previously exist.
                                if( !array_key_exists( $this->eventUiCategoryMap[ $catName ], $uiCategories ) )
                                    $uiCategories[ $this->eventUiCategoryMap[ $catName ] ] = 0;

                                // List of matching UI Categories.
                                $thisRecordUiCats[] = $this->eventUiCategoryMap[ $catName ];
                            }
                        }

                        // If at least UI Category was matched.
                        if( count( $thisRecordUiCats ) > 0 )
                        {
                            // Find category with highest business value.
                            $highestCat = $this->pickHighestValueCategory( array_unique( $thisRecordUiCats ) );

                            // Increment the UI category count. Eg. eventUiCategoryMap['Film']++
                            $uiCategories[ $highestCat ]++;

                            // Mark this vpid as billed for.
                            $this->storeVendorEventIds[] = $vendorEventId;

                            // Increment the count of total billed for per city.
                            $this->existingEventCount[ $city ]++;

                            // Dump id to screen if option enabled.
                            if( $options['dump_ids'] == 'true' ) echo $vendorEventId . PHP_EOL;
                        }

                        // We couldn't find a single matching UI category for this entry.
                        else $noVendorCats[] = $vendorEventId;
                    }

                    // Write new report line.
                    if( $options['dump_ids'] == 'false' )
                        echo $this->report( date( 'Y-m-d', $date ), ucfirst( $city ), $totalPois, $uiCategories, $this->existingEventCount[ $city ], count( $noVendorCats ) );
                }

           break;

            case 'movie':

                // Get a list of files in directory.
                $movieFiles = DirectoryIteratorN::iterate( $options['path'].'/'.$folder.'/movie', DirectoryIteratorN::DIR_FILES, 'xml' );

                // Cycle through movie files.
                foreach( $movieFiles as $city_xml_file )
                {
                    $date = $this->getDateFromFolderName( $folder );
                    $city = $this->getCityNameFromFileName( $city_xml_file );

                    // Skip if we specified only one city and this isn't it.
                    if( strlen( $options['city'] ) > 0 && strtolower( $city ) != $options['city'] ) continue;

                    // Load XML file.
                    $xml = simplexml_load_file( $options['path'].'/'.$folder.'/movie/'.$city_xml_file );
                    $totalPois = count( $xml->xpath( '/vendor-movies/movie' ) );

                    // Array to hold counts for each UI category
                    $uiCategories = array();

                    // Initialise existingMovieCount for this city (only happens once per city).
                    if( !array_key_exists( $city, $this->existingMovieCount ) ) $this->existingMovieCount[ $city ] = 0;

                    // Cycle through entries.
                    foreach( $xml->movie as $node )
                    {
                        // Extract vpid and continue if we've previously billed for it.
                        $vendorMovieId = (int) substr( $node['id'], 25 );
                        if( in_array( $vendorMovieId, $this->storeVendorMovieIds ) ) continue;

                        // Initialise uiCategories['Film'] to 0, where the key 'Film' did not previously exist.
                        if( !array_key_exists( 'Film', $uiCategories ) ) $uiCategories[ 'Film' ] = 0;

                        // Increment the UI category count. Eg. eventUiCategoryMap['Film']++
                        $uiCategories[ 'Film' ]++;

                        // Mark this vpid as billed for.
                        $this->storeVendorMovieIds[] = $vendorMovieId;

                        // Increment the count of total billed for per city.
                        $this->existingMovieCount[ $city ]++;

                        // Dump id to screen if option enabled.
                        if( $options['dump_ids'] == 'true' ) echo $vendorMovieId . PHP_EOL;
                    }

                    // Write new report line.
                    if( $options['dump_ids'] == 'false' )
                        echo $this->report( date( 'Y-m-d', $date ), ucfirst( $city ), $totalPois, $uiCategories, $this->existingMovieCount[ $city ] );
                }

           break;

           default : throw new Exception( 'Invalid "type" specified.' );
        }
    }
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