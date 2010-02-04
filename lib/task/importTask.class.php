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
        $vendorObj = $this->getVendorByCityAndLanguage('ny', 'en-US');

        switch( $options['type'] )
        {
          case 'poi-event':
            $processXmlObj = new processNyXml('import/tony_leo.xml');
            $processXmlObj->setEvents('/body/event')->setVenues('/body/address');
            $nyImportMoviesObj = new importNy($processXmlObj,$vendorObj);
            $nyImportMoviesObj->insertEventCategoriesAndEventsAndVenues();
            break;

          case 'film':
            $processXmlObj = new processNyMoviesXml( 'import/tms.xml' );
            $processXmlObj->setMovies('/xffd/movies/movie');
            $processXmlObj->setPoi('/xffd/theaters/theater');
            $processXmlObj->setOccurances('/xffd/showTimes/showTime');

            $nyImportMoviesObj = new importNyMovies($processXmlObj,$vendorObj);
            $nyImportMoviesObj->importMovies();
            break;

          case 'eating-drinking':
            $vendor = $this->getVendorByCityAndLanguage('ny', 'en-US');
            $csv = new processCsv( 'import/tony_ed_made_up_headers.csv' );
            $nyEDImport =  new importNyED( $csv, $vendor );
            $nyEDImport->insertPois();

            break;
        }
        break; // end ny

      case 'chicago':
        $vendorObj = $this->getVendorByCityAndLanguage('chicago', 'en-US');

        switch( $options['type'] )
        {
          case 'poi-event':
            break;

          case 'film':
            $processXmlObj = new processNyMoviesXml( dirname(__FILE__).'/../../test/unit/data/chicago_movies.xml' );

            $processXmlObj->setMovies( '/xffd/movies/movie' );
            $processXmlObj->setPoi( '/xffd/theaters/theater' );
            $processXmlObj->setOccurances( '/xffd/showTimes/showTime' );

            $nyImportMoviesObj = new importNyMovies( $processXmlObj, $vendorObj) ;
            $nyImportMoviesObj->importMovies();
          break;

          case 'eating-drinking':
          break;
        }
        break; //end chicago

      case 'lisbon':

        $importer    = new Importer();
        $feedObj     = new curlImporter();
        $url         = 'http://www.timeout.pt/';
        $parameters  = array( 'from' => '2010-01-01', 'to' => '2010-01-30' );
        $method      = 'POST';

        switch( $options['type'] )
        {
          case 'poi':
            $request = 'xmlvenues.asp';
            $feedObj->pullXml ( $url, $request, $parameters, $method );
            $importer->addDataMapper( new LisbonFeedVenuesMapper( $feedObj->getXml() ) );
            break;

          case 'event':
            $request = 'xmllist.asp';
            $feedObj->pullXml ( $url, $request, $parameters, $method );
            $importer->addDataMapper( new LisbonFeedListingsMapper( $feedObj->getXml() ) );
          break;

          case 'movie':
            $request = 'xmlfilms.asp';
            $feedObj->pullXml ( $url, $request, $parameters, $method );
            $importer->addDataMapper( new LisbonFeedMoviesMapper( $feedObj->getXml() ) );
          break;
        }

        $importer->run();
        
        break; //end lisbon

      case 'singapore':
        $vendorObj = $this->getVendorByCityAndLanguage('singapore', 'en-US');

        //must be set for price range function
        setlocale(LC_MONETARY, 'en_US.UTF-8');

        switch( $options['type'] )
        {
          case 'poi-event':

            $curlImporterObj = new curlImporter();
            $parametersArray = array( 'section' => 'index', 'thisweek' => '', 'key' => 'ffab6a24c60f562ecf705130a36c1d1e' );
            $curlImporterObj->pullXml ('http://www.timeoutsingapore.com/xmlapi/events/', '', $parametersArray );
            $xmlObj = $curlImporterObj->getXml();
            
            $singaporeImportObj = new singaporeImport( $xmlObj, $vendorObj, $curlImporterObj );
            $singaporeImportObj->insertCategoriesPoisEvents();
            break;

          case 'film':
          break;

          case 'eating-drinking':
          break;
        }
        break; //end lisbon

      case 'london':
      	$connection = $databaseManager->getDatabase( 'searchlight_london' )->getConnection();

        switch( $options['type'] )
        {
          case 'poi-event':
            $london = new LondonImporter( );
            $london->run( );
            break;

          case 'film':
          break;

          case 'eating-drinking':
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
        break; // end ny




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
