<?php

/**
 * SLLCategory form base class.
 *
 * @method SLLCategory getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseSLLCategoryForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                 => new sfWidgetFormInputHidden(),
      'parent_category_id' => new sfWidgetFormInputText(),
      'name'               => new sfWidgetFormInputText(),
      'status'             => new sfWidgetFormInputText(),
      'name_url'           => new sfWidgetFormInputText(),
      'tagline'            => new sfWidgetFormTextarea(),
      'last_listing_date'  => new sfWidgetFormDate(),
      'lft'                => new sfWidgetFormInputText(),
      'rgt'                => new sfWidgetFormInputText(),
      'level'              => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'                 => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'parent_category_id' => new sfValidatorInteger(),
      'name'               => new sfValidatorString(array('max_length' => 255)),
      'status'             => new sfValidatorInteger(array('required' => false)),
      'name_url'           => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'tagline'            => new sfValidatorString(array('max_length' => 2147483647, 'required' => false)),
      'last_listing_date'  => new sfValidatorDate(array('required' => false)),
      'lft'                => new sfValidatorInteger(array('required' => false)),
      'rgt'                => new sfValidatorInteger(array('required' => false)),
      'level'              => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sll_category[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SLLCategory';
  }

}
