<?php

class importTask extends sfBaseTask
{

  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('city', null, sfCommandOption::PARAMETER_REQUIRED, 'The city to import'),
      new sfCommandOption('type', null, sfCommandOption::PARAMETER_REQUIRED, 'The type to import', 'poi-event'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('verbose', null, sfCommandOption::PARAMETER_OPTIONAL, 'Switch on/off printing of log info'),
      new sfCommandOption('db-log', null, sfCommandOption::PARAMETER_OPTIONAL, 'Switch on/off saving log info to database', 'true'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'backend'),
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'import';
    $this->briefDescription = 'Import data files from vendors';
    $this->detailedDescription = '';


  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->options = $options;

    $this->writeLogLine( 'start import for ' . $options['city'] . ' (type: ' . $options['type'] . ', environment: ' . $options['env'] . ')' );

    //Connect to the database.
    $databaseManager = new sfDatabaseManager($this->configuration);
    Doctrine_Manager::getInstance()->setAttribute( Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_ALL );
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    $importer = new Importer();

    if( $options['verbose'] == 'true' )
    {
      $importer->addLogger( new echoingLogger() );
    }

    //Select the task
    switch( $options['city'] )
    {
      case 'ny':

        //Set vendor and logger
        $vendorObj = $this->getVendorByCityAndLanguage('ny', 'en-US');
        $importer->addLogger( new logImport($vendorObj) );
        


        switch( $options['type'] )
        {
          case 'poi-event-kids':
            try
            {
              //Setup NY FTP @todo refactor FTPClient to not connect in constructor
              $ftpClientObj = new FTPClient( 'ftp.timeoutny.com', 'london', 'timeout', $vendorObj[ 'city' ] );
              $ftpClientObj->setSourcePath( '/NOKIA/' );
              $fileNameString = $ftpClient->fetchLatestFileByPattern( 'tony_kids_leo.xml' );

              $processXmlObj = new processNyXml( $fileNameString );
              $processXmlObj->setEvents('/body/event')->setVenues('/body/address');
              $nyImportMoviesObj = new importNy($processXmlObj,$vendorObj);
              $nyImportMoviesObj->insertEventCategoriesAndEventsAndVenues();
            }
            catch ( Exception $e )
            {
              echo 'Exception caught in chicago' . $options['city'] . ' ' . $options['type'] . ' import: ' . $e->getMessage();
            }
            break;

          case 'poi-event':
                //Setup NY FTP @todo refactor FTPClient to not connect in constructor
                $ftpClientObj = new FTPClient( 'ftp.timeoutny.com', 'london', 'timeout', $vendorObj[ 'city' ] );
                $ftpClientObj->setSourcePath( '/NOKIA/' );
                $this->importNyEvents($vendorObj, $ftpClientObj);
            break;

          case 'movie':
                $importer->addDataMapper( new londonDatabaseFilmsDataMapper( $vendorObj ) );
            break;

          case 'eating-drinking':
            try
            {
              //Setup NY FTP @todo refactor FTPClient to not connect in constructor
              $ftpClientObj = new FTPClient( 'ftp.timeoutny.com', 'london', 'timeout', $vendorObj[ 'city' ] );
              $ftpClientObj->setSourcePath( '/NOKIA/' );
              $this->importNyEd($vendorObj, $ftpClientObj);
            }
            catch ( Exception $e )
            {
              echo 'Exception caught in chicago' . $options['city'] . ' ' . $options['type'] . ' import: ' . $e->getMessage();
            }
            break;

          case 'eating-drinking-kids':
            try
            {
              //Setup NY FTP @todo refactor FTPClient to not connect in constructor
              $ftpClientObj = new FTPClient( 'ftp.timeoutny.com', 'london', 'timeout', $vendorObj[ 'city' ] );
              $ftpClientObj->setSourcePath( '/NOKIA/' );
              $fileNameString = $ftpClient->fetchFile( 'tonykids_ed.xml' );

              /*$vendor = $this->getVendorByCityAndLanguage('ny', 'en-US');
              $csv = new processCsv( 'import/tony_ed_made_up_headers.csv' );
              $nyEDImport =  new importNyED( $csv, $vendor );
              $nyEDImport->insertPois();*/
            }
            catch ( Exception $e )
            {
              echo 'Exception caught in chicago' . $options['city'] . ' ' . $options['type'] . ' import: ' . $e->getMessage();
            }
            break;

          case 'bars-clubs':
            try
            {
              //Setup NY FTP @todo refactor FTPClient to not connect in constructor
              $ftpClientObj = new FTPClient( 'ftp.timeoutny.com', 'london', 'timeout', $vendorObj[ 'city' ] );
              $ftpClientObj->setSourcePath( '/NOKIA/' );
              $this->importNyBc($vendorObj, $ftpClientObj);
            }
            catch ( Exception $e )
            {
              echo 'Exception caught in chicago' . $options['city'] . ' ' . $options['type'] . ' import: ' . $e->getMessage();
            }
            break;


          case 'all':
              //Import all events
              //Setup NY FTP @todo refactor FTPClient to not connect in constructor
              $ftpClientObj = new FTPClient( 'ftp.timeoutny.com', 'london', 'timeout', $vendorObj[ 'city' ] );
              $ftpClientObj->setSourcePath( '/NOKIA/' );
              $this->importNyEvents($vendorObj, $ftpClientObj);
              $this->importNyMovies($vendorObj, $ftpClientObj);

          break;

          default: echo "Types available: \n bars-clubs \n";

        }
        break; // end ny

      case 'chicago':

        $vendorObj = $this->getVendorByCityAndLanguage('chicago', 'en-US');
        $ftpClientObj = new FTPClient( 'ftp.timeoutchicago.com', 'timeout', 'y6fv2LS8', $vendorObj[ 'city' ] );
       

        switch( $options['type'] )
        {
          case 'poi-event':
              $this->importChicagoEvents($vendorObj, $ftpClientObj);
            break;

          case 'movie':
               $importer->addDataMapper( new londonDatabaseFilmsDataMapper( $vendorObj ) );

          break;

          case 'eating-drinking':
            try
            {
              $importObj = $this->importChicagoEd($vendorObj, $ftpClientObj);
            }
            catch ( Exception $e )
            {
              echo 'Exception caught in chicago' . $options['city'] . ' ' . $options['type'] . ' import: ' . $e->getMessage();
            }
           break;
          case 'bars-clubs':
            try
            {
              $importObj = $this->importChicagoBc($vendorObj, $ftpClientObj);
            }
            catch ( Exception $e )
            {
              echo 'Exception caught in chicago' . $options['city'] . ' ' . $options['type'] . ' import: ' . $e->getMessage();
            }
           break;


           case 'all':
             
               $this->importChicagoEvents($vendorObj, $ftpClientObj);
               $this->importChicagoMovies($vendorObj, $ftpClientObj);
               $this->importChicagoBc($vendorObj, $ftpClientObj, $loggerObj);
               $this->importChicagoEd($vendorObj, $ftpClientObj, $loggerObj);
               
           break;


        }
        break; //end chicago

      case 'singapore':
        $vendorObj = $this->getVendorByCityAndLanguage('singapore', 'en-US');

        //must be set for price range function
        //@todo get get this info out of vendor?!
        setlocale(LC_MONETARY, 'en_US.UTF-8');

        switch( $options['type'] )
        {
          case 'poi-event':
            //http://www.timeoutsingapore.com/xmlapi/events/?section=index&full&key=ffab6a24c60f562ecf705130a36c1d1e
            //http://www.timeoutsingapore.com/xmlapi/venues/?section=index&full&key=ffab6a24c60f562ecf705130a36c1d1e

            $logger = new logImport($vendorObj, 'poi' );

            echo "Starting Singapore Pois import \n";
            $curlImporterObj = new curlImporter();
            $parametersArray = array( 'section' => 'index', 'full' => '', 'key' => 'ffab6a24c60f562ecf705130a36c1d1e' );

            echo "Getting Singapore poi-event feed\n";
            $curlImporterObj->pullXml ('http://www.timeoutsingapore.com/xmlapi/venues/', '', $parametersArray, 'GET', true );
            $xmlObj = $curlImporterObj->getXml();

            echo "Importing Singapores Pois \n\n";
            $this->object = new singaporeImport( $vendorObj, $curlImporterObj, $logger );
            $this->object->insertPois( $xmlObj );

            $logger->save();

            $logger = new logImport($vendorObj, 'event' );

            echo "Starting Singapore Events import \n";
            $curlImporterObj = new curlImporter();
            $parametersArray = array( 'section' => 'index', 'full' => '', 'key' => 'ffab6a24c60f562ecf705130a36c1d1e' );

             echo "Getting reading Singapore poi-event feed";
            $curlImporterObj->pullXml ('http://www.timeoutsingapore.com/xmlapi/events/', '', $parametersArray, 'GET', true );
            $xmlObj = $curlImporterObj->getXml();

            $this->object = new singaporeImport( $vendorObj, $curlImporterObj, $logger, 'http://www.timeoutsingapore.com/xmlapi/xml_detail/?venue={venueId}&key=ffab6a24c60f562ecf705130a36c1d1e' );
            $this->object->insertEvents( $xmlObj );

            $logger->save();
            
            break;

          case 'movie':
            //http://www.timeoutsingapore.com/xmlapi/movies/?section=index&full&key=ffab6a24c60f562ecf705130a36c1d1e
            $logger = new logImport($vendorObj, 'movie' );

            echo "Connecting to Singapore Movie Feed \n";
            $curlImporterObj = new curlImporter();
            $parametersArray = array( 'section' => 'index', 'full' => '', 'key' => 'ffab6a24c60f562ecf705130a36c1d1e' );
            $curlImporterObj->pullXml ('http://www.timeoutsingapore.com/xmlapi/movies/', '', $parametersArray, 'GET', true );

            echo "Importing Singapore Feed \n";
            $xmlObj = $curlImporterObj->getXml();

            echo "Importing Movie Data";
            $this->object = new singaporeImport( $vendorObj, $curlImporterObj, $logger );
            $this->object->insertMovies( $xmlObj );

            $logger->save();
            echo "Impored Singapores Movies \n";
            
          break;

          case 'eating-drinking':
          break;
        }

        break; //end singapore

      case 'lisbon':

        $vendorObj    = $this->getVendorByCityAndLanguage('lisbon', 'pt');
        $feedObj      = new curlImporter();
        $url          = 'http://www.timeout.pt/';
        $today        = date( 'Y-m-d' );
        $oneWeekLater = date_add( new DateTime(), new DateInterval( 'P4D' ) )->format( 'Y-m-d' );
        $parameters   = array( 'from' => $today, 'to' => $oneWeekLater );//lisbon caps the request at 9 days
        $method       = 'POST';
        $loggerObj    = new logImport( $vendorObj );
        
        switch( $options['type'] )
        {
          case 'poi':
            $request = 'xmlvenues.asp';
            $feedObj->pullXml ( $url, $request, $parameters, $method );
            $loggerObj->setType( 'poi' );

            $importer->addLogger( $loggerObj );
            $importer->addDataMapper( new LisbonFeedVenuesMapper( $feedObj->getXml() ) );
            break;

          case 'event':
            $request = 'xmllist.asp';
            $feedObj->pullXml ( $url, $request, $parameters, $method );
            $loggerObj->setType( 'event' );

            $importer->addLogger( $loggerObj );
            $importer->addDataMapper( new LisbonFeedListingsMapper( $feedObj->getXml() ) );
          break;

          case 'movie':
            $request = 'xmlfilms.asp';
            $feedObj->pullXml ( $url, $request, $parameters, $method );
            $loggerObj->setType( 'movie' );
            
            $importer->addLogger( $loggerObj );
            $importer->addDataMapper( new LisbonFeedMoviesMapper( $feedObj->getXml() ) );
          break;
        }
        break; //end lisbon   
    
    
      case 'russia':

        switch( $options['type'] )
        {
          case 'poi':
//            $vendorObj    = $this->getVendorByCityAndLanguage('moscow', 'ru');
//            $loggerObj    = new logImport( $vendorObj );
//            $loggerObj->setType( 'poi' );
//            $importer->addLogger( $loggerObj );

            $feedObj = new Curl( 'http://www.timeout.ru/london/places_msk.xml' );
            $feedObj->exec();
            $xml = simplexml_load_string( $feedObj->getResponse() );
            $importer->addDataMapper( new RussiaFeedPlacesMapper( $xml, null, 'moscow' ) );

            $feedObj = new Curl( 'http://www.timeout.ru/london/places_spb.xml' );
            $feedObj->exec();
            $xml = simplexml_load_string( $feedObj->getResponse() );
            $importer->addDataMapper( new RussiaFeedPlacesMapper( $xml, null, 'saint petersburg' ) );

            $feedObj = new Curl( 'http://www.timeout.ru/london/places_omsk.xml' );
            $feedObj->exec();
            $xml = simplexml_load_string( $feedObj->getResponse() );
            $importer->addDataMapper( new RussiaFeedPlacesMapper( $xml, null, 'omsk' ) );

            $feedObj = new Curl( 'http://www.timeout.ru/london/places_almaty.xml' );
            $feedObj->exec();
            $xml = simplexml_load_string( $feedObj->getResponse() );
            $importer->addDataMapper( new RussiaFeedPlacesMapper( $xml, null, 'almaty' ) );

            $feedObj = new Curl( 'http://www.timeout.ru/london/places_novosibirsk.xmll' );
            $feedObj->exec();
            $xml = simplexml_load_string( $feedObj->getResponse() );
            $importer->addDataMapper( new RussiaFeedPlacesMapper( $xml, null, 'novosibirsk' ) );

            $feedObj = new Curl( 'http://www.timeout.ru/london/places_krasnoyarsk.xml' );
            $feedObj->exec();
            $xml = simplexml_load_string( $feedObj->getResponse() );
            $importer->addDataMapper( new RussiaFeedPlacesMapper( $xml, null, 'krasnoyarsk' ) );

            $feedObj = new Curl( 'http://www.timeout.ru/london/places_tumen.xml' );
            $feedObj->exec();
            $xml = simplexml_load_string( $feedObj->getResponse() );
            $importer->addDataMapper( new RussiaFeedPlacesMapper( $xml, null, 'tyumen' ) );

            break;

          case 'event':
//            $loggerObj->setType( 'event' );
//            $importer->addLogger( $loggerObj );
          
            $feedObj = new Curl( 'http://www.timeout.ru/london/events.xml' );
            $feedObj->exec();
            $xml = simplexml_load_string( $feedObj->getResponse() );
          
            $importer->addDataMapper( new RussiaFeedEventsMapper( $xml, null, 'moscow' ) );
            break;

          case 'movie':
//            $loggerObj->setType( 'movie' );
//            $importer->addLogger( $loggerObj );
          
            $feedObj = new Curl( 'http://www.timeout.ru/london/movies.xml' );
            $feedObj->exec();
            $xml = simplexml_load_string( $feedObj->getResponse() );

            $importer->addDataMapper( new RussiaFeedMoviesMapper( $xml, null, 'moscow' ) );
            break;
        }
        break; //end russia


      case 'london':
        $vendor = $this->getVendorByCityAndLanguage( 'london', 'en-GB' );
        $loggerObj = new logImport( $vendor );
        switch( $options['type'] )
        {
          case 'poi':
            $connection = $databaseManager->getDatabase( 'searchlight_london' )->getConnection();
            $loggerObj->setType( 'poi' );
            $importer->addLogger( $loggerObj );

            $importer->addDataMapper( new LondonDatabaseEventsAndVenuesMapper( 'poi' ) );
            $importer->addDataMapper( new LondonAPIBarsAndPubsMapper() );
            $importer->addDataMapper( new LondonAPIRestaurantsMapper() );
            $importer->addDataMapper( new LondonAPICinemasMapper() );
            break;

          case 'event':
            $connection = $databaseManager->getDatabase( 'searchlight_london' )->getConnection();
            $loggerObj->setType( 'event' );
            $importer->addLogger( $loggerObj );

            $importer->addDataMapper( new LondonDatabaseEventsAndVenuesMapper( 'event' ) );
            break;

          case 'event-occurrence':
            $connection = $databaseManager->getDatabase( 'searchlight_london' )->getConnection();

            $importer->addDataMapper( new LondonDatabaseEventsAndVenuesMapper( 'event-occurrence' ) );
            break;

          case 'movie':
            $loggerObj->setType( 'movie' );
            $importer->addLogger( $loggerObj );
            $importer->addDataMapper( new londonDatabaseFilmsDataMapper( $vendor ) );
          break;
        }
        break; //end london




      //sydney is dirty I know... needs fixing bad, like the rest of this task
      case 'sydney':
        $vendor = $this->getVendorByCityAndLanguage( 'sydney', 'en-AU' );
        $loggerObj = new logImport( $vendor );
        $importer->addLogger( new logImport( $vendor ) );

        $ftpClient = new FTPClient( 'ftp.timeoutsydney.com.au', 'timeoutlondon', 'T1m3outl0nd0n', $vendor[ 'city' ] );
        $ftpClient->setSourcePath( '/timeoutlondon/' );

        //map the files to our terms
        $ftpFiles = array();
        foreach( $ftpClient->fetchRawDirListing() as $fileListing )
        {
          //get rid of the date / other info from ls command
          $filename = preg_replace( '/^.*?([-a-z0-9_]*.xml)$/', '$1', $fileListing );

          if( strpos( $filename, 'venue' ) !== false )
            $ftpFiles[ 'poi' ] = $filename;

          else if( strpos( $filename, 'event' ) !== false )
            $ftpFiles[ 'event' ] = $filename;

          else if( strpos( $filename, 'film' ) !== false )
            $ftpFiles[ 'movie' ] = $filename;
        }

        switch( $options['type'] )
        {
          case 'poi':
            $loggerObj->setType( 'poi' );
            $this->output( 'fetching sydney poi xml...' );
            $downloadedFile = $ftpClient->fetchFile( $ftpFiles[ 'poi' ], 'venues.xml' );
            $this->output( 'xml received' );
            $xml = simplexml_load_file( $downloadedFile );
            $importer->addDataMapper( new sydneyFtpVenuesMapper( $vendor, $xml ) );
            break;

          case 'event':
            $loggerObj->setType( 'event' );
            $this->output( 'fetching sydney event xml...' );
            $downloadedFile = $ftpClient->fetchFile( $ftpFiles[ 'event' ], 'event.xml' );
            $this->output( 'xml received' );
            $xml = simplexml_load_file( $downloadedFile );
            $importer->addDataMapper( new sydneyFtpEventsMapper( $vendor, $xml ) );
            break;

          case 'movie':
            $loggerObj->setType( 'movie' );
            $this->output( 'fetching sydney movie xml...' );
            $downloadedFile = $ftpClient->fetchFile( $ftpFiles[ 'movie' ], 'movie.xml' );
            $this->output( 'xml received' );
            $xml = simplexml_load_file( $downloadedFile );
            $importer->addDataMapper( new sydneyFtpMoviesMapper( $vendor, $xml ) );
            break;
        }
        break;


    case 'kuala lumpur':
        $vendor         = $this->getVendorByCityAndLanguage( 'kuala lumpur', 'en-MY' );
        $loggerObj      = new logImport( $vendor );
        $importer->addLogger( $loggerObj );

        if( $options['type'] == "event" || $options['type'] == "movie" )
        {
            $this->output( 'fetching KL event/movie xml...' );
            $feedObj = new Curl( 'http://www.timeoutkl.com/xml/events.xml' );
            $feedObj->exec();
            $this->output( 'xml received' );
        }
        elseif( $options['type'] == "poi" )
        {
            $this->output( 'fetching KL poi xml...' );
            $feedObj = new Curl( 'http://www.timeoutkl.com/xml/venues.xml' );
            $feedObj->exec();
            $this->output( 'xml received' );
        }
        else break;
        
        switch( $options['type'] )
        {
          case 'poi':
            $loggerObj->setType( 'poi' );
            $xml = simplexml_load_string( $feedObj->getResponse() );
            $importer->addDataMapper( new kualaLumpurVenuesMapper( $vendor, $xml ) );
            break;

          case 'event':
            $loggerObj->setType( 'event' );
            $xml = $this->removeKualaLumpurMoviesFromEventFeed( simplexml_load_string( $feedObj->getResponse() ) );
            $importer->addDataMapper( new kualaLumpurEventsMapper( $vendor, $xml ) );
          break;

          case 'movie':
            $loggerObj->setType( 'movie' );
            $xml = $this->returnKualaLumpurMoviesFromEventFeed( simplexml_load_string( $feedObj->getResponse() ) );
            $importer->addDataMapper( new kualaLumpurMoviesMapper( $vendor, $xml ) );
          break;
        }
        unset( $xml );
        break; //end kuala_lumpur




    case 'uae':
        switch( $options['type'] )
        {
          case 'poi': $this->importUaePois();
            break;

          case 'poi-event': $this->importUaeEvents();
            break;

          case 'movies': $this->importUaeMovies();
            break;
        }
    break; // end dubai




    }//end switch

    $importer->run();

    $this->writeLogLine( 'end import for ' . $options['city'] . ' (type: ' . $options['type'] . ', environment: ' . $options['env'] . ') -- Peak memory used: ' . $this->byteToHumanReadable( memory_get_peak_usage( true ) ) );
  }




