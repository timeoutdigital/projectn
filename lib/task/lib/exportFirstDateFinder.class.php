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
        $cityName =  str_replace( '_', ' ', array_shift( explode( '.', $fileName ) ) );

        if( !isset( $firstExportDates[ $cityName ] ) )
        {
          $firstExportDates[ $cityName ] = $date;
        }
      }
    }

    return $firstExportDates;
  }
}
