<?php
/**
 * pnWidgetFormOccurrenceCollection represents an  doctrine_collection of EventOccurrences
 *
 * @package symfony
 * @subpackage widget.lib
 *
 * @author Emre Basala <emrebasala@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class pnWidgetFormOccurrenceCollection extends sfWidgetForm
{
    /**
    * Constructor.
    *
    * Available options:
    *
    *  * type: The widget type
    *
    * @param array $options     An array of options
    * @param array $attributes  An array of default HTML attributes
    *
    * @see sfWidgetForm
    */
    protected $event ;

    protected function configure($options = array(), $attributes = array())
    {
        $this->addOption( 'format', ' %controls% %js% %clearer% ' );

        //option to decide if we need 2 calendars for  a date-interval
        parent::configure( $options, $attributes );
    }


    public function render($name, $value = null, $attributes = array(), $errors = array())
    {

        $event = $this->attributes[ 'event' ];
        if( !$event )
        {
            return "No Occurrences for this event!";
        }

        $output = '';
        foreach ( $event[ 'EventOccurrence' ] as $occurrence )
        {
            $output .= 'On ' . $occurrence[ 'start_date'  ]  .'  '. $occurrence[ 'start_time'  ] . ' at ' . $occurrence[ 'Poi' ][ 'poi_name' ] .PHP_EOL;
        }
        return $output;
    }
}
