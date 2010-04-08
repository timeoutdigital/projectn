<?php

/**
 * RecordFieldOverride
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
class RecordFieldOverride extends BaseRecordFieldOverride
{
  public function save( Doctrine_Connection $conn = null )
  {
    if ( $this[ 'id' ] == NULL )
    {
      $recordFinder = new recordFinder();
      $override = $recordFinder->findEquivalentOf( $this )
                               ->comparingAllFieldsExcept( 'id', 'created_at', 'updated_at', 'is_active' )
                               ->getUniqueRecord();

      if( $override[ 'id' ] != NULL )
      {
        $override[ 'is_active' ] = $this[ 'is_active' ];
        $override->save();
        return;
      }
    }
    parent::save();
  }
}
