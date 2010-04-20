<?php

/**
 * ExportRecordLogger form base class.
 *
 * @method ExportRecordLogger getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedInheritanceTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseExportRecordLoggerForm extends LoggerForm
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema   ['vendor_record_id'] = new sfWidgetFormInputText();
    $this->validatorSchema['vendor_record_id'] = new sfValidatorInteger();

    $this->widgetSchema   ['export_logger_id'] = new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ExportLogger'), 'add_empty' => false));
    $this->validatorSchema['export_logger_id'] = new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ExportLogger')));

    $this->widgetSchema->setNameFormat('export_record_logger[%s]');
  }

  public function getModelName()
  {
    return 'ExportRecordLogger';
  }

}
