<?php

/**
 * ImportLoggerError filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseImportLoggerErrorFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'trace'            => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'log'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'type'             => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'message'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'import_logger_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ImportLogger'), 'add_empty' => true)),
      'created_at'       => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'       => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
    ));

    $this->setValidators(array(
      'trace'            => new sfValidatorPass(array('required' => false)),
      'log'              => new sfValidatorPass(array('required' => false)),
      'type'             => new sfValidatorPass(array('required' => false)),
      'message'          => new sfValidatorPass(array('required' => false)),
      'import_logger_id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('ImportLogger'), 'column' => 'id')),
      'created_at'       => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'       => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
    ));

    $this->widgetSchema->setNameFormat('import_logger_error_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ImportLoggerError';
  }

  public function getFields()
  {
    return array(
      'id'               => 'Number',
      'trace'            => 'Text',
      'log'              => 'Text',
      'type'             => 'Text',
      'message'          => 'Text',
      'import_logger_id' => 'ForeignKey',
      'created_at'       => 'Date',
      'updated_at'       => 'Date',
    );
  }
}
