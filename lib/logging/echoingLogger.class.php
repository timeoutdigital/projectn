<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage logging.lib
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class echoingLogger implements loggable
{
  private $lineBreak       = PHP_EOL;
  private $separatorChar   = '-';
  private $separatorLength = 30;
  private $newInsertMarker = '+';
  private $existingMarker  = '#';
  private $errorMarker     = 'e';

  private $insertCount = 0;
  private $changeCount = 0;
  private $errorCount  = 0;
  private $markerCount = 0;

  public function  __construct( )
  {
    $this->printLineSeparator();
    echo 'Using verbose logging';
    $this->printLineSeparator();
  }
  
  /**
   * Set the charactor used for printing a separationl line
   *
   * @param <type> $separatorChar 
   */
  public function setSeparatorCharacter( $separatorChar )
  {
    $this->separatorChar = $separatorChar;
  }

  /**
   * Set the lenght of the separation line
   *
   * @param string $separatorLength
   */
  public function setSeparatorLength( $separatorLength )
  {
    $this->separatorLength = $separatorLength;
  }

  /**
   * Set the string that is printed when countNewInsert() is called
   *
   * @param string $output
   */
  public function setNewInsertOutput( $output )
  {
    $this->newInsertMarker = $output;
  }

  /**
   * Set the string that is printed when countNewInsert() is called
   *
   * @param string $output
   */
  public function setExistingOutput( $output )
  {
    $this->existingMarker = $output;
  }

  public function countNewInsert()
  {
    $this->insertCount++;
    $this->printMarker( $this->newInsertMarker );
  }

  public function countExisting()
  {
  }
  
  /**
   * Log a change
   *
   * @param string $type
   * @param string $log Log of all updates
   */
  public function addChange( $type, $modifiedFieldsArray )
  {
    $this->changeCount++;
    $this->printLineBreak();
    echo 'Updated ' . $type . ':';
    $this->printLineBreak();
    var_dump( $modifiedFieldsArray );
    $this->printLineBreak();
  }

  public function addError(Exception $exception, Doctrine_Record $record = null, $message = '')
  {
    $this->errorCount++;

    $recordType = '';
    if( $record )
    {
      $recordType = ' for record of type "' . get_class( $record ) . '"';
    }

    echo 'Error on save attempt: ' . ( $this->markerCount + 1 ) . $recordType;

    $this->printLineBreak( 2 );

    $this->printLineSeparator();

    if( $message )
    {
      echo $message;
      $this->printLineBreak();
    }

    echo $exception->getMessage();
    
    $this->printLineBreak( );

    echo $exception->getTraceAsString();

    $this->printLineBreak( );

    //var_dump( $record->toArray() );

    $this->printLineBreak( );
    
    $this->printLineSeparator();
    $this->printLineBreak( 2 );
  }

  public function save()
  {
    $this->printLineBreak( 2 );
    $this->printLineSeparator();

    echo 'save';
    $this->printLineBreak();
    
    echo 'Inserts: ' . $this->insertCount;
    $this->printLineBreak();
    
    echo 'Updates: ' . $this->changeCount;
    $this->printLineBreak();

    echo 'Errors: ' . $this->errorCount;

    $this->printLineSeparator();
    $this->printLineBreak( 2 );
  }

  private function printMarker( $marker )
  {
    $isMultipleOfTen = ( $this->markerCount % 10 ) == 0;
    $isAlreadyImporting = $this->markerCount > 0;
    
    if( $isMultipleOfTen && $isAlreadyImporting )
    {
      echo ' ' . $this->markerCount;
      $this->printLineBreak();
    }
    echo $marker;
    $this->markerCount++;
  }

  private function printLineBreak( $amount = 1 )
  {
    echo $this->lineBreak;
  }

  private function printLineSeparator()
  {
    $separator = '';
    
    for( $i = 0; $i < $this->separatorLength; $i++ )
    {
      $separator .= $this->separatorChar;
    }

    $this->printLineBreak();
    echo $separator;
    $this->printLineBreak();
  }
}
?>