  /********************************************************************************
   *
   * CITY IMPORT FUNCTIONS
   *
   *
   *
   *
   *
   *    CHICAGO
   *
   */

  /**
   * Import Chicago's Events
   *
   *
   * @param Vendor $vendoObj./
   * @param FTPClient $ftpClientObj
   *
   */
  private function importChicagoEvents($vendorObj, $ftpClientObj)
  {
      try
        {

          echo "Downloading Chicago's Events Feed \n";
          $fileNameString = $ftpClientObj->fetchLatestFileByPattern( 'toc_leo.xml' );

          echo "Parsing Chicago's Events Feed \n";
          $processXmlObj = new processNyXml( $fileNameString );

          //$processXmlObj = new processNyXml('/var/workspace/projectn/import/chicago/toc_leo.xml');
          $processXmlObj->setEvents('/body/event')->setVenues('/body/address');
          
          echo "Inserting Chicago's Events  \n";
          $nyImportObj = new importNyChicagoEvents($processXmlObj,$vendorObj);
          $nyImportObj->insertEventCategoriesAndEventsAndVenues();

          echo "\n\n Chicago's Events imported \n\n";

        }
        catch ( Exception $e )
        {
          echo 'Exception caught in chicago' . $e->getMessage();
        }
  }


  /**
   * Import Chicago's Movies
   * 
   * @param <Vendor> $vendoObj
   * @param <FTPClient> $ftpClientObj 
   */
  private function importChicagoMovies($vendorObj, $ftpClientObj)
  {
    try
    {

      echo "Downloading Chicago's Movies feed \n";
      $fileNameString = $ftpClientObj->fetchLatestFileByPattern( 'xffd_TOChicago_[0-9]+.xml' );

      echo "Parsing Chicago's Movies \n";
      $processXmlObj = new processNyMoviesXml( $fileNameString );
      $processXmlObj->setMovies( '/xffd/movies/movie' );
      $processXmlObj->setPoi( '/xffd/theaters/theater' );
      $processXmlObj->setOccurances( '/xffd/showTimes/showTime' );

      echo "Importing Chicago's Movies \n";
      $nyImportMoviesObj = new importNyMovies( $processXmlObj, $vendorObj) ;
      $nyImportMoviesObj->importMovies();

      echo "\n\n Chicago's Movies Imported \n\n";

    }
    catch ( Exception $e )
    {
      echo 'Exception caught in chicago' . $options['city'] . ' ' . $options['type'] . ' import: ' . $e->getMessage();
    }
  }

