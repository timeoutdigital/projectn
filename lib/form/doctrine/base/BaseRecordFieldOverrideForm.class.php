<?php

/**
 * RecordFieldOverride form base class.
 *
 * @method RecordFieldOverride getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseRecordFieldOverrideForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'             => new sfWidgetFormInputHidden(),
      'record_id'      => new sfWidgetFormInputText(),
      'field'          => new sfWidgetFormInputText(),
      'received_value' => new sfWidgetFormTextarea(),
      'edited_value'   => new sfWidgetFormTextarea(),
      'is_active'      => new sfWidgetFormInputCheckbox(),
      'created_at'     => new sfWidgetFormDateTime(),
      'updated_at'     => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'record_id'      => new sfValidatorInteger(),
      'field'          => new sfValidatorString(array('max_length' => 50)),
      'received_value' => new sfValidatorString(array('required' => false)),
      'edited_value'   => new sfValidatorString(array('required' => false)),
      'is_active'      => new sfValidatorBoolean(array('required' => false)),
      'created_at'     => new sfValidatorDateTime(),
      'updated_at'     => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('record_field_override[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'RecordFieldOverride';
  }

}
