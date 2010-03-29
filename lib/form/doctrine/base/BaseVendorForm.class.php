<?php

/**
 * Vendor form base class.
 *
 * @method Vendor getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseVendorForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                     => new sfWidgetFormInputHidden(),
      'city'                   => new sfWidgetFormInputText(),
      'language'               => new sfWidgetFormInputText(),
      'time_zone'              => new sfWidgetFormInputText(),
      'inernational_dial_code' => new sfWidgetFormInputText(),
      'airport_code'           => new sfWidgetFormInputText(),
      'country_code'           => new sfWidgetFormInputText(),
      'created_at'             => new sfWidgetFormDateTime(),
      'updated_at'             => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                     => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'city'                   => new sfValidatorString(array('max_length' => 15)),
      'language'               => new sfValidatorString(array('max_length' => 10)),
      'time_zone'              => new sfValidatorString(array('max_length' => 50)),
      'inernational_dial_code' => new sfValidatorString(array('max_length' => 5, 'required' => false)),
      'airport_code'           => new sfValidatorString(array('max_length' => 3)),
      'country_code'           => new sfValidatorString(array('max_length' => 2)),
      'created_at'             => new sfValidatorDateTime(),
      'updated_at'             => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('vendor[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Vendor';
  }

}
