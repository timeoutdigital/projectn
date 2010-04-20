<?php

/**
 * ImportRecordLogger form base class.
 *
 * @method ImportRecordLogger getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedInheritanceTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseImportRecordLoggerForm extends RecordLoggerForm
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema   ['operation'] = new sfWidgetFormChoice(array('choices' => array('insert' => 'insert', 'update' => 'update', 'existing' => 'existing', 'delete' => 'delete')));
    $this->validatorSchema['operation'] = new sfValidatorChoice(array('choices' => array('insert' => 'insert', 'update' => 'update', 'existing' => 'existing', 'delete' => 'delete')));

    $this->widgetSchema   ['log'] = new sfWidgetFormTextarea();
    $this->validatorSchema['log'] = new sfValidatorString(array('required' => false));

    $this->widgetSchema   ['import_logger_id'] = new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ImportLogger'), 'add_empty' => false));
    $this->validatorSchema['import_logger_id'] = new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ImportLogger')));

    $this->widgetSchema->setNameFormat('import_record_logger[%s]');
  }

  public function getModelName()
  {
    return 'ImportRecordLogger';
  }

}
