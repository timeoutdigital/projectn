<?php

/**
 * Recurring Event Widget
 *
 * @package toWidgetFormEventRecurring
 * @author Clarence Lee
 **/
class toWidgetFormEventRecurring extends sfWidgetForm
{
    /**
     * Constructor.
     *
     * @param array $options Available options: 'daily_weekly_monthly' => 'daily'|'weekly'|'monthly', 'every_x_weeks' => integer
     * @param array $attributes
     *
     * @see sfWidgetFormInput
     **/
    protected function configure( $options = array(), $attributes = array() )
    {
        $this->addOption( 'dependentOn' );
        parent::configure($options, $attributes);
    }

    /**
     * @param  string $name        The element name
     * @param  string $value       The value displayed in this widget
     * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
     * @param  array  $errors      An array of errors for the field
     *
     **/
    public function render( $name, $value = null, $attributes = array(), $errors = array() )
    {
        $this->widgetDefaultValues = $value;
        $this->widgetName = $name;

        $output = $this->renderRepeatFrequencyChoices( '[recurring_freq]',  'controller' )

                . $this->renderWeekFrequencyInput( '[recurring_weekly_week_number]',   'weekly parent-daily_weekly_monthly show-if-weekly child hidden', 'Every INPUT weeks(s)' )
                . $this->renderWeekFrequencyInput( '[recurring_monthly_month_number]', 'monthly parent-daily_weekly_monthly show-if-monthly child hidden', 'Every INPUT month(s)' )
                . $this->renderWeekdayCheckboxes(  '[recurring_daily_except]',         'daily parent-daily_weekly_monthly child show-if-daily ',
                    '<span class="note above">Daily except these days</span> INPUT' )

                . $this->renderWeekdayCheckboxes( '[recurring_weekly_days]',  'weekly parent-daily_weekly_monthly show-if-weekly child hidden',
                    '<span class="note above">On which days(s) does you event happen?</span> INPUT' )

                . $this->renderMonthlyPosition( '[recurring_monthly_position]', 'monthly parent-daily_weekly_monthly show-if-monthly child hidden' )
                . $this->renderMonthlyWeekday(  '[recurring_monthly_weekday]',   'monthly parent-daily_weekly_monthly show-if-monthly child hidden' )

                . $this->renderInformation(  '[recurring_freq_other]',  'other hidden parent-daily_weekly_monthly show-if-other child' )
                . $this->renderEndDateInput( '[recurring_until]',        'daily weekly monthly other parent-daily_weekly_monthly show-if-daily-weekly-monthly child ' )
                . '<script type="text/javascript">'
                . '$( function() {'
                . '  var dailyWeeklyMonthly = $( "#daily_weekly_monthly input[CHECKED]" ).val();'
                . '  $( "#form-recurring_dates div.daily"   ).addClass( "hidden" );'
                . '  $( "#form-recurring_dates div.weekly"  ).addClass( "hidden" );'
                . '  $( "#form-recurring_dates div.monthly" ).addClass( "hidden" );'
                . '  $( "#form-recurring_dates div.other"   ).addClass( "hidden" );'
                . '  $( "#form-recurring_dates div." + dailyWeeklyMonthly ).removeClass( "hidden" );'
                . '  if( dailyWeeklyMonthly == "other" )'
                . '  $( "#form-recurring_dates div.other.date" ).addClass( "hidden" );'
                . '});'
                . '</script>'
                ;

        return $this->divWrap($output, 'gray-box' );
    }

    private function renderRepeatFrequencyChoices( $name, $group = '', $text = '' )
    {
        $widget = new sfWidgetFormChoice(
            array (
                'choices' => array (
                    'other'  =>  "Never",
                    'daily'   => 'Daily',
                    'weekly'  => 'Weekly',
                    'monthly' => 'Monthly',

                ),
                'expanded' => true,
            )
        );

        $widgetOutput = $widget->render( $this->widgetName . $name, $this->getWidgetDefault( $name, 'other' ) );

        return $this->divWrap( $widgetOutput, $group, 'daily_weekly_monthly' ) ;
    }