 /**
  * Import the Chicago Bars and clubs
  *
  * @return importBc Object
  */
  public function importChicagoBc($vendorObj, $ftpClientObj)
  {
        try
        {
            echo "Downloading Chicago's B/C feed \n";
            $fileNameString = $ftpClientObj->fetchFile( 'toc_bc.xml' );

            echo "Parsing Chicago's B/C's feed \n";
            $processXmlObj = new processNyBcXml( $fileNameString );

            echo "Importing Chicago's B/C's \n";
            $importObj = new chicagoImportBcEd($processXmlObj, $vendorObj);
            $importObj->import();

            echo "\n\n Chicago's B/C's Imported \n\n";
                     
        }
        catch ( Exception $e )
        {
          echo 'Exception caught in Chicago import: ' . $e->getMessage();
        }
        return $importObj;
  }


  /**
  * Import the Chicago Bars and clubs
  *
  * @return importBc Object
  */
  public function importChicagoEd($vendorObj, $ftpClientObj)
  {
        try
        {
            echo "Downloading Chicago's E/D feed \n";
            $fileNameString = $ftpClientObj->fetchFile( 'toc_ed.xml' );
            
            echo "Parsing Chicago's E/D's feed \n";
            $processXmlObj = new processNyBcXml( $fileNameString );

            echo "Importing Chicago's E/D's \n";
            $importObj = new chicagoImportBcEd($processXmlObj, $vendorObj);
            $importObj->import();

            echo "\n\n Chicago's E/D's Imported \n\n";

        }
        catch ( Exception $e )
        {
          echo 'Exception caught in Chicago import: ' . $e->getMessage();
        }


        return $importObj;

  }
  

