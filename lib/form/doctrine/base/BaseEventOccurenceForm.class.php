<?php

/**
 * EventOccurence form base class.
 *
 * @method EventOccurence getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseEventOccurenceForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'vendor_id'   => new sfWidgetFormInputText(),
      'booking_url' => new sfWidgetFormTextarea(),
      'start'       => new sfWidgetFormDate(),
      'end'         => new sfWidgetFormDate(),
      'utc_offset'  => new sfWidgetFormTime(),
      'event_id'    => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Event'), 'add_empty' => false)),
      'poi_id'      => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Poi'), 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'vendor_id'   => new sfValidatorInteger(),
      'booking_url' => new sfValidatorString(array('max_length' => 1024, 'required' => false)),
      'start'       => new sfValidatorDate(),
      'end'         => new sfValidatorDate(),
      'utc_offset'  => new sfValidatorTime(),
      'event_id'    => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Event'))),
      'poi_id'      => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Poi'))),
    ));

    $this->widgetSchema->setNameFormat('event_occurence[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'EventOccurence';
  }

}
