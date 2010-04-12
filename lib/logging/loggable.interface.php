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
interface loggable
{
  public function countNewInsert();
  public function countExisting();
  public function addSuccess( Doctrine_Record $newObject, $operation, $changedFields = array() );
  public function addError(Exception $error, Doctrine_Record $record = NULL, $log = '');
  public function save();
}
?>
