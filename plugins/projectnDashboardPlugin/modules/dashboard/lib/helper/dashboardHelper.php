<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function getStatus( $value, $threshold = 0 )
{
  if( !is_numeric( $value ) )
  {
      return "no-log";
  }

  return ( $value < 0 ) ? 'warning' : 'ok';
}

function buildAndReturnTD( $city, $log, $model, $threshold, $className = '' )
{
    $threshold = ( !is_numeric( $threshold ) ) ? 0 : $threshold;
    $percentageValue = isset( $log[ $city ][ $model ] ) ? $log[ $city ][ $model ] : '-';
    return sprintf( '<td class="%s">%s</td>', $className. ' ' . getStatus( $percentageValue, $threshold ),is_numeric( $percentageValue ) ? round( $percentageValue, 1 ): '-' );
}

?>
