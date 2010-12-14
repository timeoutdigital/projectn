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

        $this->newStyleImport( 'lisbon', 'pt', $options, $databaseManager, $importer );
          
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

        $this->newStyleImport( 'hong kong', 'en-HK', $options, $databaseManager, $importer );

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
}
