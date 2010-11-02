<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


function dashboardRow( $cityName, $log, $model, $htmlClasses = '' )
{
    $status = ( trim( $htmlClasses ) != '' ) ? $htmlClasses . ' ' : '';
    $status .=  $log->getStatusBy( $cityName , $model);
    
    $percentage = $log->getVariantPercentageBy(  $cityName , $model, 1 );
    $number = $log->getVariantNumberBy( $cityName , $model );
    $pastPeriod = $log->getPastPeriodCountBy( $cityName , $model );
    $currentPeriod = $log->getCurrentPeriodCountBy( $cityName , $model );
    
    return sprintf( '<td class="%s" percentage="%s" number="%s" pastperiod="%s" currentperiod="%s" title="Percentage Variant">%s</td>', $status, $percentage, $number, $pastPeriod, $currentPeriod, $percentage );
}

?>
