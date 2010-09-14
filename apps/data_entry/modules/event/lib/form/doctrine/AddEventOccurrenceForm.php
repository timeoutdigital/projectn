<?php


class AddEventOccurrenceForm extends BaseFormDoctrine
{

  public function configure()
  {
  /*
    $this->widgetSchema[ 'collection' ]      = new sfWidgetFormDate(array());
    $this->validatorSchema[ 'collection' ]   = new sfValidatorDate(array( 'required' => false ));
  */
      $this->widgetSchema['poi_id']->setOption('renderer_class', 'sfWidgetFormDoctrineJQueryAutocompleter');
      $this->widgetSchema['poi_id']->setOption('renderer_options', array(
        'model' => 'Poi',
        'url'   => sfContext::getInstance()->getRequest()->getScriptName() . '/event/ajaxPoiList',
      ));

  }

  public function getModelName()
  {
    return 'Event';
  }

   public function __construct( $event  = null )
    {
        //var_dump( get_class( $event ) );
        $this->event = $event;
        parent::__construct( );
    }

  public function setup()
  {
    $this->setWidgets( array(
      'poi_id'            => new sfWidgetFormDoctrineChoice( array('model' =>   'Poi' , 'add_empty' => false)),
      'start_date'        => new pnWidgetFormDatePicker(  array(), array( 'update' => 'event_AddEventOccurrenceForm_end_date') ),
      'end_date'          => new pnWidgetFormDatePicker( ),
      'start_time'        => new pnWidgetFormTimePicker( array(), array( 'event' => $this->event)  ),
      'end_time'          => new pnWidgetFormTimePicker( array(), array( 'event' => $this->event)  ),
      'recurring_dates'   => new toWidgetFormEventRecurring( array(), array( 'event' => $this->event)  ),
    ));

    $this->setValidators( array(
      'poi_id'          => new sfValidatorPass(),
      'start_date'      => new sfValidatorDate(array('required' => false)),
      'end_date'        => new sfValidatorDate(array('required' => false)),
      'start_time'      => new sfValidatorTime(array('required' => false)),
      'end_time'        => new sfValidatorTime(array('required' => false)),
      'recurring_dates' => new toValidatorFormEventRecurring( array('required' => false) ),
    ));

    $this->widgetSchema->setNameFormat('event-occurrence[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }
}