  /***************************************************************************
   *
   *    NEW YORK
   *
   * *************************************************************************/

  /**
   * Import NY's Events
   *
   * @param <Vendor> $vendoObj
   * @param <FTPClient> $ftpClientObj
   */
   private function importNyEvents($vendorObj, $ftpClientObj)
  {
       try
        {
          echo "Downloading NY's Event's feed \n";
          $fileNameString = $ftpClientObj->fetchLatestFileByPattern( 'tony_leo.xml' );

          echo "Parsing Ny's Event's feed \n";
          $processXmlObj = new processNyXml( $fileNameString );

          //$processXmlObj = new processNyXml( '/var/workspace/projectn/import/ny/tony_leo.xml' );
          
          $processXmlObj->setEvents('/body/event')->setVenues('/body/address');

          echo "Importing Ny's Event's \n";
          $nyImportObj = new importNyChicagoEvents($processXmlObj,$vendorObj);
          $nyImportObj->insertEventCategoriesAndEventsAndVenues();

          echo "\n\n NY's Event's Imported \n\n";
        }
        catch ( Exception $e )
        {
          echo 'Exception caught in NY events import '  . $e->getMessage();
        }
   }


   /**
    * Import NY's Movies
   *
   * @param <Vendor> $vendoObj
   * @param <FTPClient> $ftpClientObj
    */
   private function importNyMovies($vendorObj, $ftpClientObj)
   {
        try
        {
          echo "Downloading NY's Movies feed \n";
          $fileNameString = $ftpClientObj->fetchLatestFileByPattern( 'xffd_TONewYork_[0-9]+.xml' );

          echo "Parsing Ny's Movies feed \n";
          $processXmlObj = new processNyMoviesXml( $fileNameString );
          $processXmlObj->setMovies('/xffd/movies/movie');
          $processXmlObj->setPoi('/xffd/theaters/theater');
          $processXmlObj->setOccurances('/xffd/showTimes/showTime');

          echo "Importing Ny's Movies \n";
          $nyImportMoviesObj = new importNyMovies($processXmlObj,$vendorObj);
          $nyImportMoviesObj->importMovies();

          echo "\n\n NY's Movies Imported \n\n";
        }
        catch ( Exception $e )
        {
          echo 'Exception caught in chicago' . $options['city'] . ' ' . $options['type'] . ' import: ' . $e->getMessage();
        }
   }


