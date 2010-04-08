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
    return Doctrine::getTable( $this->getRelationAlias() );
  }

  /**
   * Get the name of the relationship linking the record to the override
   * eg For an Event record this will return 'RecordFieldOverrideEvent'
   * 
   * @return string
   */
  public function getRelationAlias()
  {
    return 'RecordFieldOverride' . get_class( $this->record );
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
      $savedValue = $savedValues[ $field ];
      $this->saveOverride( $field, $savedValue, $editedValue );
    }
  }

  /**
   * @return Doctrine_Collection
   */
  public function getOverrides()
  {
    $alias = $this->getRelationAlias();
    return $this->record[ $alias ];
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
   *
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
    $override->save();
  }

  /**
   * @return string
   */
  private function getRecordType()
  {
    return get_class( $this->record );
  }

}
