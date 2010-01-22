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
      'id'        => new sfWidgetFormInputHidden(),
      'map_from'  => new sfWidgetFormInputText(),
      'map_to'    => new sfWidgetFormInputText(),
      'vendor_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'), 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'        => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'map_from'  => new sfValidatorString(array('max_length' => 50)),
      'map_to'    => new sfValidatorString(array('max_length' => 50)),
      'vendor_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'))),
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
