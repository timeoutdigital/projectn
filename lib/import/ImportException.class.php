<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ImportExceptionclass
 *
 * @author clarence
 */
class ImportException extends Exception
{
    function __construct( $message )
    {
      parent::__construct( $message );
    }
}
?>
