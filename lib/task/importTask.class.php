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

          $this->newStyleImport( 'singapore', 'en-US', $options, $databaseManager, $importer );
          
        break; //end singapore

      case 'lisbon':

        $vendorObj    = Doctrine::getTable('Vendor')->getVendorByCityAndLanguage('lisbon', 'pt');

        switch( $options['type'] )
        {
          case 'poi':

            $curlObj = new Curl( 'http://www.timeout.pt/xmlvenues.asp' );
            $curlObj->exec();

            new FeedArchiver( $vendorObj, $curlObj->getResponse(), 'poi' );

            $dataFixer = new xmlDataFixer( $curlObj->getResponse() );
            $dataFixer->removeHtmlEntiryEncoding();

            ImportLogger::getInstance()->setVendor( $vendorObj );
            $importer->addDataMapper( new LisbonFeedVenuesMapper( $dataFixer->getSimpleXML() ) );
            $importer->run();
            ImportLogger::getInstance()->end();
            $this->dieWithLogMessage();

          break;

          case 'event':

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

                    echo "Getting Lisbon Events for Period: " . $parameters[ 'from' ] . "-" . $parameters[ 'to' ] . PHP_EOL;

                    // -- [start] CURL -- //

                    $curlObj = new Curl( 'http://www.timeout.pt/xmllist.asp', $parameters );
                    $curlObj->exec();

                    new FeedArchiver( $vendorObj, $curlObj->getResponse(), 'event_' . $parameters[ 'from' ] . '_to_' . $parameters[ 'to' ] );

                    $dataFixer = new xmlDataFixer( $curlObj->getResponse() );
                    $dataFixer->removeHtmlEntiryEncoding();

                    // -- [end] CURL -- //

                    // Add segment to array
                    $eventDataSimpleXMLSegmentsArray[] = $dataFixer->getSimpleXML();

                    // Move start date ahead one day from last end date
                    $startDate = strtotime( "+".( $daysAhead +1 )." day", $startDate );
                }
                catch ( Exception $e )
                {
                    // Add error
                    ImportLogger::getInstance()->addError( $e );

                    // Stop while loop.
                    break;
                }
            }

            echo "Running Lisbon Mappers" . PHP_EOL;

            $concatenatedFeed = XmlConcatenator::concatXML( $eventDataSimpleXMLSegmentsArray, 'geral' );

            $importer->addDataMapper( new LisbonFeedListingsMapper( $concatenatedFeed ) );

            $importer->run();
            ImportLogger::getInstance()->end();
            $this->dieWithLogMessage();

          break;

          case 'movie':

            $curlObj = new Curl( 'http://www.timeout.pt/xmlfilms.asp' );
            $curlObj->exec();

            new FeedArchiver( $vendorObj, $curlObj->getResponse(), 'poi' );

            $dataFixer = new xmlDataFixer( $curlObj->getResponse() );
            $dataFixer->removeHtmlEntiryEncoding();

            ImportLogger::getInstance()->setVendor( $vendorObj );
            $importer->addDataMapper( new LisbonFeedMoviesMapper( $dataFixer->getSimpleXML() ) );
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
      case 'melbourne':

        $this->newStyleImport( $options['city'], 'en-AU', $options, $databaseManager, $importer );

     break; //end Australia


    case 'kuala lumpur':

        $this->newStyleImport( $options['city'], 'en-MY', $options, $databaseManager, $importer );

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

   case 'bucharest':

       $this->newStyleImport( 'bucharest', 'ro', $options, $databaseManager, $importer );
       
     break; // End bucharest

    case 'beirut':

        $this->newStyleImport( 'beirut', 'en-US', $options, $databaseManager, $importer );
        
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
