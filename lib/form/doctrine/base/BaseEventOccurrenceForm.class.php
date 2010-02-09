<?php

/**
 * EventOccurrence form base class.
 *
 * @method EventOccurrence getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseEventOccurrenceForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                         => new sfWidgetFormInputHidden(),
      'vendor_event_occurrence_id' => new sfWidgetFormInputText(),
      'booking_url'                => new sfWidgetFormTextarea(),
      'start'                      => new sfWidgetFormInputText(),
      'end'                        => new sfWidgetFormInputText(),
      'utc_offset'                 => new sfWidgetFormInputText(),
      'event_id'                   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Event'), 'add_empty' => false)),
      'poi_id'                     => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Poi'), 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'                         => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'vendor_event_occurrence_id' => new sfValidatorString(array('max_length' => 50)),
      'booking_url'                => new sfValidatorString(array('max_length' => 1024, 'required' => false)),
      'start'                      => new sfValidatorPass(),
      'end'                        => new sfValidatorPass(array('required' => false)),
      'utc_offset'                 => new sfValidatorString(array('max_length' => 9)),
      'event_id'                   => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Event'))),
      'poi_id'                     => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Poi'))),
    ));

    $this->widgetSchema->setNameFormat('event_occurrence[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'EventOccurrence';
  }

}
