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
  private $lineBreak = PHP_EOL;
  private $separatorChar = '-';
  private $separatorLength = 30;
  private $newInsertOutput = '+';
  private $existingOutput = '#';

  private $insertCount = 0;
  private $changeCount = 0;
  private $errorCount = 0;

  public function  __construct(  )
  {
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
    $this->newInsertOutput = $output;
  }

  /**
   * Set the string that is printed when countNewInsert() is called
   *
   * @param string $output
   */
  public function setExistingOutput( $output )
  {
    $this->existingOutput = $output;
  }

  public function countNewInsert()
  {
    $this->insertCount++;
    echo $this->newInsertOutput;
  }

  public function countExisting()
  {
    $this->changeCount++;
    echo $this->existingOutput;
  }
  
  /**
   * Log a change
   *
   * @param string $type
   * @param string $log Log of all updates
   */
  public function addChange( $type, $modifiedFieldsArray )
  {
    //echo '#';
  }

  public function addError(Exception $exception, Doctrine_Record $record = null, $message = '')
  {
    $this->errorCount++;

    $this->printLineBreak( 2 );

    $this->printLineSeparator();

    echo $exception->getMessage();

    $this->printLineBreak( );

    echo $exception->getTraceAsString();
    
    $this->printLineSeparator();

    echo $message;

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
