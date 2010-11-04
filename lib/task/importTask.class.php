<?php

class importTask extends sfBaseTask
{

    protected $config;

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
      new sfCommandOption('configFolder', null, sfCommandOption::PARAMETER_OPTIONAL, 'The config file to be used (if other than default)'),
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'import';
    $this->briefDescription = 'Import data files from vendors';
    $this->detailedDescription = '';
  }

  public function newStyleImport( $city, $language, $options, $databaseManager, $importer )
    {
      // London DB Switch
      if( $city == 'london')
      {
          $databaseManager->getDatabase('searchlight_london')->getConnection(); // Set sfDatabase
      }
        $vendor = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage( $city, $language );

        $type = $this->options['type'];
        $mapperClassName = $this->config['import'][$type]['class']['name'];

        $constructorParams = array();
        if( isset( $this->config['import'][$type]['class']['params'] ) )
        {
            $constructorParams = $this->config['import'][$type]['class']['params'];
        }

        ImportLogger::getInstance()->setVendor($vendor);
        $importer->addDataMapper( new $mapperClassName( $vendor, $constructorParams ) );
        $importer->run();
        ImportLogger::getInstance()->end();
        $this->dieWithLogMessage( '', true );
        
    }

  protected function execute($arguments = array(), $options = array())
  {

    $this->options = $options;

    $this->writeLogLine( 'start import for ' . $options['city'] . ' (type: ' . $options['type'] . ', environment: ' . $options['env'] . ')' );

    //Load Config
    if ( $this->options[ 'configFolder' ] === null )
    {
        $this->options[ 'configFolder' ] = sfConfig::get('sf_config_dir') . DIRECTORY_SEPARATOR . 'projectn';
    }

    $this->config = sfYaml::load( $this->options['configFolder'] . DIRECTORY_SEPARATOR . str_replace( ' ', '_', $this->options['city'] ) . '.yml' );
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

          $this->newStyleImport( 'ny', 'en-US', $options, $databaseManager, $importer );

        break; // end ny

      case 'chicago':

          $this->newStyleImport( 'chicago', 'en-US', $options, $databaseManager, $importer );

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
            new FeedArchiver( $vendorObj, $feedObj->getResponse(), 'poi' );

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
                new FeedArchiver( $vendorObj, $feedObj->getResponse(), 'event_' . $parameters[ 'from' ] . '_to_' . $parameters[ 'to' ] );

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
            new FeedArchiver( $vendorObj, $feedObj->getResponse(), 'movie' );

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
        new FeedArchiver( $vendorObj, $feedObj->getResponse(), $options['type'] );
        $xml = simplexml_load_string( $feedObj->getResponse() );

        ImportLogger::getInstance()->setVendor( $vendorObj );
        $importer->addDataMapper( new $mapperClass( $xml, null, $city ) );
        $importer->run();
        ImportLogger::getInstance()->end();

        $this->dieWithLogMessage();

      break; //End Russian Cities


      case 'london':

          $this->newStyleImport( 'london', 'en-GB', $options, $databaseManager, $importer );

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
        new FeedArchiver( $vendor, file_get_contents( sfConfig::get( 'sf_root_dir' ) . "/import/{$vendor['city']}/{$targetFileName}" ), $options['type'] );

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
        new FeedArchiver( $vendor, $feedObj->getResponse(), $options['type'] );
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
        new FeedArchiver( $vendorObj, $feedObj->getResponse(), $options['type'] );
        $xml = simplexml_load_string( $feedObj->getResponse() );

        ImportLogger::getInstance()->setVendor( $vendorObj );
        $importer->addDataMapper( new $mapperClass( $xml ) );
        $importer->run();
        ImportLogger::getInstance()->end();
        $this->dieWithLogMessage();

    break; // end Barcelona

    case 'dubai':

        $this->newStyleImport( 'dubai', 'en-US', $options, $databaseManager, $importer );

    break;
    
    case 'abu dhabi':

        $this->newStyleImport( 'abu dhabi', 'en-US', $options, $databaseManager, $importer );

        break;
    case 'bahrain':
        $vendor = Doctrine::getTable( 'Vendor' )->findOneByCityAndLanguage('bahrain', 'en-US');

        switch( $options['type'] )
        {
            case 'bar':
                $feedUrl            = 'http://www.timeoutbahrain.com/nokia/bars';
                $dataMapperClass    = 'UAEFeedBarsMapper';
                break;
            case 'restaurant':
                $feedUrl            = 'http://www.timeoutbahrain.com/nokia/restaurants';
                $dataMapperClass    = 'UAEFeedRestaurantsMapper';
                break;
            case 'poi':
                $feedUrl            = 'http://www.timeoutbahrain.com/nokia/latestevents';
                $dataMapperClass    = 'UAEFeedPoiMapper';
                $xslt               = file_get_contents( sfConfig::get( 'sf_data_dir' ).'/xslt/uae_pois.xml' );
                break;
            case 'event':
                $feedUrl            = 'http://www.timeoutbahrain.com/nokia/latestevents';
                $dataMapperClass    = 'UAEFeedEventsMapper';
                $xslt               = file_get_contents( sfConfig::get( 'sf_data_dir' ).'/xslt/uae_events.xml' );
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
    case 'doha':
        $vendor = Doctrine::getTable( 'Vendor' )->findOneByCityAndLanguage('doha', 'en-US');

        switch( $options['type'] )
        {
            case 'bar':
                $feedUrl            = 'http://www.timeoutdoha.com/nokia/bars';
                $dataMapperClass    = 'UAEFeedBarsMapper';
                break;
            case 'restaurant':
                $feedUrl            = 'http://www.timeoutdoha.com/nokia/restaurants';
                $dataMapperClass    = 'UAEFeedRestaurantsMapper';
                break;
            case 'poi':
                $feedUrl            = 'http://www.timeoutdoha.com/nokia/latestevents';
                $dataMapperClass    = 'UAEFeedPoiMapper';
                $xslt               = file_get_contents( sfConfig::get( 'sf_data_dir' ).'/xslt/uae_pois.xml' );
                break;
            case 'event':
                $feedUrl            = 'http://www.timeoutdoha.com/nokia/latestevents';
                $dataMapperClass    = 'UAEFeedEventsMapper';
                $xslt               = file_get_contents( sfConfig::get( 'sf_data_dir' ).'/xslt/uae_events.xml' );
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
        new FeedArchiver( $vendorObj, $feedObj->getResponse(), $options['type'] );
        
        $xml = simplexml_load_string( $feedObj->getResponse() );

        ImportLogger::getInstance()->setVendor( $vendorObj );
        $importer->addDataMapper( new $mapperClass( $xml ) );
        $importer->run();
        ImportLogger::getInstance()->end();
        $this->dieWithLogMessage();
     break;

    default : $this->dieWithLogMessage( 'FAILED IMPORT - INVALID CITY SPECIFIED' );

    }//end switch
  }

  /* Common Function */
  private function dieDueToInvalidTypeSpecified()
  {
      $this->dieWithLogMessage( 'FAILED IMPORT - INVALID TYPE SPECIFIED' );
  }

  private function dieWithLogMessage( $custom_message = "", $survive = false )
  {
    if( $custom_message ) $this->writeLogLine( $custom_message );

    $message = "";
    $message.= 'end import for ' . $this->options['city'];
    $message.= ' (type: ' . $this->options['type'] . ', environment: ' . $this->options['env'] . ')';
    $message.= ' -- Peak memory used: ' . stringTransform::byteToHumanReadable( memory_get_peak_usage( true ) );
    $this->writeLogLine( $message );

    echo PHP_EOL;
    if( !$survive ) die;
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

        new FeedArchiver( $vendorObj, $feedObj->getResponse(), $type );

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
