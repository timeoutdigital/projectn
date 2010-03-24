<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class recordFinder
{
  /**
   * @var Doctrine_Record
   */
  private $recordToFind;

  /**
   * @var array
   */
  private $dontCompareFields;

  public function findEquivalentOf( Doctrine_Record $record )
  {
    $this->recordToFind = $record;
    return $this;
  }

  public function comparingAllFieldsExcept()
  {
    $this->dontCompareFields = func_get_args();
    return $this;
  }

  public function go()
  {
    $query   = $this->createQuery();
    $columns = $this->getColumnsFromRecordTable();
    $record  = $this->recordToFind;

    foreach( $columns as $column )
    {
      if( !$this->shouldCompare( $column ) )
        continue;

      if( $record[$column] )
      {
        $query->addWhere( "r.$column = ? " , $record[$column] );
      }
      else
      {
        $query->addWhere( "r.$column IS NULL" );
      }
    }

    $equivalentRecord = $query->fetchOne();

    if( $equivalentRecord )
    {
      return $equivalentRecord;
    }
    else
    {
      return null;
    }
  }

  private function shouldCompare( $column )
  {
    return !in_array( $column, $this->dontCompareFields );
  }

  private function createQuery()
  {
    return $this->recordToFind->getTable()->createQuery( 'r' );
  }

  private function getColumnsFromRecordTable()
  {
    return $this->recordToFind->getTable()->getColumnNames();
  }
}
?>
