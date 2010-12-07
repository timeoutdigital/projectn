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
        // Check config file has this type configuration
        if( !isset( $this->config['import'][$type] ) )
        {
            $this->dieDueToInvalidTypeSpecified();
        }
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

        $daysAhead    = 7; //lisbon caps the request at max 9 days
        $url          = 'http://www.timeout.pt/';
        $parameters   = array(
            'from' => date( 'Y-m-d' ),
            'to' => date( 'Y-m-d', strtotime( "+$daysAhead day" ) )
        );

        switch( $options['type'] )
        {
          case 'poi':

            $url .= 'xmlvenues.asp';
            $xmlData = $this->getLisbonSimpleXML( $vendorObj, $url, $parameters, 'POST', 'poi' );
            
            ImportLogger::getInstance()->setVendor( $vendorObj );
            $importer->addDataMapper( new LisbonFeedVenuesMapper( $xmlData ) );
            $importer->run();
            ImportLogger::getInstance()->end();
            $this->dieWithLogMessage();

          break;

          case 'event':

            $url .= 'xmllist.asp';
            ImportLogger::getInstance()->setVendor( $vendorObj );

            $startDate = time();
            $daysAhead = 7; //lisbon caps the request at max 9 days

            $eventDataSimpleXMLSegmentsArray = array();

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

                echo "Getting Lisbon Events for Period: " . $parameters[ 'from' ] . " to " . $parameters[ 'to' ] . PHP_EOL;
                
                $xmlData = $this->getLisbonSimpleXML($vendorObj, $url, $parameters, 'GET', 'event_' . $parameters[ 'from' ] . '_to_' . $parameters[ 'to' ]);

                // add XML data to array for XmlConcatenator
                $eventDataSimpleXMLSegmentsArray[] = $xmlData;
              }
              catch ( Exception $e )
              {
                ImportLogger::getInstance()->addError( $e );
              }

            }

            echo "Running Lisbon Mappers" . PHP_EOL;

            $concatenatedFeed = XmlConcatenator::concatXML( $eventDataSimpleXMLSegmentsArray, '/geral/listings' );
            
            $importer->addDataMapper( new LisbonFeedListingsMapper( $concatenatedFeed ) );

            $importer->run();
            ImportLogger::getInstance()->end();
            $this->dieWithLogMessage();

          break;

          case 'movie':

            $url .= 'xmlfilms.asp';
            $xmlData = $this->getLisbonSimpleXML( $vendorObj, $url, $parameters, 'POST', 'movie' );
            
            ImportLogger::getInstance()->setVendor( $vendorObj );
            $importer->addDataMapper( new LisbonFeedMoviesMapper( $xmlData ) );
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

        $this->newStyleImport( $city, 'ru', $options, $databaseManager, $importer );

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

        $this->newStyleImport( 'barcelona', 'ca', $options, $databaseManager, $importer );

    break; // end Barcelona

    case 'dubai':

        $this->newStyleImport( 'dubai', 'en-US', $options, $databaseManager, $importer );

    break;
    
    case 'abu dhabi':

        $this->newStyleImport( 'abu dhabi', 'en-US', $options, $databaseManager, $importer );

        break;
    case 'bahrain':

        $this->newStyleImport( 'bahrain', 'en-US', $options, $databaseManager, $importer );

        break;
    case 'doha':
        
        $this->newStyleImport( 'doha', 'en-US', $options, $databaseManager, $importer );

    break;

    // data entry imports
    case 'mumbai':
    case 'delhi':
    case 'bangalore':
    case 'beijing-data-entry':
    case 'pune':
    case 'shanghai_en':

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

    case 'hong kong':

        $vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage( 'hong kong', 'en-HK' );
        
        $dataMapper = null;
        $params = array( 'datasource' => array( 'classname' => 'Curl', 'url' => '' ) );
        $params['type'] = $options['type']; // Requierd by Feed Archiver
        
        switch( $options['type'] )
        {    
            case 'poi':
                    $dataMapper = 'HongKongFeedVenuesMapper';
                    $params['datasource']['url']    = 'http://www.timeout.com.hk/rss/venues/';
                break;
            case 'movie':
                    $dataMapper = 'HongKongFeedMoviesMapper';
                    $params['datasource']['url']    = 'http://www.timeout.com.hk/rss/movies/';
                break;
            case 'event':
                    $dataMapper = 'HongKongFeedEventsMapper';
                    $params['datasource']['url']    = 'http://www.timeout.com.hk/rss/events/';
                break;
            default : $this->dieDueToInvalidTypeSpecified();
                break;
        }
        if(!$dataMapper || !$vendorObj )
        {
            throw new Exception('HongKong:: Invalid value!');
        }

        ImportLogger::getInstance()->setVendor( $vendorObj );
        $importer->addDataMapper( new $dataMapper( $vendorObj, $params ) );
        $importer->run();
        ImportLogger::getInstance()->end();

        $this->dieWithLogMessage();
        
        break;

     case 'istanbul':

        $this->newStyleImport( 'istanbul', 'tr', $options, $databaseManager, $importer );

     break;

 case 'istanbul_en':

     $this->newStyleImport( 'istanbul_en', 'en-US', $options, $databaseManager, $importer );

     break;

     case 'beijing_zh':
         
         $this->newStyleImport( 'beijing_zh', 'zh-Hans', $options, $databaseManager, $importer );

         break;

     case 'shanghai_zh':
         
         $this->newStyleImport( 'shanghai_zh', 'zh-Hans', $options, $databaseManager, $importer );
         
         break;

    case 'amsterdam_data_entry':

        $dataEntryImportManager = new DataEntryImportManager( 'amsterdam' );

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

<<<<<<< HEAD
   case 'bucharest':

        $vendorObj = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage( 'bucharest', 'ro' );

        switch( $options['type'] )
        {
          case 'poi':

            $mapperClass = "bucharestVenueMapper";

          break; //end Poi

          default : $this->dieDueToInvalidTypeSpecified();
        }

        $xml = simplexml_load_file( '/home/ralph/venues.xml' );

        ImportLogger::getInstance()->setVendor( $vendorObj );
        $importer->addDataMapper( new $mapperClass( $xml ) );
        $importer->run();
        ImportLogger::getInstance()->end();
        $this->dieWithLogMessage();
     break; // End bucharest

=======
    case 'beirut':

        $this->newStyleImport( 'beirut', 'en-US', $options, $databaseManager, $importer );
        
        break;
>>>>>>> 2ac56120d91332777fc18b0bbfe96a83106aebc1

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

    /**
     * Download lisbone Feed and clean before parshing as XML
     * @param Vendir $vendorObj
     * @param string $url
     * @param array $parameters
     * @param string $method
     * @param string $type
     * @return SimpleXMLElement
     */
    private function getLisbonSimpleXML( $vendorObj, $url, $parameters, $method = 'POST', $type='lisbon' )
    {
        $curl = new Curl( $url, $parameters, $method );
        $curl->exec();
        new FeedArchiver( $vendorObj, $curl->getResponse(), $type );

        // Clean the Feed, Sometime XML feed starts with Empty or new line and it causing simplexml load to throw exception
        $rawXmlData = stringTransform::mb_trim( $curl->getResponse() );

        return simplexml_load_string( $rawXmlData );
    }

}
