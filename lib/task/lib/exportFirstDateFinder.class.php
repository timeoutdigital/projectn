<?php

class exportFirstDateFinder
{
  private $exportType;
  private $pathToExports;

  public function __construct( $exportType, $pathToExports )
  {
    $this->exportType    = $exportType;
    $this->pathToExports = $pathToExports;
  }

  public function run()
  {
    $firstExportDates = array();

    foreach( DirectoryIteratorN::iterate( $this->pathToExports, DirectoryIteratorN::DIR_FOLDERS ) as $dirName )
    {
      try
      {
        $date = new DateTime( str_replace( 'export_', '', $dirName ) );
      }
      catch( Exception $e )
      {
        continue;
      }

      $absolutePath = implode( '/', array( $this->pathToExports,
                                           $dirName,
                                           $this->exportType,
                                         ) );

      foreach( DirectoryIteratorN::iterate( $absolutePath, DirectoryIteratorN::DIR_FILES, 'xml' ) as $fileName )
      {
        // First split the filename and see if we could find the City name (Can be splitted),
        // and than select the city name
        $fileNameSplit = explode( '.', $fileName );
        if( !is_array( $fileNameSplit ) || count( $fileNameSplit ) <= 0 )
        {
          continue;
        }
          
        $cityName =  str_replace( '_', ' ', array_shift( $fileNameSplit ) );

        if( !isset( $firstExportDates[ $cityName ] ) )
        {
          $firstExportDates[ $cityName ] = $date;
        }
      }
    }

    return $firstExportDates;
  }
}
