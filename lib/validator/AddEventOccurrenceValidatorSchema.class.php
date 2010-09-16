<?php
/**
 * widgetFormVendorPoiCategory
 *
 * @package symfony
 * @subpackage validator.lib.modules.data_entry.apps
 *
 * @author Ralph Schwaninger <ralphschwaninger@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class AddEventOccurrenceValidatorSchema extends sfValidatorSchema
{

  protected function configure($options = array(), $messages = array())
  {
   // $this->addMessage('poi_id', 'The poi is required.');
   // $this->addMessage('start_date', 'The start date is required.');
  }

  protected function doClean($values)
  {

    $errorSchema = new sfValidatorErrorSchema($this);

    if( $values[ 'start_time' ][ 'hour']  == "0" )
    {
        $values[ 'start_time' ][ 'hour'] = "00";
    }
    if( $values[ 'start_time' ][ 'minute']  == "0" )
    {
        $values[ 'start_time' ][ 'minute'] = "00";
    }
    if( $values[ 'end_time' ][ 'hour']  == "0" )
    {
        $values[ 'end_time' ][ 'hour'] = "00";
    }
    if( $values[ 'end_time' ][ 'minute']  == "0" )
    {
        $values[ 'end_time' ][ 'minute'] = "00";
    }

    //if no data is given don't validate
   if(  empty( $values[ 'poi_id' ] ) &&
        empty( $values[ 'start_date' ] ) &&
        empty( $values[ 'end_date' ] ) &&
        empty( $values[ 'start_time' ][ 'hour'] ) &&
        empty( $values[ 'start_time' ][ 'minute'] ) &&
        empty( $values[ 'end_time' ][ 'hour'] ) &&
        empty( $values[ 'end_time' ][ 'minute'] ) )
    {
        return $values;
    }

    if(  empty( $values[ 'poi_id' ] ))
    {
        $errorSchema->addError(new sfValidatorError($this, 'Please select a Poi for the occurrence'), 'poi_id');
    }

    if( empty( $values[ 'start_date' ] ) )
    {
        $errorSchema->addError(new sfValidatorError($this, 'Start date is required'), 'start_date');
    }

    if( empty( $values[ 'end_date' ] ) )
    {
         $errorSchema->addError(new sfValidatorError($this, 'End date is required'), 'end_date');
    }

    if( empty( $values[ 'start_time' ][ 'hour'] )   || empty( $values[ 'start_time' ][ 'minute'] ))
    {
        $errorSchema->addError(new sfValidatorError($this, 'Start time is required'), 'start_time');
    }

    if( empty( $values[ 'end_time' ][ 'hour'] ) || empty( $values[ 'end_time' ][ 'minute'] ) )
    {
        $errorSchema->addError(new sfValidatorError($this, 'End time is required'), 'end_time');
    }

    if( count($errorSchema) == 0  &&
        $values[ 'start_date' ] == $values[ 'end_date' ]  &&
        $values[ 'start_time' ][ 'hour'] > $values[ 'end_time' ][ 'hour']  )
    {
        $errorSchema->addError(new sfValidatorError($this, "Events start time is later than it's end time" ), 'end_time');
    }


    //If the form is valid so far and we want to add recurring occurrences but the start date != end_date
    //we can't do it
    if ( count($errorSchema) == 0  && $values [ 'start_date' ] !=  $values [ 'end_date' ] &&  $values[ 'recurring_dates' ] [ 'recurring_freq' ] != 'never')
    {
         $errorSchema->addError(new sfValidatorError($this, 'You can add multiple occurrences if only "Start date" and "End date" is same!'), 'recurring_dates');
    }

    if( count($errorSchema) == 0  && $values[ 'recurring_dates' ] [ 'recurring_freq' ] != 'never')
    {
        if( empty( $values[ 'recurring_dates' ]['recurring_until'] ) )
        {
            $errorSchema->addError(  new sfValidatorError( $this,
                    'Please specify the last confirmed date of the event.',
                     array('field' => 'recurring_dates') )  );
        }else
        {
            $today = new DateTime( date( 'Y-m-d' ) );
            $recurringUntil  = new DateTime( $values[ 'recurring_dates' ]['recurring_until'] );
            $diff = $recurringUntil->diff( $today ) ;
            if( $diff->y >= 1  )
            {
                $errorSchema->addError(  new sfValidatorError( $this,
                    'Cannot create occurrences for more than one year.',
                     array('field' => 'recurring_dates') )  );
            }
        }
    }

    if ( count($errorSchema) )
    {
      throw new sfValidatorErrorSchema($this, $errorSchema);
    }

    return $values;

  }
}