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
class pnWidgetFormTimePicker extends sfWidgetForm
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
       $id = $this->generateId( $name, $value );

       $output = $this->renderTag( 'input',
                                   array( 'type'  => 'input',
                                          'name'  => $name ,
                                          'id'    => $id ,
                                          'class' => 'item-filter',
                                         )
                                   );
        $script = <<<JS
        $(function()
        {
            $('#{$id}').timepickr();
        });
JS;
        return $output . $this->renderContentTag( 'script', $script, array( 'type' => 'text/javascript' ) ); ;
     }


    }