     private function importNyBc($vendorObj, $ftpClientObj)
     {
        try
        {
            echo "Downloading B/C's feed \n";
            $fileNameString = $ftpClientObj->fetchFile( 'tony_bc.xml' );

            echo "Parsing Ny's B/C's feed \n";
            $processXmlObj = new processNyBcXml( $fileNameString );

            echo "Importing Ny's B/C's \n";
            $importBcEd = new nyImportBcEd($processXmlObj, $vendorObj, nyImportBcEd::BAR_CLUB );
            $importBcEd->import();

           echo "\n\n NY's B/C's Imported \n\n";
        }
        catch ( Exception $e )
        {
          echo "Exception caught in NY's B/C's import: " . $e->getMessage();
        }
         
     }


     private function importNyEd($vendorObj, $ftpClientObj)
     {
        try
        {

            echo "Downloading E/D's feed \n";
            $fileNameString = $ftpClientObj->fetchFile( 'tony_ed.xml' );

            echo "Parsing Ny's E/D's feed \n";
            $processXmlObj = new processNyBcXml( $fileNameString );

            echo "Importing Ny's E/D's \n";
            $importBcEd = new nyImportBcEd($processXmlObj, $vendorObj, nyImportBcEd::RESTAURANT );
            $importBcEd->import();

            echo "\n\n NY's E/D's Imported \n\n";
        }
        catch ( Exception $e )
        {
          echo "Exception caught in NY's E/D import: " . $e->__toString();
        }

     }



