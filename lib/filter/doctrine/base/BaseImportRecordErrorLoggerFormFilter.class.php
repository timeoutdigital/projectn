<?php

/**
 * ImportRecordErrorLogger filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseImportRecordErrorLoggerFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'model'             => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'exception_class'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'trace'             => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'message'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'log'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'serialized_object' => new sfWidgetFormFilterInput(),
      'resolved'          => new sfWidgetFormChoice(array('choices' => array('' => 'yes or no', 1 => 'yes', 0 => 'no'))),
      'import_logger_id'  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ImportLogger'), 'add_empty' => true)),
      'created_at'        => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'        => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
    ));

    $this->setValidators(array(
      'model'             => new sfValidatorPass(array('required' => false)),
      'exception_class'   => new sfValidatorPass(array('required' => false)),
      'trace'             => new sfValidatorPass(array('required' => false)),
      'message'           => new sfValidatorPass(array('required' => false)),
      'log'               => new sfValidatorPass(array('required' => false)),
      'serialized_object' => new sfValidatorPass(array('required' => false)),
      'resolved'          => new sfValidatorChoice(array('required' => false, 'choices' => array('', 1, 0))),
      'import_logger_id'  => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('ImportLogger'), 'column' => 'id')),
      'created_at'        => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'        => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
    ));

    $this->widgetSchema->setNameFormat('import_record_error_logger_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ImportRecordErrorLogger';
  }

  public function getFields()
  {
    return array(
      'id'                => 'Number',
      'model'             => 'Text',
      'exception_class'   => 'Text',
      'trace'             => 'Text',
      'message'           => 'Text',
      'log'               => 'Text',
      'serialized_object' => 'Text',
      'resolved'          => 'Boolean',
      'import_logger_id'  => 'ForeignKey',
      'created_at'        => 'Date',
      'updated_at'        => 'Date',
    );
  }
}