    private function renderWeekFrequencyInput( $name, $group = '', $text='' )
    {
        $widget = new sfWidgetFormChoice(
            array (
                'choices' => array (
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',

                )
            )
        );

        $widgetOutput = $widget->render( $this->widgetName . $name, $this->getWidgetDefault( $name, '1' ) );

        $widgetOutput = preg_replace( '/INPUT/', $widgetOutput, $text );

        return $this->divWrap( $widgetOutput, $group );
    }

    private function renderWeekdayCheckboxes( $name, $group = '', $text='' )
    {
        $widget = new sfWidgetFormChoice(
            array (
                'choices' => array (
                    'monday'    => 'Monday',
                    'tuesday'   => 'Tuesday',
                    'wednesday' => 'Wednesday',
                    'thursday'  => 'Thursday',
                    'friday'    => 'Friday',
                    'saturday'  => 'Saturday',
                    'sunday'    => 'Sunday',
                ),
                'multiple' => true,
                'expanded' => true,
                )
        );

        $widgetOutput = $widget->render( $this->widgetName . $name, $this->getWidgetDefault( $name ) );

        $widgetOutput = preg_replace( '/INPUT/', $widgetOutput, $text );

        return $this->divWrap( $widgetOutput, $group );
    }

    private function renderEndDateInput( $name, $group='' )
    {
        $widget = new sfWidgetFormInput( );

        $widgetOutput = $widget->render( $this->widgetName . $name, $this->getWidgetDefault( $name ), array( 'class' => 'datepicker recurring_until connect-with-start_date-as-min' ) );

        $group .= ' date';

        return $this->divWrap( 'Event runs until ' . $widgetOutput . ' <span class="note below">(leave blank if event runs indefinitely)</span>', $group );
    }

    private function renderMonthlyPosition( $name, $group = '' )
    {
        $widget = new sfWidgetFormChoice(
            array (
                'choices' => array (
                    'first'  => 'First',
                    'second' => 'Second',
                    'third'  => 'Third',
                    'fourth' => 'Fourth',
                    'fifth'  => 'Fifth',
                    'last'   => 'Last',
                ),
            )
        );

        $widgetOutput = $widget->render( $this->widgetName . $name, $this->getWidgetDefault( $name ) );

        return $this->divWrap( $widgetOutput, $group, 'recurring_monthly_position_container' );
    }

    private function renderMonthlyWeekday( $name, $group = '' )
    {
        $widget = new sfWidgetFormChoice(
            array (
                'choices' => array (
                    'mon'  => 'Monday',
                    'tue'  => 'Tuesday',
                    'wed'  => 'Wednesday',
                    'thu'  => 'Thursday',
                    'fri'  => 'Friday',
                    'sat'  => 'Saturday',
                    'sun'  => 'Sunday',
                ),
            )
        );

        $widgetOutput = $widget->render( $this->widgetName . $name, $this->getWidgetDefault( $name )  );

        return $this->divWrap( $widgetOutput, $group, 'recurring_monthly_weekday_container' );
    }

    private function renderInformation( $name, $group='' )
    {
        return '';
        $widget = new sfWidgetFormTextArea( );

        $widgetOutput = $widget->render( $this->widgetName . $name, $this->getWidgetDefault( $name )  );

        return $this->divWrap( '<span id="recurring_information_label" class="note_above">Information </span>' . $widgetOutput , $group );
    }

    private function divWrap( $output, $group='', $id='' )
    {
        if( $id )
        {
            $id = ' id="'.$id.'"';
        }

        return '<div class="field ' . $group . '"' . $id . '><div class="clearer"></div>' . $output . '</div>';
    }

    private function getWidgetDefault( $widget, $default='' )
    {
        $widgetName = preg_replace( '/\[|\]/', '', $widget );
        return isset( $this->widgetDefaultValues[ $widgetName ] ) ? $this->widgetDefaultValues[ $widgetName ] : $default ;
    }
}
