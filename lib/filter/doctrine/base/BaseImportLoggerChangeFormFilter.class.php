<?php

/**
 * ImportLoggerChange filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseImportLoggerChangeFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'type'             => new sfWidgetFormChoice(array('choices' => array('' => '', 'update' => 'update', 'delete' => 'delete'))),
      'log'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'import_logger_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ImportLogger'), 'add_empty' => true)),
      'created_at'       => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'       => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
    ));

    $this->setValidators(array(
      'type'             => new sfValidatorChoice(array('required' => false, 'choices' => array('update' => 'update', 'delete' => 'delete'))),
      'log'              => new sfValidatorPass(array('required' => false)),
      'import_logger_id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('ImportLogger'), 'column' => 'id')),
      'created_at'       => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'       => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
    ));

    $this->widgetSchema->setNameFormat('import_logger_change_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ImportLoggerChange';
  }

  public function getFields()
  {
    return array(
      'id'               => 'Number',
      'type'             => 'Enum',
      'log'              => 'Text',
      'import_logger_id' => 'ForeignKey',
      'created_at'       => 'Date',
      'updated_at'       => 'Date',
    );
  }
}
