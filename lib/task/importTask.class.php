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
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    //Select the task
    switch( $options['city'] )
    {
      case 'ny':
        $vendorObj = $this->getVendorByCityAndLanguage('ny', 'en-GB');

        switch( $options['type'] )
        {
          case 'poi-event':
            $processXmlObj = new processNyXml('import/tony_leo.xml');
            $processXmlObj->setEvents('/body/event')->setVenues('/body/address');
            $nyImportMoviesObj = new importNy($processXmlObj,$vendorObj);
            $nyImportMoviesObj->insertEventCategoriesAndEventsAndVenues();
            break;

          case 'film':
            $processXmlObj = new processNyMoviesXml(dirname(__FILE__).'/../../test/unit/data/tms.xml');
            $processXmlObj->setMovies('/xffd/movies/movie');
            $processXmlObj->setPoi('/xffd/theaters/theater');
            $processXmlObj->setOccurances('/xffd/showTimes/showTime');

            $nyImportMoviesObj = new importNyMovies($processXmlObj,$vendorObj);
            $nyImportMoviesObj->importMovies();
            break;

          case 'eating-drinking':
            $vendor = $this->getVendorByCityAndLanguage('ny', 'english');
            $csv = new processCsv( 'import/tony_ed_made_up_headers.csv' );
            $nyEDImport =  new importNyED( $csv, $vendor );
            $nyEDImport->insertPois();

            break;
        }
        break; // end ny

      case 'chicago':
        $vendorObj = $this->getVendorByCityAndLanguage('chicago', 'en-GB');

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
        switch( $options['type'] )
        {
          case 'poi-event':
            $processXmlObj = new curlImporter();
            $parameters = array( 'from' => '2010-01-01', 'to' => '2010-01-30' );
            $processXmlObj->pullXml ('http://www.timeout.pt/', 'xmllist.asp', $parameters, 'POST' );
            break;

          case 'film':
          break;

          case 'eating-drinking':
          break;
        }
        break; //end lisbon

      case 'singapore':
        $vendorObj = $this->getVendorByCityAndLanguage('singapore', 'en-GB');

        switch( $options['type'] )
        {
          case 'poi-event':

            $curlImporterObj = new curlImporter();
            $parametersArray = array( 'section' => 'index', 'thisweek' => '', 'key' => 'ffab6a24c60f562ecf705130a36c1d1e' );
            $curlImporterObj->pullXml ('http://www.timeoutsingapore.com/xmlapi/events/', '', $parametersArray );
            $xmlObj = $curlImporterObj->getXml();
            
            $nyImportMoviesObj = new singaporeImport( $xmlObj, $vendorObj );
            $nyImportMoviesObj->insertCategoriesPoisEvents();
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
