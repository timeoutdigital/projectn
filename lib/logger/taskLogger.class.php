<?php
/**
 * taskLogger class providing static functions for unified logging of tasks
 *
 * @package projectn
 * @subpackage import.lib
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 *
 * @copyright Timeout Communications Ltd
 *
 *
 * @version 1.0.0
 *
 */

class taskLogger
{

  /**
   * List of params which are not getting logged
   *
   * @var $ignoreParams
   */
  private static $ignoreParams = array( 'help', 'quiet', 'trace', 'version', 'color', 'log,', 'configFolder', 'verbose' );



  /**
   * Start task log
   * 
   * @param sfBaseTask $obj 
   */
  public static function start( $obj, $taskOptions )
  {
      $obj->logSection( 'START:    ' . $obj->getName(), date( 'Y-d-m H:i:s' ) );

      $paramString = '';

      foreach ( $taskOptions as $k => $v )
      {
          if ( !in_array( $k, self::$ignoreParams ) )
          {
              if ( $paramString != '') $paramString .= ', ';

              $paramString .= $k . '=' .$v;
          }
      }

      $obj->logSection( 'Params:', $paramString );
  }

  /**
   * End task log
   * 
   * @param sfBaseTask $obj 
   */
  public static function end( $obj )
  {
      $section = 'END:      ' . $obj->getName() . '  ';
      $obj->logSection( $section, date( 'Y-d-m H:i:s' ) );
  }
    
  /**
   * Log a message
   * 
   * @param sfBaseTask $obj 
   * @param string $message 
   */
  public static function log( $obj, $message )
  {
      $message = str_replace( '\n', PHP_EOL, $message);      
      $logLines = explode( PHP_EOL, $message );

      if ( 1 < count( $logLines ) )
      {
        $obj->logSection( 'Message:', '' );
        $obj->logBlock( $logLines, '' );
      }
      else
      {
        $obj->logSection( 'Message:', implode( '', $logLines) );
      }
  }
  
}
