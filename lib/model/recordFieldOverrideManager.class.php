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
   * Get the table containing the overrides
   * 
   * @return Doctrine_Table
   */
  public function getOverrideTable()
  {
    $tableName = 'RecordFieldOverride' . $this->getRecordType();
    return Doctrine::getTable( $tableName );
  }

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
    $currentReceivedValues = $this->record->getModified( true );
    $modifiedValues        = $this->record->getModified();

    foreach( $modifiedValues as $field => $editedValue )
    {
      if( empty( $editedValue ) && is_null( $currentReceivedValues[ $field ] ) )
        continue;

      if( $field == 'updated_at' )
        continue;

      $this->deactivateOverridesForField( $field );
      $currentReceivedValue = $currentReceivedValues[ $field ];
      $this->saveOverride( $field, $currentReceivedValue, $editedValue );
    }
  }

  /**
   * @return Doctrine_Collection
   */
  public function getActiveOverrides()
  {
    $ret = $this->getOverrideTable()->findActiveOverrideForRecord( $this->record );
    return $ret;
  }

  /**
   * @return Doctrine_Collection
   */
  public function getActiveOverrideByField( $field )
  {
    $ret = $this->getOverrideTable()->findActiveOverrideForRecordByField( $this->record, $field );
    return $ret;
  }

  /**
   * @return Doctrine_Collection
   */
  public function getAllOverrides()
  {
    //$alias = $this->getRelationAlias();
    //$ret = $this->record[ $alias ];
    //todo find out why the above relationship does not return all override records

    $ret = $this->getOverrideTable()->findByRecordId( $this->record[ 'id' ] );
    return $ret;
  }

  /*
   * Apply the overrides to the record
   */
  public function applyOverridesToRecord()
  {
    foreach( $this->getActiveOverrides() as $override )
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
   *$this->record[ 'id' ]
   * @param string $field
   * @param string $currentReceivedValue
   * @param string $editedValue
   */
  private function saveOverride( $field, $currentReceivedValue, $editedValue )
  {
    $class = 'RecordFieldOverride' . $this->getRecordType();
    $override  = new $class;
    $override[ 'field' ]          = $field;
    $override[ 'received_value' ] = $currentReceivedValue;
    $override[ 'edited_value' ]   = $editedValue;
    $override[ 'is_active' ]      = true;

    $recordType = $this->getRecordType();
    $override[ $recordType ] = $this->getRecord();
    
    $override->save();
  }

  /**
   * @return string
   */
  private function getRecordType()
  {
    return get_class( $this->record );
  }

  private function deactivateOverridesForField( $field )
  {
    $overrides = $this->getOverrideTable()->findActiveOverrideForRecordByField( $this->record, $field );

    foreach ( $overrides as $override )
    {
      $override[ 'is_active' ] = false;

      //it appears that relations are confusing
      //the save method on the RecordFieldOverride
      //superclass and causing a save failure
      $override->clearRelated();
      $override->save();
    }
  }

}
