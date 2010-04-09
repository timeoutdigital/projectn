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
    $savedValues    = $this->record->getLastModified( true );
    $modifiedValues = $this->record->getModified();

    foreach( $modifiedValues as $field => $editedValue )
    {
      $this->deactivateOverridesForField( $field );
      $savedValue = $savedValues[ $field ];
      $this->saveOverride( $field, $savedValue, $editedValue );
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
   *$this->record[ 'id' ]
   * @param string $field
   * @param string $savedValue
   * @param string $editedValue
   */
  private function saveOverride( $field, $savedValue, $editedValue )
  {
    $class = 'RecordFieldOverride' . $this->getRecordType();
    $override  = new $class;
    $override[ 'field' ]          = $field;
    $override[ 'received_value' ] = $savedValue;
    $override[ 'edited_value' ]   = $editedValue;
    $override[ 'is_active' ]      = true;

    $recordType = $this->getRecordType();
    $override[ $recordType ] = $this->getRecord();
    
    //$override = $this->getEquivalentOverrideRecord( $override );

    $override->save();
  }



  private function getEquivalentOverrideRecord( $overrideRecord )
  {
    $recordFinder = new recordFinder();
    $returnOverrideRecord = $recordFinder->findEquivalentOf( $overrideRecord )
                            ->comparingAllFieldsExcept( 'id', 'created_at', 'updated_at', 'is_active' )
                            ->getUniqueRecord();
    
    $returnOverrideRecord[ 'is_active' ] = $overrideRecord[ 'is_active' ];
    
    return $returnOverrideRecord;
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
    $class = 'RecordFieldOverride' . $this->getRecordType();

    $overrides = $this->getOverrideTable()->findActiveOverrideForRecordByField( $this->record, $field );
    foreach ( $overrides as $override )
    {
      $override[ 'is_active' ]      = false;
      $override->save();
    }
  }

}
