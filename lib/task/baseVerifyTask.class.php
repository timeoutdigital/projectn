<?php 
/**
 * Checks place-ids in an event xml exist in the poi xml
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
abstract class baseVerifyTask
{
  private $task;
  private $message;

  public function run( $task )
  {
    $this->task = $task;
    return $this->verify();
  }

  abstract protected function verify();

  protected function getPoiXml()
  {
    return $this->task->getPoiXml();
  }

  protected function getEventXml()
  {
    return $this->task->getEventXml();
  }

  protected function getOption( $option )
  {
    return $this->task->getOption( $option );
  }

  protected function setMessage( $message )
  {
    $this->message = $message;
  }

  public function getMessage()
  {
    return $this->message;
  }
}
