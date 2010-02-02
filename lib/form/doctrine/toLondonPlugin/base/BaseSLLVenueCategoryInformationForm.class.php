<?php

/**
 * SLLVenueCategoryInformation form base class.
 *
 * @method SLLVenueCategoryInformation getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseSLLVenueCategoryInformationForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'category_id'      => new sfWidgetFormInputHidden(),
      'venue_id'         => new sfWidgetFormInputHidden(),
      'annotation'       => new sfWidgetFormTextarea(),
      'price_export'     => new sfWidgetFormTextarea(),
      'telephone_export' => new sfWidgetFormTextarea(),
      'times_export'     => new sfWidgetFormTextarea(),
      'url_export'       => new sfWidgetFormInputText(),
      'food_served'      => new sfWidgetFormInputText(),
      'free_venue'       => new sfWidgetFormInputText(),
      'late_night'       => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'category_id'      => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'category_id', 'required' => false)),
      'venue_id'         => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'venue_id', 'required' => false)),
      'annotation'       => new sfValidatorString(array('max_length' => 2147483647, 'required' => false)),
      'price_export'     => new sfValidatorString(array('max_length' => 2147483647, 'required' => false)),
      'telephone_export' => new sfValidatorString(array('max_length' => 2147483647, 'required' => false)),
      'times_export'     => new sfValidatorString(array('max_length' => 2147483647, 'required' => false)),
      'url_export'       => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'food_served'      => new sfValidatorInteger(array('required' => false)),
      'free_venue'       => new sfValidatorInteger(array('required' => false)),
      'late_night'       => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sll_venue_category_information[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SLLVenueCategoryInformation';
  }

}
