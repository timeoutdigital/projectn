<?php

/**
 * ImportRecordErrorLogger form base class.
 *
 * @method ImportRecordErrorLogger getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseImportRecordErrorLoggerForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                => new sfWidgetFormInputHidden(),
      'model'             => new sfWidgetFormInputText(),
      'exception_class'   => new sfWidgetFormInputText(),
      'trace'             => new sfWidgetFormTextarea(),
      'message'           => new sfWidgetFormTextarea(),
      'log'               => new sfWidgetFormTextarea(),
      'serialized_object' => new sfWidgetFormTextarea(),
      'resolved'          => new sfWidgetFormInputCheckbox(),
      'import_logger_id'  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ImportLogger'), 'add_empty' => false)),
      'created_at'        => new sfWidgetFormDateTime(),
      'updated_at'        => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'model'             => new sfValidatorString(array('max_length' => 50)),
      'exception_class'   => new sfValidatorString(array('max_length' => 255)),
      'trace'             => new sfValidatorString(),
      'message'           => new sfValidatorString(),
      'log'               => new sfValidatorString(),
      'serialized_object' => new sfValidatorString(array('required' => false)),
      'resolved'          => new sfValidatorBoolean(array('required' => false)),
      'import_logger_id'  => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ImportLogger'))),
      'created_at'        => new sfValidatorDateTime(),
      'updated_at'        => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('import_record_error_logger[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ImportRecordErrorLogger';
  }

}
