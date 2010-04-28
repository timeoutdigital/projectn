<?php

/**
 * SLLEventImage form base class.
 *
 * @method SLLEventImage getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseSLLEventImageForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'item_id'    => new sfWidgetFormInputHidden(),
      'item_type'  => new sfWidgetFormInputHidden(),
      'image_id'   => new sfWidgetFormInputHidden(),
      'sort_index' => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'item_id'    => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'item_id', 'required' => false)),
      'item_type'  => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'item_type', 'required' => false)),
      'image_id'   => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'image_id', 'required' => false)),
      'sort_index' => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'sort_index', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sll_event_image[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SLLEventImage';
  }

}
