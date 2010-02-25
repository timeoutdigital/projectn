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
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'import';
    $this->briefDescription = 'Import data files from vendors';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
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
        
        //Setup NY FTP
        $ftpClientObj = new FTPClient( 'ftp.timeoutny.com', 'london', 'timeout', $vendorObj[ 'city' ] );
        $ftpClientObj->setSourcePath( '/NOKIA/' );


        switch( $options['type'] )
        {
          case 'poi-event-kids':
            try
            {
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
                $this->importNyEvents($vendorObj, $ftpClientObj);
            break;

          case 'movie':
                $importer->addDataMapper( new londonDatabaseFilmsDataMapper( $vendorObj, londonDatabaseFilmsDataMapper::NEW_YORK_REVIEW_TYPE_ID ) );
            break;

          case 'eating-drinking':
            try
            {
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
              $this->importNyBc($vendorObj, $ftpClientObj);
            }
            catch ( Exception $e )
            {
              echo 'Exception caught in chicago' . $options['city'] . ' ' . $options['type'] . ' import: ' . $e->getMessage();
            }
            break;


          case 'all':
              //Import all events
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
               $importer->addDataMapper( new londonDatabaseFilmsDataMapper( $vendorObj , londonDatabaseFilmsDataMapper::CHICAGO_REVIEW_TYPE_ID ) );
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

            /*$curlImporterObj = new curlImporter();
            $parametersArray = array( 'section' => 'index', 'thisweek' => '', 'key' => 'ffab6a24c60f562ecf705130a36c1d1e' );
            $curlImporterObj->pullXml ('http://www.timeoutsingapore.com/xmlapi/events/', '', $parametersArray, 'GET', true );
            $xmlObj = $curlImporterObj->getXml();

            $singaporeImportObj = new singaporeImport( $xmlObj, $vendorObj, $curlImporterObj );
            $singaporeImportObj->insertCategoriesPoisEvents();*/

            //http://www.timeoutsingapore.com/xmlapi/events/?section=index&full&key=ffab6a24c60f562ecf705130a36c1d1e

            //http://www.timeoutsingapore.com/xmlapi/venues/?section=index&full&key=ffab6a24c60f562ecf705130a36c1d1e

            //http://www.timeoutsingapore.com/xmlapi/movies/?section=index&full&key=ffab6a24c60f562ecf705130a36c1d1e


            $logger = new logImport($vendorObj, 'poi' );

            $curlImporterObj = new curlImporter();
            $parametersArray = array( 'section' => 'index', 'full' => '', 'key' => 'ffab6a24c60f562ecf705130a36c1d1e' );
            $curlImporterObj->pullXml ('http://www.timeoutsingapore.com/xmlapi/venues/', '', $parametersArray, 'GET', true );
            $xmlObj = $curlImporterObj->getXml();

            $this->object = new singaporeImport( $vendorObj, $curlImporterObj, $logger );

            $this->object->insertPois( $xmlObj );

            $logger->save();

            $logger = new logImport($vendorObj, 'event' );

            $curlImporterObj = new curlImporter();
            $parametersArray = array( 'section' => 'index', 'full' => '', 'key' => 'ffab6a24c60f562ecf705130a36c1d1e' );
            $curlImporterObj->pullXml ('http://www.timeoutsingapore.com/xmlapi/events/', '', $parametersArray, 'GET', true );
            $xmlObj = $curlImporterObj->getXml();

            $this->object = new singaporeImport( $vendorObj, $curlImporterObj, $logger, 'http://www.timeoutsingapore.com/xmlapi/xml_detail/?venue={venueId}&key=ffab6a24c60f562ecf705130a36c1d1e' );
            $this->object->insertEvents( $xmlObj );

            $logger->save();
            
            break;

          case 'film':
            $logger = new logImport($vendorObj, 'movie' );
          
            $curlImporterObj = new curlImporter();
            $parametersArray = array( 'section' => 'index', 'full' => '', 'key' => 'ffab6a24c60f562ecf705130a36c1d1e' );
            $curlImporterObj->pullXml ('http://www.timeoutsingapore.com/xmlapi/movies/', '', $parametersArray, 'GET', true );
            $xmlObj = $curlImporterObj->getXml();

            $this->object = new singaporeImport( $vendorObj, $curlImporterObj, $logger );
            $this->object->insertMovies( $xmlObj );

            $logger->save();
            
          break;

          case 'eating-drinking':
          break;
        }

        break; //end singapore

      case 'lisbon':

        $vendorObj = $this->getVendorByCityAndLanguage('lisbon', 'pt');
        $feedObj     = new curlImporter();
        $url         = 'http://www.timeout.pt/';
        $parameters  = array( 'from' => '2010-02-18', 'to' => '2010-02-23' );
        $method      = 'POST';
        $loggerObj =   new logImport( $vendorObj );
        
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

      case 'london':
        $vendor = $this->getVendorByCityAndLanguage( 'london', 'en-GB' );
        $loggerObj = new logImport( $vendor );
        switch( $options['type'] )
        {
          case 'poi-event':
            $connection = $databaseManager->getDatabase( 'searchlight_london' )->getConnection();
            $loggerObj->setType( 'poi' );
            $importer->addLogger( $loggerObj );
            $importer->addDataMapper( new LondonDatabaseEventsAndVenuesMapper() );
            $importer->addDataMapper( new LondonAPIBarsAndPubsMapper() );
            $importer->addDataMapper( new LondonAPIRestaurantsMapper() );
            $importer->addDataMapper( new LondonAPICinemasMapper() );
            break;

          case 'movie':
            $loggerObj->setType( 'movie' );
            $importer->addLogger( $loggerObj );
            $importer->addDataMapper( new londonDatabaseFilmsDataMapper($vendor, londonDatabaseFilmsDataMapper::LONDON_REVIEW_TYPE_ID) );
          break;
        }
        break; //end lisbon


    case 'uae':
        
    
        switch( $options['type'] )
        {
          case 'poi': //$this->importDubaiBars($vendorObj);
                      $this->importDubaiRestaurants();

            break;

          case 'poi-event': $this->importDubaiEvents();

            break;


        case 'movies': $this->importUaeMovies();

            break;
        }



        break; // end dubai




    }//end switch

    $importer->run();

     //Get the total import time
     //echo "Total time: ". $importObj->poiLoggerObj ->finalTime . "\n";
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
   * @param Vendor $vendoObj
   * @param FTPClient $ftpClientObj
   *
   */
  private function importChicagoEvents($vendorObj, $ftpClientObj)
  {
      try
        {
          $fileNameString = $ftpClientObj->fetchLatestFileByPattern( 'toc_leo.xml' );

          $processXmlObj = new processNyXml( $fileNameString );
          $processXmlObj->setEvents('/body/event')->setVenues('/body/address');
          $nyImportMoviesObj = new importNy($processXmlObj,$vendorObj);
          $nyImportMoviesObj->insertEventCategoriesAndEventsAndVenues();

        }
        catch ( Exception $e )
        {
          echo 'Exception caught in chicago' . $options['city'] . ' ' . $options['type'] . ' import: ' . $e->getMessage();
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
      $fileNameString = $ftpClientObj->fetchLatestFileByPattern( 'xffd_TOChicago_[0-9]+.xml' );

      $processXmlObj = new processNyMoviesXml( $fileNameString );
      $processXmlObj->setMovies( '/xffd/movies/movie' );
      $processXmlObj->setPoi( '/xffd/theaters/theater' );
      $processXmlObj->setOccurances( '/xffd/showTimes/showTime' );

      $nyImportMoviesObj = new importNyMovies( $processXmlObj, $vendorObj) ;
      $nyImportMoviesObj->importMovies();

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
            $fileNameString = $ftpClientObj->fetchFile( 'toc_bc.xml' );
            $processXmlObj = new processNyBcXml( $fileNameString );


            $importObj = new chicagoImportBcEd($processXmlObj, $vendorObj);
            $importObj->import();
                     
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

            $fileNameString = $ftpClientObj->fetchFile( 'toc_ed.xml' );
            $processXmlObj = new processNyBcXml( $fileNameString );
            //$processXmlObj = new processNyBcXml( '/var/workspace/projectn/import/chicago/toc_ed.xml' );

            $importObj = new chicagoImportBcEd($processXmlObj, $vendorObj);
            $importObj->import();

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
          echo "Starting download \n\n";
          $fileNameString = $ftpClientObj->fetchLatestFileByPattern( 'tony_leo.xml' );

          $processXmlObj = new processNyXml( $fileNameString );
          //$processXmlObj = new processNyXml( '/var/workspace/projectn/import/ny/tony_leo.xml' );
          echo "XML Parsed \n\n";
          $processXmlObj->setEvents('/body/event')->setVenues('/body/address');
          $nyImportObj = new importNyChicagoEvents($processXmlObj,$vendorObj);
          $nyImportObj->insertEventCategoriesAndEventsAndVenues();
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
          $fileNameString = $ftpClientObj->fetchLatestFileByPattern( 'xffd_TONewYork_[0-9]+.xml' );

          $processXmlObj = new processNyMoviesXml( $fileNameString );
          $processXmlObj->setMovies('/xffd/movies/movie');
          $processXmlObj->setPoi('/xffd/theaters/theater');
          $processXmlObj->setOccurances('/xffd/showTimes/showTime');

          $nyImportMoviesObj = new importNyMovies($processXmlObj,$vendorObj);
          $nyImportMoviesObj->importMovies();
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
            //Download and process XML
            $fileNameString = $ftpClientObj->fetchFile( 'tony_bc.xml' );
            $processXmlObj = new processNyBcXml( $fileNameString );

            //Import the bars
            $importBcEd = new nyImportBcEd($processXmlObj, $vendorObj);
            $importBcEd->import();
        }
        catch ( Exception $e )
        {
          echo 'Exception caught in NY import: ' . $e->getMessage();
        }
         
     }


     private function importNyEd($vendorObj, $ftpClientObj)
     {
        try
        {

            //Download and process XML
           // $fileNameString = $ftpClientObj->fetchFile( 'tony_ed.xml' );
            $fileNameString = "/var/workspace/projectn/import/ny/tony_ed.xml";
            echo 'processing';
            $processXmlObj = new processNyBcXml( $fileNameString );
            echo "\n\n Importing \n\n";
            //Import the bars
            $importBcEd = new nyImportBcEd($processXmlObj, $vendorObj);
            $importBcEd->import();
        }
        catch ( Exception $e )
        {
          echo 'Exception caught in NY import: ' . $e->__toString();
        }

     }



   /***************************************************************************
   *
   *    Dubai
   *
   * *************************************************************************/
    private function importDubaiBars($vendorObj)
     {
        try
        {
            $feed = new Curl('http://www.timeoutdubai.com/nokia/bars');
            
            $xmlObj = new ValidateUaeXmlFeed($feed->getResponse());
            $xmlFeedObj = $xmlObj->getXmlFeed();
           
            $importDubaiBars = new ImportUaeBars($xmlFeedObj, $vendorObj);
            $importDubaiBars->importPois();
        }
        catch ( Exception $e )
        {
          echo 'Exception caught in Dubai Bars import: ' . $e->getMessage();
        }

     }

     private function importDubaiRestaurants($vendorObj)
     {
        try
        {
            $feed = new Curl('http://www.timeoutdubai.com/nokia/restaurants');

            $xmlObj = new ValidateUaeXmlFeed($feed->getResponse());
            $xmlFeedObj = $xmlObj->getXmlFeed();

            $importDubaiRestaurants = new ImportUaeRestaurants($xmlFeedObj, $vendorObj);
            $importDubaiRestaurants->importPois();
        }
        catch ( Exception $e )
        {
          echo 'Exception caught in Dubai Bars import: ' . $e->getMessage();
        }

     }


      private function importDubaiEvents($vendorObj)
     {
        try
        {
            $feed = new Curl('http://www.timeoutdubai.com/nokia/latestevents');
            $feed->exec();
            $xmlObj = new ValidateUaeXmlFeed($feed->getResponse());
            $xmlFeedObj = $xmlObj->getXmlFeed();


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
}
