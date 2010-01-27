<?php

/**
 * EventCategoryMapping form base class.
 *
 * @method EventCategoryMapping getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseEventCategoryMappingForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'map_from_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('VendorEventCategory'), 'add_empty' => false)),
      'map_to_id'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('EventCategory'), 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'map_from_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('VendorEventCategory'))),
      'map_to_id'   => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('EventCategory'))),
    ));

    $this->widgetSchema->setNameFormat('event_category_mapping[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'EventCategoryMapping';
  }

}