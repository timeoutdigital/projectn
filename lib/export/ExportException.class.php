<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ExportExceptionclass
 *
 * @author clarence
 */
class ExportException extends Exception
{
  public function __construct( $message )
  {
      parent::__construct($message);
  }
}
?>
