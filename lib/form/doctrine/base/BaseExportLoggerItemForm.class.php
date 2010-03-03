<?php

/**
 * ExportLoggerItem form base class.
 *
 * @method ExportLoggerItem getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseExportLoggerItemForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'export_logger_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ExportLogger'), 'add_empty' => false)),
      'item_id'          => new sfWidgetFormInputText(),
      'vendor_item_id'   => new sfWidgetFormInputText(),
      'created_at'       => new sfWidgetFormDateTime(),
      'updated_at'       => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'export_logger_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ExportLogger'))),
      'item_id'          => new sfValidatorInteger(),
      'vendor_item_id'   => new sfValidatorString(array('max_length' => 25)),
      'created_at'       => new sfValidatorDateTime(),
      'updated_at'       => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('export_logger_item[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ExportLoggerItem';
  }

}