   /***************************************************************************
   *
   *    Dubai
   *
   * *************************************************************************/
    private function importUaePois()
     {
        try
        {
            echo "Starting to import Dubai Bars  \n";
            echo "Downloading Dubai bars Feed \n";
            $vendorObj = $this->getVendorByCityAndLanguage('dubai', 'en-US');

            $feed = new Curl('http://www.timeoutdubai.com/nokia/bars');
            $feed->exec();

            echo "Validating Dubai Bars XML \n";
            $xmlObj = new ValidateUaeXmlFeed($feed->getResponse());
            $xmlFeedObj = $xmlObj->getXmlFeed();
            
            
            echo "Importing Dubai Bars \n";
            $importDubaiBars = new ImportUaeBars($xmlFeedObj, $vendorObj);
            $importDubaiBars->importPois();




            echo "Starting to Import Dubhai Restaurants \n";
            echo "Downloading Dubai restaurants Feed \n";
            $feed = new Curl('http://www.timeoutdubai.com/nokia/restaurants');
            $feed->exec();


            echo "Validating Dubai restaurants XML \n";
            $xmlObj = new ValidateUaeXmlFeed($feed->getResponse());
            $xmlFeedObj = $xmlObj->getXmlFeed();

            echo "Importing Dubai Restaurants \n";
            $importDubaiRestaurants = new ImportUaeRestaurants($xmlFeedObj, $vendorObj);
            $importDubaiRestaurants->importPois();


            /**
             * Abu Dhabi's imports
             */

            echo "Starting to import Abu Dhab bars \n";
            echo "Downloading Abu Dhabis bars Feed \n";
            $vendorObj = $this->getVendorByCityAndLanguage('abu dhabi', 'en-US');

            $feed = new Curl('http://www.timeoutabudhabi.com/nokia/bars');
            $feed->exec();

            echo "Validating Abu Dhabis Bars XML \n";
            $xmlObj = new ValidateUaeXmlFeed($feed->getResponse());
            $xmlFeedObj = $xmlObj->getXmlFeed();

            echo "Importing Abu Dhabi's Bars \n";
            $importDubaiBars = new ImportUaeBars($xmlFeedObj, $vendorObj);
            $importDubaiBars->importPois();



            echo "Starting to Import Abu Dhab Resaurants \n";
            $vendorObj = $this->getVendorByCityAndLanguage('abu dhabi', 'en-US');

            $feed = new Curl('http://www.timeoutabudhabi.com/nokia/restaurants');
            $feed->exec();

            echo "Validating Abu Dhabis Resaurants XML \n";
            $xmlObj = new ValidateUaeXmlFeed($feed->getResponse());
            $xmlFeedObj = $xmlObj->getXmlFeed();

             echo "Importing Abu Dhabi's Resaurants \n";
            $importDubaiBars = new ImportUaeBars($xmlFeedObj, $vendorObj);
            $importDubaiBars->importPois();

        }
        catch ( Exception $e )
        {
          echo 'Exception caught in Dubai Bars import: ' . $e->getMessage();
        }

     }

