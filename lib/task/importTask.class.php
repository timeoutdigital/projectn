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

    // Select the City
    switch( $options['city'] )
    {
      case 'ny':

        $vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('ny', 'en-US');

        switch( $options['type'] )
        {
          case 'poi-event':
                //Setup NY FTP @todo refactor FTPClient to not connect in constructor
                $ftpClientObj = new FTPClient( 'ftp.timeoutny.com', 'london', 'timeout', $vendorObj[ 'city' ] );
                $ftpClientObj->setSourcePath( '/NOKIA/' );
                $this->importNyEvents($vendorObj, $ftpClientObj);
            break;

          case 'movie':
                ImportLogger::getInstance()->setVendor( $vendorObj );
                $importer->addDataMapper( new londonDatabaseFilmsDataMapper( $vendorObj ) );
                $importer->run();
                ImportLogger::getInstance()->end();
                $this->dieWithLogMessage();
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

              /*$vendor = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('ny', 'en-US');
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

          default : $this->dieDueToInvalidTypeSpecified();

        }
        break; // end ny

      case 'chicago':

        $vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('chicago', 'en-US');
        $ftpClientObj = new FTPClient( 'ftp.timeoutchicago.com', 'timeout', 'y6fv2LS8', $vendorObj[ 'city' ] );

        switch( $options['type'] )
        {
            case 'test-bc':
                echo "Downloading Chicago's BC Feed \n";
                $fileNameString = $ftpClientObj->fetchLatestFileByPattern( 'toc_bc.xml' );

                echo "Importing Chicago's BC \n";

                ImportLogger::getInstance()->setVendor( $vendorObj );
                $importer->addDataMapper( new ChicagoFeedBCMapper($vendorObj, $fileNameString) );
                $importer->run();
                ImportLogger::getInstance()->end();
                $this->dieWithLogMessage();

              break;
            case 'test-ed':
                echo "Downloading Chicago's ED Feed \n";
                $fileNameString = $ftpClientObj->fetchLatestFileByPattern( 'toc_ed.xml' );

                echo "Importing Chicago's ED \n";

                ImportLogger::getInstance()->setVendor( $vendorObj );
                $importer->addDataMapper( new ChicagoFeedEDMapper($vendorObj, $fileNameString) );
                $importer->run();
                ImportLogger::getInstance()->end();
                $this->dieWithLogMessage();

              break;
          case 'test-poi':
                echo "Downloading Chicago's Poi/Events Feed \n";
                $fileNameString = $ftpClientObj->fetchLatestFileByPattern( 'toc_leo.xml' );

                echo "Parsing Chicago's Poi/Events Feed \n";
                $xmlData = simplexml_load_file( $fileNameString );

                echo "Importing Chicago's Pois \n";

                ImportLogger::getInstance()->setVendor( $vendorObj );
                $importer->addDataMapper( new ChicagoFeedPoiMapper($vendorObj, $xmlData) );
                $importer->run();
                ImportLogger::getInstance()->end();
                $this->dieWithLogMessage();

              break;
          case 'test-event':
                echo "Downloading Chicago's Poi/Events Feed \n";
                $fileNameString = $ftpClientObj->fetchLatestFileByPattern( 'toc_leo.xml' );

                echo "Parsing Chicago's Poi/Events Feed \n";
                $xmlData = simplexml_load_file( $fileNameString );

                echo "Importing Chicago's Events \n";

                // @todo Wrap in try catch
                // We are to Run two Import on Events to Manage Memory!
                $eventsNode = $xmlData->xpath( '/body/event' );
                $totalCount = count($eventsNode); 
                $splitAt = round( $totalCount / 2 );
                
                ImportLogger::getInstance()->setVendor( $vendorObj );
                $importer->addDataMapper( new ChicagoFeedEventMapper($vendorObj, $xmlData, null, $eventsNode, 0, $splitAt) );
                $importer->run();
                ImportLogger::getInstance()->end();

                // Run the Second one
                ImportLogger::getInstance()->setVendor( $vendorObj );
                $importer->addDataMapper( new ChicagoFeedEventMapper($vendorObj, $xmlData, null, $eventsNode, $splitAt, $totalCount ) );
                $importer->run();
                ImportLogger::getInstance()->end();
                
                $this->dieWithLogMessage();
                
              break;
          case 'poi-event':
              $this->importChicagoEvents($vendorObj, $ftpClientObj);
            break;

          case 'movie':
              ImportLogger::getInstance()->setVendor( $vendorObj );
              $importer->addDataMapper( new londonDatabaseFilmsDataMapper( $vendorObj ) );
              $importer->run();
              ImportLogger::getInstance()->end();
              $this->dieWithLogMessage();
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

           default : $this->dieDueToInvalidTypeSpecified();
        }
        break; //end chicago

      case 'singapore':
        $vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('singapore', 'en-US');

        //must be set for price range function
        //@todo get get this info out of vendor?!
        setlocale(LC_MONETARY, 'en_US.UTF-8');

        switch( $options['type'] )
        {
          case 'poi-event':
            //http://www.timeoutsingapore.com/xmlapi/events/?section=index&full&key=ffab6a24c60f562ecf705130a36c1d1e
            //http://www.timeoutsingapore.com/xmlapi/venues/?section=index&full&key=ffab6a24c60f562ecf705130a36c1d1e

            echo "Starting Singapore Pois import \n";
            $curlImporterObj = new curlImporter();
            $parametersArray = array( 'section' => 'index', 'full' => '', 'key' => 'ffab6a24c60f562ecf705130a36c1d1e' );

            echo "Getting Singapore poi-event feed\n";
            $curlImporterObj->pullXml ('http://www.timeoutsingapore.com/xmlapi/venues/', '', $parametersArray, 'GET', true );
            $xmlObj = $curlImporterObj->getXml();

            echo "Importing Singapores Pois \n\n";
            $this->object = new singaporeImport( $vendorObj, $curlImporterObj );
            $this->object->insertPois( $xmlObj );

            echo "Starting Singapore Events import \n";
            $curlImporterObj = new curlImporter();
            $parametersArray = array( 'section' => 'index', 'full' => '', 'key' => 'ffab6a24c60f562ecf705130a36c1d1e' );

            echo "Getting reading Singapore poi-event feed";
            $curlImporterObj->pullXml ('http://www.timeoutsingapore.com/xmlapi/events/', '', $parametersArray, 'GET', true );
            $xmlObj = $curlImporterObj->getXml();

            $this->object = new singaporeImport( $vendorObj, $curlImporterObj, 'http://www.timeoutsingapore.com/xmlapi/xml_detail/?venue={venueId}&key=ffab6a24c60f562ecf705130a36c1d1e' );
            $this->object->insertEvents( $xmlObj );

            ImportLogger::getInstance()->end();
            $this->dieWithLogMessage();

            break;

          case 'movie':
            //http://www.timeoutsingapore.com/xmlapi/movies/?section=index&full&key=ffab6a24c60f562ecf705130a36c1d1e

            echo "Connecting to Singapore Movie Feed \n";
            $curlImporterObj = new curlImporter();
            $parametersArray = array( 'section' => 'index', 'full' => '', 'key' => 'ffab6a24c60f562ecf705130a36c1d1e' );
            $curlImporterObj->pullXml ('http://www.timeoutsingapore.com/xmlapi/movies/', '', $parametersArray, 'GET', true );

            echo "Importing Singapore Feed \n";
            $xmlObj = $curlImporterObj->getXml();

            echo "Importing Movie Data";
            $this->object = new singaporeImport( $vendorObj, $curlImporterObj );
            $this->object->insertMovies( $xmlObj );

            echo "Impored Singapores Movies \n";
            ImportLogger::getInstance()->end();
            $this->dieWithLogMessage();

          break;

          case 'eating-drinking':
          break;

          default : $this->dieDueToInvalidTypeSpecified();
        }

        break; //end singapore

      case 'lisbon':

        $vendorObj    = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('lisbon', 'pt');
        $feedObj      = new curlImporter();

        $daysAhead    = 7; //lisbon caps the request at max 9 days

        $url          = 'http://www.timeout.pt/';
        $parameters   = array(
            'from' => date( 'Y-m-d' ),
            'to' => date( 'Y-m-d', strtotime( "+$daysAhead day" ) )
        );

        switch( $options['type'] )
        {
          case 'poi':
            $request = 'xmlvenues.asp';
            $feedObj->pullXml ( $url, $request, $parameters, 'POST' );

            ImportLogger::getInstance()->setVendor( $vendorObj );
            $importer->addDataMapper( new LisbonFeedVenuesMapper( $feedObj->getXml() ) );
            $importer->run();
            ImportLogger::getInstance()->end();
            $this->dieWithLogMessage();

          break;

          case 'event':
            $request = 'xmllist.asp';
            ImportLogger::getInstance()->setVendor( $vendorObj );

            $startDate = time();
            while ( $startDate < strtotime( "+3 month" ) ) // Only look 3 months ahead
            {
              try
              {
                $parameters = array(
                    'from' => date( 'Y-m-d', $startDate ),
                    'to' => date( 'Y-m-d', strtotime( "+$daysAhead day", $startDate ) ) // Query x days ahead
                );

                // Move start date ahead one day from last end date
                $startDate = strtotime( "+".( $daysAhead +1 )." day", $startDate );

                echo "Getting Lisbon Events for Period: " . $parameters[ 'from' ] . "-" . $parameters[ 'to' ] . PHP_EOL;
                $feedObj->pullXml( $url, $request, $parameters, 'POST' );

                $importer->addDataMapper( new LisbonFeedListingsMapper( $feedObj->getXml() ) );
              }
              catch ( Exception $e )
              {
                ImportLogger::getInstance()->addError( $e );
              }
            }

            $importer->run();
            ImportLogger::getInstance()->end();
            $this->dieWithLogMessage();

          break;

          case 'movie':
            $request = 'xmlfilms.asp';
            $feedObj->pullXml ( $url, $request, $parameters, 'POST' );

            ImportLogger::getInstance()->setVendor( $vendorObj );
            $importer->addDataMapper( new LisbonFeedMoviesMapper( $feedObj->getXml() ) );
            $importer->run();
            ImportLogger::getInstance()->end();
            $this->dieWithLogMessage();
          break;

          default : $this->dieDueToInvalidTypeSpecified();
        }
        break; //end lisbon


      // Russian Cities
      case 'moscow':
      case 'saint petersburg':
      case 'omsk':
      case 'almaty':
      case 'novosibirsk':
      case 'krasnoyarsk':
      case 'tyumen':
      case 'russia':

        $city = $options['city'];
        if( $city == 'russia' ) $city = 'unknown';

        $vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage( $city, 'ru' );

        switch( $options['type'] )
        {
            case 'poi':

                $feedName = array();
                $feedName['moscow']              = 'places_msk.xml';
                $feedName['saint petersburg']    = 'places_spb.xml';
                $feedName['omsk']                = 'places_omsk.xml';
                $feedName['almaty']              = 'places_almaty.xml';
                $feedName['novosibirsk']         = 'places_novosibirsk.xml';
                $feedName['krasnoyarsk']         = 'places_krasnoyarsk.xml';
                $feedName['tyumen']              = 'places_tumen.xml';

                if( !in_array( $city, array_keys( $feedName ) ) )
                    $this->dieWithLogMessage( 'No Feed Available For City Named: ' . $city );

                $feedUrl = 'http://www.timeout.ru/london/' . $feedName[ $city ];
                $mapperClass = 'RussiaFeedPlacesMapper';

            break; //End Poi

            case 'event':

                $feedName = array();
                $feedName['moscow']              = 'events_msk.xml';
                $feedName['saint petersburg']    = 'events_spb.xml';
                $feedName['omsk']                = 'events_omsk.xml';
                $feedName['almaty']              = 'events_almaty.xml';
                $feedName['novosibirsk']         = 'events_novosibirsk.xml';
                $feedName['krasnoyarsk']         = 'events_krasnoyarsk.xml';
                $feedName['tyumen']              = 'events_tumen.xml';

                if( !in_array( $city, array_keys( $feedName ) ) )
                    $this->dieWithLogMessage( 'No Feed Available For City Named: ' . $city );

                $feedUrl = 'http://www.timeout.ru/london/' . $feedName[ $city ];
                $mapperClass = 'RussiaFeedEventsMapper';

            break; //End Event

            case 'movie':

                $feedUrl = 'http://www.timeout.ru/london/movies.xml';
                $mapperClass = 'RussiaFeedMoviesMapper';

            break; //End Movie

            default : $this->dieDueToInvalidTypeSpecified();
        }

        $feedObj = new Curl( $feedUrl );
        $feedObj->exec();
        $xml = simplexml_load_string( $feedObj->getResponse() );

        ImportLogger::getInstance()->setVendor( $vendorObj );
        $importer->addDataMapper( new $mapperClass( $xml, null, $city ) );
        $importer->run();
        ImportLogger::getInstance()->end();

        $this->dieWithLogMessage();

      break; //End Russian Cities


      case 'london':

        $vendor = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage( 'london', 'en-GB' );
        $databaseManager->getDatabase( 'searchlight_london' )->getConnection(); // Set sfDatabase

        switch( $options['type'] )
        {
          case 'poi-ev-mapper': $importer->addDataMapper( new LondonDatabaseEventsAndVenuesMapper( 'poi' ) );
          break; //End EventsAndVenuesMapper

          case 'poi-bars-pubs': $importer->addDataMapper( new LondonAPIBarsAndPubsMapper() );
          break; // End BarsAndPubsMapper

          case 'poi-restaurants': $importer->addDataMapper( new LondonAPIRestaurantsMapper() );
          break; // End RestaurantsMapper

          case 'poi-cinemas': $importer->addDataMapper( new LondonAPICinemasMapper() );
          break; //End CinemasMapper

          case 'event': $importer->addDataMapper( new LondonDatabaseEventsAndVenuesMapper( 'event' ) );
          break; //End Event

          case 'event-occurrence': $importer->addDataMapper( new LondonDatabaseEventsAndVenuesMapper( 'event-occurrence' ) );
          break; //End Event-Occurrence

          case 'movie': $importer->addDataMapper( new londonDatabaseFilmsDataMapper( $vendor ) );
          break; //End Movie

          default : $this->dieDueToInvalidTypeSpecified();
        }

        ImportLogger::getInstance()->setVendor( $vendor );
        $importer->run();
        ImportLogger::getInstance()->end();
        $this->dieWithLogMessage();

      break; //end London


      case 'sydney':

        $vendor = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage( 'sydney', 'en-AU' );

        switch( $options['type'] )
        {
          case 'poi':

            $targetFileName = 'venues.xml';
            $mapperClass = 'sydneyFtpVenuesMapper';

          break; //End Poi

          case 'event':

            $targetFileName = 'event.xml';
            $mapperClass = 'sydneyFtpEventsMapper';

          break; //End Event

          case 'movie':

            $targetFileName = 'movie.xml';
            $mapperClass = 'sydneyFtpMoviesMapper';

          break; //End Movie

          default : $this->dieDueToInvalidTypeSpecified();
        }

        ImportLogger::getInstance()->setVendor( $vendor );

        $ftpClient = new FTPClient( 'ftp.timeoutsydney.com.au', 'timeoutlondon', 'T1m3outl*nd)n', $vendor[ 'city' ] );
        $ftpClient->setSourcePath( '/timeoutlondon/' );
        $ftpFiles = $this->parseSydneyFtpDirectoryListing( $ftpClient->fetchRawDirListing() );

        $xml = simplexml_load_file( $ftpClient->fetchFile( $ftpFiles[ $options['type'] ], $targetFileName ) );

        $importer->addDataMapper( new $mapperClass( $vendor, $xml ) );
        $importer->run();

        ImportLogger::getInstance()->end();
        $this->dieWithLogMessage();

     break; //end Sydney


    case 'kuala lumpur':

        $vendor = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage( 'kuala lumpur', 'en-MY' );

        if( in_array( $options['type'], array( "event", "movie" ) ) )
            $feedObj = new Curl( 'http://www.timeoutkl.com/xml/events.xml' );
        elseif( $options['type'] == "poi" )
            $feedObj = new Curl( 'http://www.timeoutkl.com/xml/venues.xml' );
        else break;

        $feedObj->exec();
        $xml = simplexml_load_string( $feedObj->getResponse() );

        switch( $options['type'] )
        {
          case 'poi':

            $mapperClass = 'kualaLumpurVenuesMapper';

          break; //End Poi

          case 'event':
            $xml = $this->removeKualaLumpurMoviesFromEventFeed( $xml );
            $mapperClass = 'kualaLumpurEventsMapper';

          break; //End Event

          case 'movie':

            $xml = $this->returnKualaLumpurMoviesFromEventFeed( $xml );
            $mapperClass = 'kualaLumpurMoviesMapper';

          break; //End Movie

          default : $this->dieDueToInvalidTypeSpecified();
        }

        ImportLogger::getInstance()->setVendor( $vendor );

        $importer->addDataMapper( new $mapperClass( $vendor, $xml ) );
        $importer->run();

        ImportLogger::getInstance()->end();
        $this->dieWithLogMessage();

    break; //end Kuala Lumpur


    case 'barcelona':

        $vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage( 'barcelona', 'ca' );

        switch( $options['type'] )
        {
          case 'poi':

            $feedUrl = "http://projectn-pro.gnuinepath.com/venues.xml";
            $mapperClass = "barcelonaVenuesMapper";

          break; //end Poi

          case 'event':

            $feedUrl = "http://projectn-pro.gnuinepath.com/events.xml";
            $mapperClass = "barcelonaEventsMapper";

          break; //end Event

          case 'movie':

            $feedUrl = "http://projectn-pro.gnuinepath.com/movies.xml";
            $mapperClass = "barcelonaMoviesMapper";

          break; //end Movie

          default : $this->dieDueToInvalidTypeSpecified();
        }

        $feedObj = new Curl( $feedUrl );
        $feedObj->exec();
        $xml = simplexml_load_string( $feedObj->getResponse() );

        ImportLogger::getInstance()->setVendor( $vendorObj );
        $importer->addDataMapper( new $mapperClass( $xml ) );
        $importer->run();
        ImportLogger::getInstance()->end();
        $this->dieWithLogMessage();

    break; // end Barcelona


    case 'uae':
        switch( $options['type'] )
        {
          case 'poi': $this->importUaePois(); break;
          case 'poi-event': $this->importUaeEvents(); break;
          case 'movies': $this->importUaeMovies(); break;
          default : $this->dieDueToInvalidTypeSpecified();
        }
    break; // end uae


    // data entry imports
    case 'mumbai':
    case 'delhi':
    case 'bangalore':
    case 'pune':
        $dataEntryImportManager = new DataEntryImportManager( $options['city'], '/var/vhosts/projectn_data_entry/export/' );
        switch( $options['type'] )
        {
          case 'poi'   : $dataEntryImportManager->importPois();   break;
          case 'event' : $dataEntryImportManager->importEvents(); break;
          case 'movie' : $dataEntryImportManager->importMovies(); break;
          default : $this->dieDueToInvalidTypeSpecified();
        }

        $this->dieWithLogMessage();
    break; //end data entry imports

    case 'beijing':
        
        switch( $options['type'] )
        {
            case 'poi':
                $pdoDB = null;
                try {

                    $pdoDB = new PDO("mysql:host=80.250.104.16;dbname=searchlight", 'projectn', 'outtime99', array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8") );

                    echo 'Database Connection Estabilished' . PHP_EOL;

                    $importer->addDataMapper( new BeijingFeedVenueMapper( $pdoDB ) );
                    $importer->run();

                }
                catch(PDOException $e)
                {
                    echo 'PDO Connection Exception: ' . $e->getMessage() . PHP_EOL;
                    return;
                } catch( Exception $e)
                {
                    echo 'Beijing Import Error: ' . $e->getMessage();
                    return;
                }

                $this->dieWithLogMessage();

                break;
            default : $this->dieDueToInvalidTypeSpecified();
        }

    break;

    default : $this->dieWithLogMessage( 'FAILED IMPORT - INVALID CITY SPECIFIED' );

    }//end switch
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
            $vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('dubai', 'en-US');

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
            $vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('abu dhabi', 'en-US');

            $feed = new Curl('http://www.timeoutabudhabi.com/nokia/bars');
            $feed->exec();

            echo "Validating Abu Dhabis Bars XML \n";
            $xmlObj = new ValidateUaeXmlFeed($feed->getResponse());
            $xmlFeedObj = $xmlObj->getXmlFeed();

            echo "Importing Abu Dhabi's Bars \n";
            $importDubaiBars = new ImportUaeBars($xmlFeedObj, $vendorObj);
            $importDubaiBars->importPois();



            echo "Starting to Import Abu Dhab Resaurants \n";
            $vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('abu dhabi', 'en-US');

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
            $vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('dubai', 'en-US');

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
            $vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('abu dhabi', 'en-US');

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
            $vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('dubai', 'en-US');

            //Dubia
            $feed = new Curl('http://www.timeoutdubai.com/customfeed/nokia/films');
            $feed->exec();
            $xmlObj = new ValidateUaeXmlFeed($feed->getResponse());
            $xmlFeedObj = $xmlObj->getXmlFeed();


            $importUaeMoviesObj = new ImportUaeMovies($xmlFeedObj, $vendorObj);
            $importUaeMoviesObj->import();

            echo "Dubai added \n\n";

            $vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('abu dhabi', 'en-US');

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

  private function dieDueToInvalidTypeSpecified()
  {
      $this->dieWithLogMessage( 'FAILED IMPORT - INVALID TYPE SPECIFIED' );
  }

  private function dieWithLogMessage( $custom_message = "" )
  {
    if( $custom_message ) $this->writeLogLine( $custom_message );

    $message = "";
    $message.= 'end import for ' . $this->options['city'];
    $message.= ' (type: ' . $this->options['type'] . ', environment: ' . $this->options['env'] . ')';
    $message.= ' -- Peak memory used: ' . stringTransform::byteToHumanReadable( memory_get_peak_usage( true ) );
    $this->writeLogLine( $message );

    echo PHP_EOL;
    die;
  }

  private function writeLogLine( $message )
  {
      echo PHP_EOL . date( 'Y-m-d H:i:s' ) . ' -- ' . $message . ' -- ' . PHP_EOL;
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
				<xsl:if test="categories[(category/text()='Film' and (subCategory/text()='Screenings' or subCategory/text()='Movies'))]">
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
				<xsl:if test="categories[not(category/text()='Film' and (subCategory/text()='Screenings' and subCategory/text()='Movies'))]">
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

    private function parseSydneyFtpDirectoryListing( $rawFtpListingOutput )
    {
        $fileListSorted = array();
        //sort the files  so the newest file should be the first item in the list
        foreach ($rawFtpListingOutput as $fileListing)
        {
            $fileName = preg_replace( '/^.*?([-a-z0-9_]*.xml)$/', '$1', $fileListing );

            preg_match( '/^.*_([0-9\-]+)\.xml$/', $fileName, $matches );

            if( isset( $matches [1] ) )
            {
                $date = date( 'Y-m-d' ,strtotime($matches[1] ));
                $fileListSorted[ $date . ' ' .$fileName ] =   $fileListing;
            }else
            {
                 $this->writeLogLine( "Failed to Extract All File Names From Sydney FTP Directory Listing. FILE NAME FORMAT MIGHT BE CHANGED" );
                 return NULL;
            }

        }
        ksort ( $fileListSorted );
        $fileListSorted = array_reverse( $fileListSorted );
        // sorting is done

        //map the files to our terms
        $ftpFiles = array();
        foreach( $fileListSorted as $fileListing )
        {
          //get rid of the date / other info from ls command
          $filename = preg_replace( '/^.*?([-a-z0-9_]*.xml)$/', '$1', $fileListing );
          if( strpos( $filename, 'venue' ) !== false  && ! isset( $ftpFiles[ 'poi' ] ) )       $ftpFiles[ 'poi' ]   = $filename; // If File Listing is For a POI
          elseif( strpos( $filename, 'event' ) !== false   && ! isset( $ftpFiles[ 'event' ] )) $ftpFiles[ 'event' ] = $filename; // If File Listing is For an Event
          elseif( strpos( $filename, 'film' ) !== false   && ! isset( $ftpFiles[ 'movie' ] ))  $ftpFiles[ 'movie' ] = $filename; // If File Listing is For a Movie
        }

        if( !isset( $ftpFiles[ 'poi' ] ) || !isset( $ftpFiles[ 'event' ] ) || !isset( $ftpFiles[ 'movie' ] ) )
            $this->writeLogLine( "Failed to Extract All File Names From Sydney FTP Directory Listing." );

        return $ftpFiles;
    }



}
