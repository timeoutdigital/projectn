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
        case 'nyfix':
            $vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('ny', 'en-US');

            switch( $options['type'] )
            {
                case 'fixid':
                ImportLogger::getInstance()->setVendor( $vendorObj );

                $task = new mapNyVendorPoiId2NewId( new sfEventDispatcher, new sfFormatter );
                $task->runFromCLI( new sfCommandManager, array());

                ImportLogger::getInstance()->end();
                $this->dieWithLogMessage();
                break;
            }
            break;
      case 'ny':

        $vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('ny', 'en-US');

        switch( $options['type'] )
        {
            case 'poi-event':
                ImportLogger::getInstance()->setVendor( $vendorObj );
                // Set FTP
                $ftpClientObj = new FTPClient( 'ftp.timeoutny.com', 'london', 'timeout', $vendorObj[ 'city' ] );
                $ftpClientObj->setSourcePath( '/NOKIA/' );

                echo "Downloading NY's Event's feed \n";
                $fileNameString = $ftpClientObj->fetchLatestFileByPattern( 'tony_leo.xml' );

                // Load XML file
                $xmlString      = file_get_contents( $fileNameString );
                $xmlDataFixer   = new xmlDataFixer( $xmlString );
                //$xmlDataFixer->addRootElement( 'body' );
                $xmlDataFixer->removeHtmlEntiryEncoding();
                $xmlDataFixer->encodeUTF8();

                $processXmlObj = new processNyXml( '' );
                $processXmlObj->xmlObj  = $xmlDataFixer->getSimpleXML();
                $processXmlObj->setEvents('/leo_export/event')->setVenues('/leo_export/address');

                echo "Importing NY Events / Poi  \n";
                $nyImportObj = new importNyChicagoEvents($processXmlObj,$vendorObj);
                $nyImportObj->insertEventCategoriesAndEventsAndVenues();
                ImportLogger::getInstance()->end();
                $this->dieWithLogMessage();

                break;
          case 'movie':
                ImportLogger::getInstance()->setVendor( $vendorObj );
                $importer->addDataMapper( new londonDatabaseFilmsDataMapper( $vendorObj ) );
                $importer->run();
                ImportLogger::getInstance()->end();
                $this->dieWithLogMessage();
            break;

          case 'eating-drinking':
              ImportLogger::getInstance()->setVendor( $vendorObj );
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
            ImportLogger::getInstance()->end();
            $this->dieWithLogMessage();
            
            break;

          case 'eating-drinking-kids':
              ImportLogger::getInstance()->setVendor( $vendorObj );
            try
            {
              //Setup NY FTP @todo refactor FTPClient to not connect in constructor
              $ftpClientObj = new FTPClient( 'ftp.timeoutny.com', 'london', 'timeout', $vendorObj[ 'city' ] );
              $ftpClientObj->setSourcePath( '/NOKIA/' );
              $fileNameString = $ftpClient->fetchFile( 'tonykids_ed.xml' );

            }
            catch ( Exception $e )
            {
              echo 'Exception caught in chicago' . $options['city'] . ' ' . $options['type'] . ' import: ' . $e->getMessage();
            }
            ImportLogger::getInstance()->end();
            $this->dieWithLogMessage();
            break;

          case 'bars-clubs':
               ImportLogger::getInstance()->setVendor( $vendorObj );
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
            ImportLogger::getInstance()->end();
            $this->dieWithLogMessage();
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
            case 'poi-bc':
                echo "Downloading Chicago's BC Feed \n";
                $fileNameString = $ftpClientObj->fetchLatestFileByPattern( 'toc_bc.xml' );

                echo "Importing Chicago's BC \n";

                ImportLogger::getInstance()->setVendor( $vendorObj );
                $importer->addDataMapper( new ChicagoFeedBCMapper($vendorObj, $fileNameString) );
                $importer->run();
                ImportLogger::getInstance()->end();
                $this->dieWithLogMessage();

              break;
            case 'poi-ed':
                echo "Downloading Chicago's ED Feed \n";
                $fileNameString = $ftpClientObj->fetchLatestFileByPattern( 'toc_ed.xml' );

                echo "Importing Chicago's ED \n";

                ImportLogger::getInstance()->setVendor( $vendorObj );
                $importer->addDataMapper( new ChicagoFeedEDMapper($vendorObj, $fileNameString) );
                $importer->run();
                ImportLogger::getInstance()->end();
                $this->dieWithLogMessage();

              break;
          case 'poi':
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
          case 'event':
                echo "Downloading Chicago's Poi/Events Feed \n";
                $fileNameString = $ftpClientObj->fetchLatestFileByPattern( 'toc_leo.xml' );

                echo "Parsing Chicago's Poi/Events Feed \n";
                $xmlData = simplexml_load_file( $fileNameString );

                echo "Importing Chicago's Events \n";

                // @todo Wrap in try catch & change this to XSLT like new NY
                // We are to Run two Import on Events to Manage Memory!
                $eventsNode = $xmlData->xpath( '/body/event' );
                $totalCount = count($eventsNode);
                $splitAt = round( $totalCount / 2 );

                ImportLogger::getInstance()->setVendor( $vendorObj );
                $importer->addDataMapper( new ChicagoFeedEventMapper($vendorObj, $xmlData, null, $eventsNode, 0, $splitAt) );
                $importer->run();
                ImportLogger::getInstance()->end();

                // Reset Importer
                unset( $importer );
                $importer = new Importer(); // Create new Importer
                echo "Importing Chicago's Events - 2 \n";
                // Run the Second one
                ImportLogger::getInstance()->setVendor( $vendorObj );
                $importer->addDataMapper( new ChicagoFeedEventMapper($vendorObj, $xmlData, null, $eventsNode, $splitAt, $totalCount ) );
                $importer->run();
                ImportLogger::getInstance()->end();

                $this->dieWithLogMessage();

              break;

          case 'movie':
              ImportLogger::getInstance()->setVendor( $vendorObj );
              $importer->addDataMapper( new londonDatabaseFilmsDataMapper( $vendorObj ) );
              $importer->run();
              ImportLogger::getInstance()->end();
              $this->dieWithLogMessage();
          break;

          default : $this->dieDueToInvalidTypeSpecified();
        }
        break; //end chicago

      case 'singapore':

          // added here to ensure URL always return valid URL string
          if( !in_array( $options['type'], array('poi', 'event', 'movie') ) )
          {
              $this->dieDueToInvalidTypeSpecified();
              return;
          }
          // Get Vendor
        $vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('singapore', 'en-US');

        $singaporeURL['poi']    = 'http://www.timeoutsingapore.com/xmlapi/venues/?section=index&full=&key=ffab6a24c60f562ecf705130a36c1d1e';
        $singaporeURL['event']  = 'http://www.timeoutsingapore.com/xmlapi/events/?section=index&full=&key=ffab6a24c60f562ecf705130a36c1d1e';
        $singaporeURL['movie']  = 'http://www.timeoutsingapore.com/xmlapi/movies/?section=index&full&key=ffab6a24c60f562ecf705130a36c1d1e';
        // Get XML
        $dataSource = new singaporeDataSource( $options['type'], $singaporeURL[$options['type']] );
        $xml = $dataSource->getXML();

        // set vendor to logger
        ImportLogger::getInstance()->setVendor( $vendorObj );

        // Create Mapper class
        switch( $options['type'] )
        {

          case 'poi':
            $importer->addDataMapper( new singaporePoiMapper( $xml ) );
            break;
          case 'event':
            $importer->addDataMapper( new singaporeEventMapper( $xml ) );
            break;
          case 'movie':
            $importer->addDataMapper( new singaporeMovieMapper( $xml ) );
          break;
          default : $this->dieDueToInvalidTypeSpecified();
        }
        
        // Run Import
        $importer->run();
        ImportLogger::getInstance()->end();
        $this->dieWithLogMessage();

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
            case 'moscow_1':
            case 'moscow_2':
                    $this->importMoscow( $city, $options['type'] );
                break;
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

                if( $options['city'] !== 'russia' ) $this->dieWithLogMessage( 'FAILED IMPORT - INVALID CITY SPECIFIED' );

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

    case 'dubai':
        $vendor = Doctrine::getTable( 'Vendor' )->findOneByCityAndLanguage('dubai', 'en-US');

        switch( $options['type'] )
        {
            case 'bar':
                $feedUrl            = 'http://www.timeoutdubai.com/nokia/bars';
                $dataMapperClass    = 'UAEFeedBarsMapper';
                break;
            case 'restaurant':
                $feedUrl            = 'http://www.timeoutdubai.com/nokia/restaurants';
                $dataMapperClass    = 'UAEFeedRestaurantsMapper';
                break;
            case 'poi':
                $feedUrl            = 'http://www.timeoutdubai.com/nokia/latestevents';
                $dataMapperClass    = 'UAEFeedPoiMapper';
                $xslt               = file_get_contents( sfConfig::get( 'sf_data_dir' ).'/xslt/uae_pois.xml' );
                break;
            case 'event':
                $feedUrl            = 'http://www.timeoutdubai.com/nokia/latestevents';
                $dataMapperClass    = 'UAEFeedEventsMapper';
                $xslt               = file_get_contents( sfConfig::get( 'sf_data_dir' ).'/xslt/uae_events.xml' );
                break;
            case 'movie':
                $feedUrl            = 'http://www.timeoutdubai.com/customfeed/nokia/films';
                $dataMapperClass    = 'UAEFeedFilmsMapper';
                $xslt               = file_get_contents( sfConfig::get( 'sf_data_dir' ).'/xslt/uae_films.xml' );
                break;
            default : $this->dieDueToInvalidTypeSpecified();
        }

        if( isset($feedUrl) && isset($dataMapperClass) )
        {
            // Download the File
            $feedCurl           = new Curl( $feedUrl );
            $feedCurl->exec();

            $xmlDataFixer       = new xmlDataFixer( $feedCurl->getResponse() );

            ImportLogger::getInstance()->setVendor( $vendor );
            if( in_array( $options['type'], array('poi', 'event', 'movie') ) )
            {
                $importer->addDataMapper( new $dataMapperClass( $vendor,  $xmlDataFixer->getSimpleXMLUsingXSLT( $xslt ) ) );
            }else{
                $importer->addDataMapper( new $dataMapperClass( $vendor,  $xmlDataFixer->getSimpleXML() ) );
            }
            $importer->run();
            ImportLogger::getInstance()->end();
            $this->dieWithLogMessage();
        }

        break;
    case 'abu dhabi':
        $vendor = Doctrine::getTable( 'Vendor' )->findOneByCityAndLanguage('abu dhabi', 'en-US');

        switch( $options['type'] )
        {
            case 'bar':
                $feedUrl            = 'http://www.timeoutabudhabi.com/nokia/bars';
                $dataMapperClass    = 'UAEFeedBarsMapper';
                break;
            case 'restaurant':
                $feedUrl            = 'http://www.timeoutabudhabi.com/nokia/restaurants';
                $dataMapperClass    = 'UAEFeedRestaurantsMapper';
                break;
            case 'poi':
                $feedUrl            = 'http://www.timeoutabudhabi.com/nokia/latestevents';
                $dataMapperClass    = 'UAEFeedPoiMapper';
                $xslt               =file_get_contents( sfConfig::get( 'sf_data_dir' ).'/xslt/uae_pois.xml' );
                break;
            case 'event':
                $feedUrl            = 'http://www.timeoutabudhabi.com/nokia/latestevents';
                $dataMapperClass    = 'UAEFeedEventsMapper';

                $xslt               =file_get_contents( sfConfig::get( 'sf_data_dir' ).'/xslt/uae_events.xml' );
                break;
            case 'movie':
                $feedUrl            = 'http://www.timeoutabudhabi.com/customfeed/nokia/films';
                $dataMapperClass    = 'UAEFeedFilmsMapper';
                $xslt               =file_get_contents( sfConfig::get( 'sf_data_dir' ).'/xslt/uae_films.xml' );
                break;
            default : $this->dieDueToInvalidTypeSpecified();
        }

        if( isset($feedUrl) && isset($dataMapperClass) )
        {
            // Download the File
            $feedCurl           = new Curl( $feedUrl );
            $feedCurl->exec();

            $xmlDataFixer       = new xmlDataFixer( $feedCurl->getResponse() );

            ImportLogger::getInstance()->setVendor( $vendor );
            if( in_array( $options['type'], array('poi', 'event', 'movie') ) )
            {
                $importer->addDataMapper( new $dataMapperClass( $vendor,  $xmlDataFixer->getSimpleXMLUsingXSLT( $xslt ) ) );
            }else{
                $importer->addDataMapper( new $dataMapperClass( $vendor,  $xmlDataFixer->getSimpleXML() ) );
            }
            $importer->run();
            ImportLogger::getInstance()->end();
            $this->dieWithLogMessage();
        }

        break;

    // data entry imports
    case 'mumbai':
    case 'delhi':
    case 'bangalore':
    case 'beijing-data-entry':
    case 'pune':

        if( $options['city'] == 'beijing-data-entry' )
        {
             $options['city'] = 'beijing';
        }



        $dataEntryImportManager = new DataEntryImportManager( $options['city']  );

        echo "Using : " . $dataEntryImportManager->getImportDir();

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

                    $dns = sfConfig::get("app_beijing_dns");
                    $user = sfConfig::get("app_beijing_user");
                    $password = sfConfig::get("app_beijing_password");

                    $pdoDB = new PDO( $dns , $user , $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8") );

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

     case 'istanbul':

        $vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage( 'istanbul', 'tr' );

        switch( $options['type'] )
        {
          case 'poi':

            $feedUrl = "http://www.timeoutistanbul.com/content/n_xml/venues.xml";
            $mapperClass = "istanbulVenueMapper";

          break; //end Poi

          case 'event':

            $feedUrl = "http://www.timeoutistanbul.com/content/n_xml/events.xml";
            $mapperClass = "istanbulEventMapper";

          break; //end Event

          case 'movie':

            $feedUrl = "http://www.timeoutistanbul.com/content/n_xml/movies.xml";
            $mapperClass = "istanbulMovieMapper";

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
     break;

     case 'beijing_zh':
         
         $vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage( 'beijing_zh', 'zh-Hans' );

         switch( $options['type'] )
         {
             case 'poi':
                 $params = array( 'datasource' => array( 'classname' => 'Curl', 'url' => 'http://www.timeoutcn.com/Account/Login.aspx?ReturnUrl=/admin/n/london/Default.aspx', 'username' => 'tolondon' , 'password' => 'to3rjk&e*8dsfj9' ) );
                 ImportLogger::getInstance()->setVendor( $vendorObj );
                 $importer->addDataMapper( new beijingZHFeedVenueMapper( $vendorObj, $params ) );
                 $importer->run();
                 ImportLogger::getInstance()->end();
                 $this->dieWithLogMessage();
                 break;
             default : $this->dieDueToInvalidTypeSpecified();
         }

         break;

     case 'shanghai':
         $vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage( 'shanghai', 'zh-Hans' );

         switch( $options['type'] )
         {
             case 'movie':
                 $params = array( 'datasource' => array( 'classname' => 'FormScraper', 'url' => 'http://n.timeoutcn.com/Account/Login.aspx?ReturnUrl=/Admin/ExportTOLondon/MoviesData.aspx', 'username' => 'timeoutlondon' , 'password' => 'aas9384jewt-0tkfd' ) );
                 ImportLogger::getInstance()->setVendor( $vendorObj );
                 $importer->addDataMapper( new ShanghaiFeedMovieMapper( $vendorObj, $params ) );
                 $importer->run();
                 ImportLogger::getInstance()->end();
                 $this->dieWithLogMessage();
                 break;
             default : $this->dieDueToInvalidTypeSpecified();
         }
         
         break;

    default : $this->dieWithLogMessage( 'FAILED IMPORT - INVALID CITY SPECIFIED' );

    }//end switch
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



     /* Common Function */

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
            }

        }

        // Check if any files Exists
        if( count($fileListSorted) <= 0 )
        {
            $this->writeLogLine( "Failed to Extract All File Names From Sydney FTP Directory Listing. FILE NAME FORMAT MIGHT BE CHANGED" );
            return null;
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

    private function importMoscow( $city, $type )
    {
        $vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage( $city, 'ru' );

        $feedUrl = 'http://www.timeout.ru/london/places_msk.xml';
        $mapperClass = 'RussiaFeedPlacesMapper';

        echo 'Downloading Feed' . PHP_EOL;
        $feedObj = new Curl( $feedUrl );
        $feedObj->exec();
        $xml = simplexml_load_string( $feedObj->getResponse() );

        echo 'Starting Import' . PHP_EOL;
        // SPlit 2 and Import based on City
        $total = $xml->venue->count();
        $splitMiddle = (int)ceil( $total / 2 );

        $startPoint = ( $type == 'moscow_1' ) ? 0 : $splitMiddle;
        $endPoint = ( $type == 'moscow_1' ) ? $splitMiddle : $total;
        
        echo ' Total :' . $total . ' | Middle: '. $splitMiddle . ' | Start: ' . $startPoint . ' | End: ' . $endPoint . PHP_EOL;

        ImportLogger::getInstance()->setVendor( $vendorObj );
        $importer = new Importer( );
        $importer->addDataMapper( new $mapperClass( $xml, null, $city, $startPoint, $endPoint ) );
        $importer->run();
        ImportLogger::getInstance()->end();

        $this->dieWithLogMessage();
    }


}