      private function importUaeEvents()
     {
        try
        {

            echo "Starting Import of Dubai Events \n";
            $vendorObj = $this->getVendorByCityAndLanguage('dubai', 'en-US');

            echo "Downloading Dubai Events feed \n";
            $feed = new Curl('http://www.timeoutdubai.com/nokia/latestevents');
            $feed->exec();

            echo "Validating Dubai Events feed \n";
            $xmlObj = new ValidateUaeXmlFeed($feed->getResponse());
            $xmlFeedObj = $xmlObj->getXmlFeed();

             echo "Importint Dubai Events \n";
            $importUaeEventsObj = new ImportUaeEvents($xmlFeedObj, $vendorObj);
            $importUaeEventsObj->import();


            echo " \n\n\n\n\n\n\n ";

            echo "Starting to import Abu Dhab Events \n";
            $vendorObj = $this->getVendorByCityAndLanguage('abu dhabi', 'en-US');

            echo "Downloading Abu Dhab Events feed \n";
            $feed = new Curl('http://www.timeoutabudhabi.com/nokia/latestevents');
            $feed->exec();

            echo "Validating Abu Dhab Events feed \n";
            $xmlObj = new ValidateUaeXmlFeed($feed->getResponse());
            $xmlFeedObj = $xmlObj->getXmlFeed();

            echo "Importing Abu Dhab Events \n";
            $importUaeEventsObj = new ImportUaeEvents($xmlFeedObj, $vendorObj);
            $importUaeEventsObj->import();
        }
        catch ( Exception $e )
        {
          echo 'Exception caught in Dubai Bars import: ' . $e->getMessage();
        }

     }

