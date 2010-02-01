<?php

/**
 * SLLEventCategoryMapping form base class.
 *
 * @method SLLEventCategoryMapping getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseSLLEventCategoryMappingForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'category_id'         => new sfWidgetFormInputHidden(),
      'event_id'            => new sfWidgetFormInputHidden(),
      'annotation_behavior' => new sfWidgetFormInputText(),
      'annotation'          => new sfWidgetFormTextarea(),
      'master_category_id'  => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'category_id'         => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'category_id', 'required' => false)),
      'event_id'            => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'event_id', 'required' => false)),
      'annotation_behavior' => new sfValidatorInteger(array('required' => false)),
      'annotation'          => new sfValidatorString(array('max_length' => 2147483647, 'required' => false)),
      'master_category_id'  => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sll_event_category_mapping[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SLLEventCategoryMapping';
  }

}
