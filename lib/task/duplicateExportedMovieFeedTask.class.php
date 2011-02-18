<?php

class duplicateExportedMovieFeedTask extends sfBaseTask
{
  protected function configure()
  {
      
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
      new sfCommandOption('source', null, sfCommandOption::PARAMETER_REQUIRED, 'source vendor City name (Copy from) --source=moscow', null),
      new sfCommandOption('dest', null, sfCommandOption::PARAMETER_REQUIRED, 'destination vendor City name (Copy to) --dest=omsk', null),
      new sfCommandOption('directory', null, sfCommandOption::PARAMETER_REQUIRED, 'Export directory for movies folder --directory=export_20110218', 'export_' . date( 'Ymd') ),
      new sfCommandOption('override', null, sfCommandOption::PARAMETER_REQUIRED, 'override existing file in destination? --override=true', false ),
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'duplicateExportedMovieFeed';
    $this->briefDescription = 'Duplicate exported Movie from one vendor to another by coping and updating details';
    $this->detailedDescription = <<<EOF
The [duplicateExportedMovieFeed|INFO] copy movie feed from source -> destination and update details to destination specific vendor
Call it with:

  [php symfony duplicateExportedMovieFeed|INFO]
EOF;
  }

    protected function execute($arguments = array(), $options = array())
    {
        // Validate parameters
        if( $options['source'] == null || empty( $options['source'] ) )
        {
            throw new DuplicateExportedMovieFeedTaskException( 'Invalid parameter source [source vendor City name (Copy from) --source="vendor city"]' );
        }
        
        if( $options['dest'] == null || empty( $options['dest'] ) )
        {
            throw new DuplicateExportedMovieFeedTaskException( 'Invalid parameter destination [destination vendor City name (Copy to) --dest=destinationcity]' );
        }
        
        if( $options['directory'] == null || empty( $options['directory'] ) )
        {
            throw new DuplicateExportedMovieFeedTaskException( 'Invalid parameter directory [Export directory for movies folder --directory=export_20110218]' );
        }

        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();
        
        // validate vendors
        $sourceVendor = Doctrine::getTable( 'Vendor' )->findOneByCity( $options['source'] );
        if( $sourceVendor === false )
        {
            throw new DuplicateExportedMovieFeedTaskException( 'Invalid source vendor city "'.$options['source'].'"' );
        }
        
        $destVendor = Doctrine::getTable( 'Vendor' )->findOneByCity( $options['dest'] );
        if( $destVendor === false )
        {
            throw new DuplicateExportedMovieFeedTaskException( 'Invalid destination vendor city "'.$options['dest'].'"' );
        }

        // vaidate export dir and movies
        $exportMovieDir = sfConfig::get('projectn_export') . '/' . $options['directory'] . '/movie';
        if( !is_dir( $exportMovieDir ) )
        {
            throw new DuplicateExportedMovieFeedTaskException( 'Invalid movie Directory specified "'.$options['directory'].'". Movies directory not found: "'.$exportMovieDir.'"' );
        }

        $this->copyMovies( $sourceVendor, $destVendor, $exportMovieDir, ( $options['override'] === 'true' ? true : false ) );
        
    }

    /**
     * Copy movie from source -> dest and update dest vendor details to movie feed
     * @param Vendor $sourceVendor
     * @param Vendor $destVendor
     * @param string $exportMovieDir
     */
    private function copyMovies( Vendor $sourceVendor, Vendor $destVendor, $exportMovieDir, $override )
    {
        $sourceFilePath = $exportMovieDir . '/' . strtolower( str_replace( ' ', '_', $sourceVendor['city'] ) ) . '.xml';
        // validate Source Movie file exists
        if( !file_exists( $sourceFilePath ) )
        {
            throw new DuplicateExportedMovieFeedTaskException( 'Source file not found at ' . $sourceFilePath );
        }

        // read the file and update the variables
        $sourceRawData = file_get_contents( $sourceFilePath );
        $sourceXML = simplexml_load_string( $sourceRawData );   // This will be used to validate No. of nodes and validity once updated

        // replace source vendor AIRPOT CODE int the ID with destination vendor AIRPORT CODE
        $sourceRawData = str_replace( '<movie id="' . strtoupper( $sourceVendor['airport_code']),  '<movie id="' . strtoupper( $destVendor['airport_code']), $sourceRawData );

        // Validate updates don't compromise XML structural integrity
        $destXML = simplexml_load_string( $sourceRawData );

        if( count( $destXML ) != count( $sourceXML ) )
        {
            throw new DuplicateExportedMovieFeedTaskException( "Source xml node count and Destination xml node count don't match!" );
        }

        // write as new file
        $destFilePath = $exportMovieDir . '/' . strtolower( str_replace( ' ', '_', $destVendor['city'] ) ) . '.xml';

        if( !$override && file_exists( $destFilePath ) )
        {
            throw new DuplicateExportedMovieFeedTaskException( "There a file exists in destination, if you would like to override; please specify --override=true. Destination file: {$destFilePath}"  );
        }

        // write file in destination
        file_put_contents( $destFilePath, $destXML->saveXML() );

        // DONE!
    }
}

class DuplicateExportedMovieFeedTaskException extends Exception{}