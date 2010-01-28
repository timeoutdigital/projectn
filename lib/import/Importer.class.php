<?php
/**
 * @package import.lib
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 */
class Importer
{
  /**
   * @var array
   */
  private $loggers;
  
  /**
   * Takes an array of strings implodes only values that are not blank using $glue
   *
   * <code>
   *   $input = array( 'one', '', 'two', '', three' );
   *   echo Importer::concatNonBlankStrings( ',', $input );
   *
   *   //outputs:
   *   //one, two, three
   * </code>
   * 
   * @param array $stringArray
   * @param string $glue
   */
  static public function concatNonBlankStrings( $glue, $stringArray )
  {
    $nonEmptyStrings = array_filter($stringArray, 'Importer::concatNonBlankStringsCallBack' );
    return implode($glue, $nonEmptyStrings );
  }

  static private function concatNonBlankStringsCallBack( $string )
  {
    return preg_match( '/\S/', $string );
  }

  /**
   * Adds a logger
   * 
   * @param logger $logger
   */
  public function registerLogger( logger $logger )
  {
    if( !$this->loggers )
    {
      $this->loggers = array();
    }

    if( !isset( $this->loggers[ $logger->getType() ] ) )
    {
      $this->loggers[ $logger->getType() ] = array();
    }

    $this->loggers[ $logger->getType() ][] = $logger;
  }

  /**
   * Retrieves all registered loggers
   */
  public function getLoggers()
  {
    return $this->loggers;
  }

  /**
   * Adds an ImportData to be saved
   */
  public function addImportData( ImportData $importData )
  {
    if( !$this->importData )
    {
      $this->importData = array();
    }

    
  }

  /**
   * gets all added ImportData
   */
  public function getImportData()
  {

  }

  public function run()
  {
    
  }
}
