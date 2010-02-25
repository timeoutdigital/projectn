<?php
/**
 * WTF?! you ask? Why doesn't it do anything?
 *
 * So you can extend it and implement one or two methods witout having to
 * write a bunch of empty methods to satisfy the interface.
 *
 * Great for tests, but use carefully in production...
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
class doNothingLogger implements loggable
{
  public function countNewInsert(){}
  public function countExisting(){}
  public function addChange( $type, $modifiedFieldsArray ){}
  public function addError(Exception $exception, Doctrine_Record $record = null, $message = ''){}
  public function save(){}
}
?>
