<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LondonImportExceptionclass
 *
 * @author clarence
 */
class LondonImportException extends Exception
{
    public function  __construct( $message )
    {
      parent::__construct( $message );
    }
}
?>
