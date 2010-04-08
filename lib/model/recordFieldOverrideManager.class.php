<?php
/**
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class recordFieldOverrideManager 
{
  /**
   * @var Doctrine_Record
   */
  private $record;

  /**
   * @param Doctrine_Record $record
   */
  public function __construct( Doctrine_Record $record )
  {
    $this->record = $record;
  }

  /**
   * @return Doctrine_Record
   */
  public function getRecord()
  {
    return $this->record;
  }

  /**
   * creates and saves record overrides using the results of getModified()
   * on the record this instance is working on.
   */
  public function saveRecordModificationsAsOverrides()
  {
    $savedValues    = $this->record->getLastModified( true );
    $modifiedValues = $this->record->getModified();

    foreach( $modifiedValues as $field => $editedValue )
    {
      $savedValue = $savedValues[ $field ];
      $this->saveOverride( $field, $savedValue, $editedValue );
    }
  }

  public function applyOverridesToRecord()
  {
    foreach( $this->getOverrides() as $override )
    {
      if( $this->lastReceivedValueEqualsValueIn( $override ) )
      {
        $this->applyOverride( $override );
      }
    }
  }

  private function lastReceivedValueEqualsValueIn( $override )
  {
    $field = $override[ 'field' ];
    return $override[ 'received_value' ] == $this->record[ $field ];
  }

  private function applyOverride( $override )
  {
      $field = $override[ 'field' ];
      $this->record[ $field ] = $override[ 'edited_value' ];
  }

  /**
   * Creates and saves an override
   *
   * @param string $field
   * @param string $savedValue
   * @param string $editedValue
   */
  private function saveOverride( $field, $savedValue, $editedValue )
  {
    $override  = new RecordFieldOverride();
    $override[ 'field' ]          = $field;
    $override[ 'received_value' ] = $savedValue;
    $override[ 'edited_value' ]   = $editedValue;

    $recordType = $this->getRecordType();
    $override[ $recordType ][] = $this->getRecord();
    $override->save();
  }

  /**
   * @return string
   */
  private function getRecordType()
  {
    return get_class( $this->record );
  }

  /**
   * @return Doctrine_Collection
   */
  private function getOverrides()
  {
    return $this->record[ 'RecordFieldOverride' ];
  }

}
