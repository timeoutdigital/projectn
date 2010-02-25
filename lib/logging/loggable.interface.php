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
  public function addChange( $type, $modifiedFieldsArray );
  public function addError(Exception $exception, Doctrine_Record $record = null, $message = '');
  public function save();
}
?>
