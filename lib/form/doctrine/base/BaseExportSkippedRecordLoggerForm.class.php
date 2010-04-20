<?php

/**
 * ExportSkippedRecordLogger form base class.
 *
 * @method ExportSkippedRecordLogger getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseExportSkippedRecordLoggerForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'record_id'        => new sfWidgetFormInputText(),
      'model'            => new sfWidgetFormInputText(),
      'vendor_record_id' => new sfWidgetFormInputText(),
      'log'              => new sfWidgetFormTextarea(),
      'export_logger_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ExportLogger'), 'add_empty' => false)),
      'created_at'       => new sfWidgetFormDateTime(),
      'updated_at'       => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'record_id'        => new sfValidatorInteger(),
      'model'            => new sfValidatorString(array('max_length' => 50)),
      'vendor_record_id' => new sfValidatorInteger(),
      'log'              => new sfValidatorString(),
      'export_logger_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ExportLogger'))),
      'created_at'       => new sfValidatorDateTime(),
      'updated_at'       => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('export_skipped_record_logger[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ExportSkippedRecordLogger';
  }

}
