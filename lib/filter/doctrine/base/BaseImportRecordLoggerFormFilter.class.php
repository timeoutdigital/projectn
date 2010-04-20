<?php

/**
 * ImportRecordLogger filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedInheritanceTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseImportRecordLoggerFormFilter extends RecordLoggerFormFilter
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema   ['operation'] = new sfWidgetFormChoice(array('choices' => array('' => '', 'insert' => 'insert', 'update' => 'update', 'existing' => 'existing', 'delete' => 'delete')));
    $this->validatorSchema['operation'] = new sfValidatorChoice(array('required' => false, 'choices' => array('insert' => 'insert', 'update' => 'update', 'existing' => 'existing', 'delete' => 'delete')));

    $this->widgetSchema   ['log'] = new sfWidgetFormFilterInput();
    $this->validatorSchema['log'] = new sfValidatorPass(array('required' => false));

    $this->widgetSchema   ['import_logger_id'] = new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ImportLogger'), 'add_empty' => true));
    $this->validatorSchema['import_logger_id'] = new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('ImportLogger'), 'column' => 'id'));

    $this->widgetSchema->setNameFormat('import_record_logger_filters[%s]');
  }

  public function getModelName()
  {
    return 'ImportRecordLogger';
  }

  public function getFields()
  {
    return array_merge(parent::getFields(), array(
      'operation' => 'Enum',
      'log' => 'Text',
      'import_logger_id' => 'ForeignKey',
    ));
  }
}
