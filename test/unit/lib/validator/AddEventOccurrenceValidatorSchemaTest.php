<?php
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'/../../bootstrap.php';

/**
 * Test class for validatorVendorEventCategoryChoice
 *
 * @package test
 * @subpackage validator.lib.unit
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class AddEventOccurrenceValidatorSchemaTest extends PHPUnit_Framework_TestCase
{
    public function testNoErrorsIfNoData()
    {
        $data = array
        (
            'poi_id' => '',
            'start_date' => '',
            'end_date' => '',
            'start_time' => Array
            (
                'hour' => '',
                'minute' => '',
            ),

            'end_time' => Array
            (
                'hour' => '',
                'minute' => '',
            ),

            'recurring_dates' => Array
            (
                'recurring_freq' => 'daily',
                'recurring_weekly_week_number' => '1',
                'recurring_monthly_month_number' => '1',
                'recurring_monthly_position' => 'first',
                'recurring_monthly_weekday' => 'mon',
                'recurring_until' => '',
            )

        );

        $validator = new AddEventOccurrenceValidatorSchema();
        try
        {
            $validator->clean( $data );
        }
        catch ( sfValidatorErrorSchema $e )
        {
           $this->fail();
        }
        $this->assertTrue( true );
    }

    public function testErrorsIfMissingData()
    {
        $data = array
        (
            'poi_id' => '',
            'start_date' => '',
            'end_date' => '2010-10-05',
            'start_time' => Array
            (
                'hour' => '',
                'minute' => '',
            ),

            'end_time' => Array
            (
                'hour' => '',
                'minute' => '',
            ),

            'recurring_dates' => Array
            (
                'recurring_freq' => 'daily',
                'recurring_weekly_week_number' => '1',
                'recurring_monthly_month_number' => '1',
                'recurring_monthly_position' => 'first',
                'recurring_monthly_weekday' => 'mon',
                'recurring_until' => '',
            )

        );

        //expecting exceptions for poi_id ,start_date start_time and end_time
        $poiIdExceptionThrown       = false;
        $startDateExceptionThrown   = false;
        $endDateExceptionThrown     = false;    //we don't want this to be thrown because the end date is given in the data
        $startTimeExceptionThrown   = false;
        $endTimeExceptionThrown     = false;

        $validator = new AddEventOccurrenceValidatorSchema();
        try
        {
            $validator->clean( $data );
        }
        catch ( sfValidatorErrorSchema $e )
        {
            foreach ($e->getErrors() as $key => $errorObj)
            {
                if( $key == 'poi_id' )
                {
                    $poiIdExceptionThrown = true;
                }

                if( $key == 'start_date' )
                {
                    $startDateExceptionThrown = true;
                }

                if( $key == 'start_time' )
                {
                    $startTimeExceptionThrown = true;
                }

                if( $key == 'end_time' )
                {
                    $endTimeExceptionThrown = true;
                }

                if( $key == 'end_date' )
                {
                    var_dump( $errorObj->__toString() );
                    $endDateExceptionThrown = true;
                }

            }
        }

        $this->assertTrue( $poiIdExceptionThrown , 'expecting exception due to missing poi_id' );
        $this->assertTrue( $startDateExceptionThrown , 'expecting exception due to missing start_date' );
        $this->assertTrue( $startTimeExceptionThrown , 'expecting exception due to missing start_time' );
        $this->assertTrue( $endTimeExceptionThrown , 'expecting exception due to missing end_time' );
        $this->assertFalse( $endDateExceptionThrown , 'not expecting exception for end date' );

    }
    public function testValidationErrorIfStartDateAndEndDateAreDifferent()
    {
        $data = array
        (
            'poi_id' => '1',
            'start_date' => '2010-10-03',
            'end_date' => '2010-10-05',
            'start_time' => Array
            (
                'hour' => '10',
                'minute' => '20',
            ),

            'end_time' => Array
            (
                'hour' => '10',
                'minute' => '50',
            ),

            'recurring_dates' => Array
            (
                'recurring_freq' => 'daily',
                'recurring_weekly_week_number' => '1',
                'recurring_monthly_month_number' => '1',
                'recurring_monthly_position' => 'first',
                'recurring_monthly_weekday' => 'mon',
                'recurring_until' => '2010-10-09',
            )

        );

        $recurringDatesExceptionThrown   = false;


        $validator = new AddEventOccurrenceValidatorSchema();
        try
        {
            $validator->clean( $data );
        }
        catch ( sfValidatorErrorSchema $e )
        {
            foreach ($e->getErrors() as $key => $errorObj)
            {
                if( $key == 'recurring_dates' )
                {
                    $recurringDatesExceptionThrown = true;
                }
            }
        }

        $this->assertTrue( $recurringDatesExceptionThrown , 'expecting recurring_dates validation error due to different start and end dates' );

    }

    public function testValidationErrorWithMissingRecurringUntil()
    {
        $data = array
        (
            'poi_id' => '1',
            'start_date' => '2010-10-03',
            'end_date' => '2010-10-05',
            'start_time' => Array
            (
                'hour' => '10',
                'minute' => '20',
            ),

            'end_time' => Array
            (
                'hour' => '10',
                'minute' => '50',
            ),

            'recurring_dates' => Array
            (
                'recurring_freq' => 'daily',
                'recurring_weekly_week_number' => '1',
                'recurring_monthly_month_number' => '1',
                'recurring_monthly_position' => 'first',
                'recurring_monthly_weekday' => 'mon',
                'recurring_until' => '',
            )

        );

        $recurringDatesExceptionThrown   = false;


        $validator = new AddEventOccurrenceValidatorSchema();
        try
        {
            $validator->clean( $data );
        }
        catch ( sfValidatorErrorSchema $e )
        {
            foreach ($e->getErrors() as $key => $errorObj)
            {
                if( $key == 'recurring_dates' )
                {
                    $recurringDatesExceptionThrown = true;
                }
            }
        }

        $this->assertTrue( $recurringDatesExceptionThrown , 'expecting recurring_dates validation error due missing recurring_until' );
    }

        public function testValidationErrorWithRecurringUntillaterThanAYear()
    {
        $data = array
        (
            'poi_id' => '1',
            'start_date' => date( 'Y-m-d' ),
            'end_date' => date( 'Y-m-d' ),
            'start_time' => Array
            (
                'hour' => '10',
                'minute' => '20',
            ),

            'end_time' => Array
            (
                'hour' => '10',
                'minute' => '50',
            ),

            'recurring_dates' => Array
            (
                'recurring_freq' => 'daily',
                'recurring_weekly_week_number' => '1',
                'recurring_monthly_month_number' => '1',
                'recurring_monthly_position' => 'first',
                'recurring_monthly_weekday' => 'mon',
                'recurring_until' => date( 'Y-m-d', strtotime( '+1 Year' )),
            )

        );

        $recurringDatesExceptionThrown   = false;


        $validator = new AddEventOccurrenceValidatorSchema();
        try
        {
            $validator->clean( $data );
        }
        catch ( sfValidatorErrorSchema $e )
        {
            foreach ($e->getErrors() as $key => $errorObj)
            {
                if( $key == 'recurring_dates' )
                {
                    $recurringDatesExceptionThrown = true;
                }
            }
        }

        $this->assertTrue( $recurringDatesExceptionThrown , 'expecting recurring_dates validation error due recurring_until later or equal to 1 year' );
    }

    public function testValidationErrorWithEndTimeEarlierThanStartTime()
    {
        $data = array
        (
            'poi_id' => '1',
            'start_date' =>  '2010-10-03',
            'end_date'   =>  '2010-10-03',  //same day with start_date
            'start_time' => Array
            (
                'hour' => '10',
                'minute' => '20',
            ),

            'end_time' => Array
            (
                'hour' => '02',
                'minute' => '50',
            ),

            'recurring_dates' => Array
            (
                'recurring_freq' => 'never',
                'recurring_weekly_week_number' => '1',
                'recurring_monthly_month_number' => '1',
                'recurring_monthly_position' => 'first',
                'recurring_monthly_weekday' => 'mon',
                'recurring_until' => '',
            )

        );

        $endTimeExceptionThrown   = false;

        $validator = new AddEventOccurrenceValidatorSchema();
        try
        {
            $validator->clean( $data );
        }
        catch ( sfValidatorErrorSchema $e )
        {
            foreach ($e->getErrors() as $key => $errorObj)
            {
                if( $key == 'end_time' )
                {
                    $endTimeExceptionThrown = true;
                }
            }
        }

        $this->assertTrue( $endTimeExceptionThrown , 'End time should be later than start time when start and end dates are same' );

        // test 2
        // if the start_date and end_date are different, end time can be earlier than start time
        // change the end_date

        $data [ 'end_date' ] = '2010-10-04'; //different date from the start date
        $endTimeExceptionThrown   = false;

        $validator = new AddEventOccurrenceValidatorSchema();
        try
        {
            $validator->clean( $data );
        }
        catch ( sfValidatorErrorSchema $e )
        {
            foreach ($e->getErrors() as $key => $errorObj)
            {
                if( $key == 'end_time' )
                {
                    $endTimeExceptionThrown = true;
                }
            }
        }

        $this->assertFalse( $endTimeExceptionThrown , 'End time can be earlier than start time when start and end dates are not same' );

    }
}
