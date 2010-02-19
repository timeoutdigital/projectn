<?php

/**
 * ImportLoggerError form base class.
 *
 * @method ImportLoggerError getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseImportLoggerErrorForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                => new sfWidgetFormInputHidden(),
      'trace'             => new sfWidgetFormTextarea(),
      'log'               => new sfWidgetFormTextarea(),
      'type'              => new sfWidgetFormTextarea(),
      'message'           => new sfWidgetFormTextarea(),
      'serialized_object' => new sfWidgetFormTextarea(),
      'import_logger_id'  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ImportLogger'), 'add_empty' => false)),
      'resolved'          => new sfWidgetFormInputCheckbox(),
      'created_at'        => new sfWidgetFormDateTime(),
      'updated_at'        => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'trace'             => new sfValidatorString(),
      'log'               => new sfValidatorString(),
      'type'              => new sfValidatorString(),
      'message'           => new sfValidatorString(),
      'serialized_object' => new sfValidatorString(),
      'import_logger_id'  => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ImportLogger'))),
      'resolved'          => new sfValidatorBoolean(array('required' => false)),
      'created_at'        => new sfValidatorDateTime(),
      'updated_at'        => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('import_logger_error[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ImportLoggerError';
  }

}
