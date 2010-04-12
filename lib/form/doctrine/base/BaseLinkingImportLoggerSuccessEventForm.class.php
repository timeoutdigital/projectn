<?php

/**
 * LinkingImportLoggerSuccessEvent form base class.
 *
 * @method LinkingImportLoggerSuccessEvent getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseLinkingImportLoggerSuccessEventForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                       => new sfWidgetFormInputHidden(),
      'import_logger_success_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ImportLoggerSuccess'), 'add_empty' => false)),
      'event_id'                 => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'                       => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'import_logger_success_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ImportLoggerSuccess'))),
      'event_id'                 => new sfValidatorInteger(),
    ));

    $this->widgetSchema->setNameFormat('linking_import_logger_success_event[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'LinkingImportLoggerSuccessEvent';
  }

}
