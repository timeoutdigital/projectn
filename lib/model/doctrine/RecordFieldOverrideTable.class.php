<?php

class RecordFieldOverrideTable extends Doctrine_Table
{
  public function findActiveOverrideForRecord( $record )
  {
    $query = $this->createQuery( 'o' )
                  ->andWhere( 'o.is_active = true' )
                  ->andWhere( 'o.record_id = ?', $record[ 'id' ] )
    ;
    return $query->execute();
  }

  public function findActiveOverrideForRecordByField( $record, $field )
  {
    $query = $this->createQuery( 'o' )
                  ->andWhere( 'o.field = ?', $field )
                  ->andWhere( 'o.is_active = true' )
                  ->andWhere( 'o.record_id = ?', $record[ 'id' ] )
    ;
    return $query->execute();
  }
}
