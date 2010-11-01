<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function getStatus( $value )
{
  if( !is_numeric( $value ) )
  {
      return "error";
  }

  return ( $value < 0 ) ? 'warning' : 'ok';
}

function buildAndReturnTD( $city, $log, $model )
{
    $percentageValue = isset( $log[ $city ][ $model ] ) ? $log[ $city ][ $model ] : '-';
    return sprintf( '<td class="%s">%s</td>', getStatus( $percentageValue ), is_numeric( $percentageValue ) ? round( $percentageValue, 1 ): '-' );
}

?>