     /**
      * Import the UAE movies
      */
     private function importUaeMovies()
     {
        try
        {
            $vendorObj = $this->getVendorByCityAndLanguage('dubai', 'en-US');

            //Dubia
            $feed = new Curl('http://www.timeoutdubai.com/customfeed/nokia/films');
            $feed->exec();
            $xmlObj = new ValidateUaeXmlFeed($feed->getResponse());
            $xmlFeedObj = $xmlObj->getXmlFeed();


            $importUaeMoviesObj = new ImportUaeMovies($xmlFeedObj, $vendorObj);
            $importUaeMoviesObj->import();

            echo "Dubai added \n\n";

            $vendorObj = $this->getVendorByCityAndLanguage('abu dhabi', 'en-US');

            //Abu Dhabi
            $feed = new Curl('http://www.timeoutabudhabi.com/customfeed/nokia/films');
            $feed->exec();
            $xmlObj = new ValidateUaeXmlFeed($feed->getResponse());
            $xmlFeedObj = $xmlObj->getXmlFeed();


            $importUaeMoviesObj = new ImportUaeMovies($xmlFeedObj, $vendorObj);
            $importUaeMoviesObj->import();

            echo "Abu Dhabi added \n\n";

           /* //Doha
            $feed = new Curl('http://www.timeoutdoha.com/customfeed/nokia/films');
            $feed->exec();
            $xmlObj = new ValidateUaeXmlFeed($feed->getResponse());
            $xmlFeedObj = $xmlObj->getXmlFeed();


            $importUaeMoviesObj = new ImportUaeMovies($xmlFeedObj, $vendorObj);
            $importUaeMoviesObj->import();


            echo "Doha \n\n";

            //Doha
            $feed = new Curl('http://www.timeoutbahrain.com/customfeed/nokia/films');
            $feed->exec();
            $xmlObj = new ValidateUaeXmlFeed($feed->getResponse());
            $xmlFeedObj = $xmlObj->getXmlFeed();


            $importUaeMoviesObj = new ImportUaeMovies($xmlFeedObj, $vendorObj);
            $importUaeMoviesObj->import();

               echo "Aahraain added \n\n";*/

            
        }
        catch ( Exception $e )
        {
          echo 'Exception caught in Dubai Bars import: ' . $e->getMessage();
        }

     }

  /**
   * Get the Vendor by its city and language
   *
   * @param string $city
   * @param string $language
   *
   * @return object Result
   */
  private function getVendorByCityAndLanguage($city, $language)
  {
    $vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage($city, $language);

    //Set the new vendor if one doens't exist
    if(!$vendorObj){
      $vendorObj = new Vendor();
      $vendorObj->setCity($city);
      $vendorObj->setLanguage($language);

      $vendorObj->save();

    }

    return $vendorObj;
  }

  private function output( $message )
  {
    if( $this->options['verbose'] ) echo $message . PHP_EOL ;
  }

  private function writeLogLine( $message )
  {
      echo PHP_EOL . date( 'Y-m-d H:m:s' ) . ' -- ' . $message . ' -- ' . PHP_EOL . PHP_EOL;
  }

  /**
   *
   * taken from http://uk2.php.net/manual/en/function.memory-get-usage.php
   */
  private function byteToHumanReadable( $size )
  {
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];

  }

  private function returnKualaLumpurMoviesFromEventFeed( SimpleXMLElement $feed )
  {
    $string = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" encoding="UTF-8" indent="yes" cdata-section-elements="id keyword category subCategory genre title short_description descripton url venue start_date end_date start_time venue_area venue_address venue_map contact tel_no email booking price small_image big_image" />
	<xsl:template match="/">
		<xsl:element name="event">
			<xsl:for-each select="//event/eventDetails">
				<xsl:if test="categories[(category/text()='Film' and subCategory/text()='Screenings')]">
					<xsl:copy-of select="." />
				</xsl:if>
			</xsl:for-each>
		</xsl:element>
	</xsl:template>
</xsl:stylesheet>
EOF;

    $xsl = new DOMDocument();
    $xsl->loadXML( $string );

    $xslProcessor = new XSLTProcessor();
    $xslProcessor->importStyleSheet( $xsl );

    return new SimpleXMLElement( $xslProcessor->transformToXML( dom_import_simplexml( $feed ) ) );
  }

  private function removeKualaLumpurMoviesFromEventFeed( SimpleXMLElement $feed )
  {
    $string = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" encoding="UTF-8" indent="yes" cdata-section-elements="id keyword category subCategory genre title short_description descripton url venue start_date end_date start_time venue_area venue_address venue_map contact tel_no email booking price small_image big_image" />
	<xsl:template match="/">
		<xsl:element name="event">
			<xsl:for-each select="//event/eventDetails">
				<xsl:if test="categories[not(category/text()='Film' and subCategory/text()='Screenings')]">
					<xsl:copy-of select="." />
				</xsl:if>
			</xsl:for-each>
		</xsl:element>
	</xsl:template>
</xsl:stylesheet>
EOF;

    $xsl = new DOMDocument();
    $xsl->loadXML( $string );

    $xslProcessor = new XSLTProcessor();
    $xslProcessor->importStyleSheet( $xsl );

    return new SimpleXMLElement( $xslProcessor->transformToXML( dom_import_simplexml( $feed ) ) );
  }
}
