<?php

/**
 * ExportSkippedRecordLogger filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseExportSkippedRecordLoggerFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'record_id'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'model'            => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'vendor_record_id' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'log'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'export_logger_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ExportLogger'), 'add_empty' => true)),
      'created_at'       => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'       => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
    ));

    $this->setValidators(array(
      'record_id'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'model'            => new sfValidatorPass(array('required' => false)),
      'vendor_record_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'log'              => new sfValidatorPass(array('required' => false)),
      'export_logger_id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('ExportLogger'), 'column' => 'id')),
      'created_at'       => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'       => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
    ));

    $this->widgetSchema->setNameFormat('export_skipped_record_logger_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ExportSkippedRecordLogger';
  }

  public function getFields()
  {
    return array(
      'id'               => 'Number',
      'record_id'        => 'Number',
      'model'            => 'Text',
      'vendor_record_id' => 'Number',
      'log'              => 'Text',
      'export_logger_id' => 'ForeignKey',
      'created_at'       => 'Date',
      'updated_at'       => 'Date',
    );
  }
}
