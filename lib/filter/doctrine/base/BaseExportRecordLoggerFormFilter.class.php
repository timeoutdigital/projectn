<?php

/**
 * ExportRecordLogger filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedInheritanceTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseExportRecordLoggerFormFilter extends LoggerFormFilter
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema   ['vendor_record_id'] = new sfWidgetFormFilterInput(array('with_empty' => false));
    $this->validatorSchema['vendor_record_id'] = new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false)));

    $this->widgetSchema   ['export_logger_id'] = new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ExportLogger'), 'add_empty' => true));
    $this->validatorSchema['export_logger_id'] = new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('ExportLogger'), 'column' => 'id'));

    $this->widgetSchema->setNameFormat('export_record_logger_filters[%s]');
  }

  public function getModelName()
  {
    return 'ExportRecordLogger';
  }

  public function getFields()
  {
    return array_merge(parent::getFields(), array(
      'vendor_record_id' => 'Number',
      'export_logger_id' => 'ForeignKey',
    ));
  }
}
