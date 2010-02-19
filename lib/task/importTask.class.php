<?php

class importTask extends sfBaseTask
{

  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('city', null, sfCommandOption::PARAMETER_REQUIRED, 'The city to import'),
      new sfCommandOption('type', null, sfCommandOption::PARAMETER_REQUIRED, 'The type to import', 'poi-event'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
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



    //Select the task
    switch( $options['city'] )
    {
      case 'ny':

        //Set vendor and logger
        $vendorObj = $this->getVendorByCityAndLanguage('ny', 'en-US');
        $loggerObj = new logImport($vendorObj);

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
                $this->importNyEvents($vendoObj, $ftpClientObj);
            break;

          case 'movie':
                $importer = new Importer();
                $importer->addDataMapper( new londonDatabaseFilmsDataMapper( $vendorObj ) );
                $importer->run();
            break;

          case 'eating-drinking':
            try
            {
              $this->importNyEd($vendorObj, $ftpClientObj, $loggerObj);
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
              $this->importNyBc($vendorObj, $ftpClientObj, $loggerObj);
            }
            catch ( Exception $e )
            {
              echo 'Exception caught in chicago' . $options[
'city'] . ' ' . $options['type'] . ' import: ' . $e->getMessage();
            }
            break;


          case 'all':
              //Import all events
              $this->importNyEvents($vendorObj, $ftpClientObj);
              $this->importNyMovies($vendorObj, $ftpClientObj);

        }
        break; // end ny

      case 'chicago':

        $vendorObj = $this->getVendorByCityAndLanguage('chicago', 'en-US');
        $ftpClientObj = new FTPClient( 'ftp.timeoutchicago.com', 'timeout', 'y6fv2LS8', $vendorObj[ 'city' ] );
        $loggerObj = new logImport($vendorObj);

        switch( $options['type'] )
        {
          case 'poi-event':
              $this->importChicagoEvents($vendorObj, $ftpClientObj);
            break;

          case 'movie':
               $importer = new Importer();
               $importer->addDataMapper( new londonDatabaseFilmsDataMapper( $vendorObj ) );
               $importer->run();
          break;

          case 'eating-drinking':
            try
            {
              $importObj = $this->importChicagoEd($vendorObj, $ftpClientObj, $loggerObj);
            }
            catch ( Exception $e )
            {
              echo 'Exception caught in chicago' . $options['city'] . ' ' . $options['type'] . ' import: ' . $e->getMessage();
            }
           break;
          case 'bars-clubs':
            try
            {
              $importObj = $this->importChicagoBc($vendorObj, $ftpClientObj, $loggerObj);
            }
            catch ( Exception $e )
            {
              echo 'Exception caught in chicago' . $options['city'] . ' ' . $options['type'] . ' import: ' . $e->getMessage();
            }
           break;


           case 'all':
               //import events
               $this->importChicagoEvents($vendorObj, $ftpClientObj);

               //import movies
               $this->importChicagoMovies($vendorObj, $ftpClientObj);
               
           break;


        }
        break; //end chicago

      case 'lisbon':

        $vendorObj = $this->getVendorByCityAndLanguage('lisbon', 'pt');
        $importer    = new Importer();
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

        $importer->run();
        break; //end lisbon

      case 'singapore':
        $vendorObj = $this->getVendorByCityAndLanguage('singapore', 'en-US');
        $logger = new logImport($vendorObj );

        //must be set for price range function
        //@todo get get this info out of vendor?!
        setlocale(LC_MONETARY, 'en_US.UTF-8');

        switch( $options['type'] )
        {
          case 'poi-event':

            /*$curlImporterObj = new curlImporter();
            $parametersArray = array( 'section' => 'index', 'thisweek' => '', 'key' => 'ffab6a24c60f562ecf705130a36c1d1e' );
            $curlImporterObj->pullXml ('http://www.timeoutsingapore.com/xmlapi/events/', '', $parametersArray );
            $xmlObj = $curlImporterObj->getXml();

            $singaporeImportObj = new singaporeImport( $xmlObj, $vendorObj, $curlImporterObj );
            $singaporeImportObj->insertCategoriesPoisEvents();*/

            //http://www.timeoutsingapore.com/xmlapi/events/?section=index&full&key=ffab6a24c60f562ecf705130a36c1d1e

            //http://www.timeoutsingapore.com/xmlapi/venues/?section=index&full&key=ffab6a24c60f562ecf705130a36c1d1e

            //http://www.timeoutsingapore.com/xmlapi/movies/?section=index&full&key=ffab6a24c60f562ecf705130a36c1d1e



            $logger->setType( 'poi');

            $curlImporterObj = new curlImporter();
            $parametersArray = array( 'section' => 'index', 'full' => '', 'key' => 'ffab6a24c60f562ecf705130a36c1d1e' );
            $curlImporterObj->pullXml ('http://www.timeoutsingapore.com/xmlapi/venues/', '', $parametersArray );
            $xmlObj = $curlImporterObj->getXml();

            $this->object = new singaporeImport( $vendorObj, $curlImporterObj, $logger );

            $this->object->insertPois( $xmlObj );

            $logger->setType( 'event');

            $curlImporterObj = new curlImporter();
            $parametersArray = array( 'section' => 'index', 'full' => '', 'key' => 'ffab6a24c60f562ecf705130a36c1d1e' );
            $curlImporterObj->pullXml ('http://www.timeoutsingapore.com/xmlapi/events/', '', $parametersArray );
            $xmlObj = $curlImporterObj->getXml();

            $this->object = new singaporeImport( $vendorObj, $curlImporterObj, $logger );
            $this->object->insertEvents( $xmlObj );
            
            break;

          case 'film':
            $logger->setType( 'movie');

            $curlImporterObj = new curlImporter();
            $parametersArray = array( 'section' => 'index', 'full' => '', 'key' => 'ffab6a24c60f562ecf705130a36c1d1e' );
            $curlImporterObj->pullXml ('http://www.timeoutsingapore.com/xmlapi/movies/', '', $parametersArray );
            $xmlObj = $curlImporterObj->getXml();

            $this->object = new singaporeImport( $vendorObj, $curlImporterObj, $logger );
            $this->object->insertMovies( $xmlObj );
            
          break;

          case 'eating-drinking':
          break;
        }

        $logger->save();

        break; //end lisbon

      case 'london':
      	$connection = $databaseManager->getDatabase( 'searchlight_london' )->getConnection();

        switch( $options['type'] )
        {
          case 'poi-event':
            $london = new LondonImporter( );
            $london->run( );
            $importer = new Importer();
            $importer->addDataMapper( new LondonAPICinemasMapper() );
            $importer->addDataMapper( new LondonAPIBarsAndPubsMapper() );
            $importer->addDataMapper( new LondonAPIRestaurantsMapper() );
            $importer->run();
            break;

          case 'movie':
            $importer = new Importer();
            $importer->addDataMapper( new LondonAPIFilmsMapper() );
            $importer->run();
          break;
        }
        break; //end lisbon


    case 'dubai':
        $vendorObj = $this->getVendorByCityAndLanguage('ny', 'en-US');

        switch( $options['type'] )
        {
          case 'restaurants':
           // $processXmlObj = new processNyXml('import/tony_leo.xml');
           // $processXmlObj->setEvents('/body/event')->setVenues('/body/address');
           // $nyImportMoviesObj = new importNy($processXmlObj,$vendorObj);
           // $nyImportMoviesObj->insertEventCategoriesAndEventsAndVenues();
              $vendorObj = $this->getVendorByCityAndLanguage('dubai', 'en-US');

                //Regression tests
              $curlObj = new curlImporter();
              //$this->barXmlObj =  $this->curlObj->pullXml('http://v7.test.timeoutdubai.com/', 'nokia/bars')->getXml();
               $restaurantXmlObj =  $curlObj->pullXml('http://v7.test.timeoutdubai.com/', 'nokia/restaurants')->getXml();

              //$this->barObject = new dubaiImportBars( $this->barXmlObj, $this->vendorObj, 'bar' );
              $restaurantObj =  new dubaiImportBars( $restaurantXmlObj, $vendorObj, 'restaurant' );
              $restaurantObj->importPoi();

            break;



            break;
        }
        break; // end dubai




    }//end switch



