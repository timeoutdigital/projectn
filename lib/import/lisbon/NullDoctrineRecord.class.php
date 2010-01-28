<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class NullDoctrineRecord extends Doctrine_Record
{
  /**
   * Null record is never valid
   *
   * @return boolean
   */
  public function isValid()
  {
    throw new Exception( 'Called isValid() on a NullDoctrineRecord' );
  }

  /**
   * Null record can not be saved
   */
  public function save()
  {
    throw new Exception( 'Called save() on a NullDoctrineRecord' );
  }
}
?>
