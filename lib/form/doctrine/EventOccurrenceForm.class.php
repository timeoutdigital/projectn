<?php

/**
 * EventOccurrence form.
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class EventOccurrenceForm extends BaseEventOccurrenceForm
{
  public function configure()
  {
      //@todo try to move this into app..
   

      $this->widgetSchema['event_occurrence_delete'] = new sfWidgetFormInputCheckbox();
      $this->widgetSchema['event_occurrence_delete']->setLabel( 'Delete' );
      $this->validatorSchema['event_occurrence_delete'] = new sfValidatorPass();

      $this->widgetSchema['poi_id']->setOption('renderer_class', 'sfWidgetFormDoctrineJQueryAutocompleter');
      $this->widgetSchema['poi_id']->setOption('renderer_options', array(
        'model' => 'Poi',
        'url'   => sfContext::getInstance()->getRequest()->getScriptName() . '/event/ajaxPoiList',
      ));

      $this->useFields( array( 'start_date', 'start_time', 'end_date', 'end_time', 'poi_id', 'event_occurrence_delete' ) );

      $this->mergePostValidator(new EventOccurrenceDataEntryValidatorSchema());
  }

  
}