     //Save the logger
     $loggerObj->save();

     //Get the total import time
     echo "Total time: ". $loggerObj->finalTime . "\n";
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
          $nyImportMoviesObj->insertEventCategoriesAndEventsAndVenues();            }
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
  public function importChicagoBc($vendorObj, $ftpClientObj, $loggerObj)
  {
        try
        {
             //Set the logger type
            $loggerObj->setType('poi');
            
            $fileNameString = $ftpClientObj->fetchFile( 'toc_bc.xml' );
            $processXmlObj = new processNyBcXml( $fileNameString );


            $importObj = new chicagoImportBcEd($processXmlObj, $vendorObj,  $loggerObj);
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
  public function importChicagoEd($vendorObj, $ftpClientObj, $loggerObj)
  {
        try
        {
             //Set the logger type
            $loggerObj->setType('poi');

            //$fileNameString = $ftpClientObj->fetchFile( 'toc_ed.xml' );
           // $processXmlObj = new processNyBcXml( $fileNameString );
            $processXmlObj = new processNyBcXml( '/var/workspace/projectn/import/chicago/toc_ed.xml' );

            $importObj = new chicagoImportBcEd($processXmlObj, $vendorObj,  $loggerObj);
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
          $fileNameString = $ftpClientObj->fetchLatestFileByPattern( 'tony_leo.xml' );

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


     private function importNyBc($vendorObj, $ftpClientObj, $loggerObj)
     {
        try
        {
            //Set the logger type
            $loggerObj->setType('poi');

            //Download and process XML
            $fileNameString = $ftpClientObj->fetchFile( 'tony_bc.xml' );
            $processXmlObj = new processNyBcXml( $fileNameString );

            //Import the bars
            $importBcEd = new nyImportBcEd($processXmlObj, $vendorObj,  $loggerObj);
            $importBcEd->import();
        }
        catch ( Exception $e )
        {
          echo 'Exception caught in NY import: ' . $e->getMessage();
        }
         
     }


     private function importNyEd($vendorObj, $ftpClientObj, $loggerObj)
     {
        try
        {
            //Set the logger type
            $loggerObj->setType('poi');

            //Download and process XML
            $fileNameString = $ftpClientObj->fetchFile( 'tony_ed.xml' );
            $processXmlObj = new processNyBcXml( $fileNameString );

            //Import the bars
            $importBcEd = new nyImportBcEd($processXmlObj, $vendorObj,  $loggerObj);
            $importBcEd->import();
        }
        catch ( Exception $e )
        {
          echo 'Exception caught in NY import: ' . $e->getMessage();
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
