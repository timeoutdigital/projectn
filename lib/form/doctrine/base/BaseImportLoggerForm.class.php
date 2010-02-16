<?php

/**
 * ImportLogger form base class.
 *
 * @method ImportLogger getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseImportLoggerForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'            => new sfWidgetFormInputHidden(),
      'total_inserts' => new sfWidgetFormInputText(),
      'total_updates' => new sfWidgetFormInputText(),
      'vendor_id'     => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'), 'add_empty' => false)),
      'type'          => new sfWidgetFormChoice(array('choices' => array('movie' => 'movie', 'poi' => 'poi', 'event' => 'event'))),
      'total_time'    => new sfWidgetFormTime(),
      'created_at'    => new sfWidgetFormDateTime(),
      'updated_at'    => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'            => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'total_inserts' => new sfValidatorInteger(),
      'total_updates' => new sfValidatorInteger(),
      'vendor_id'     => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'))),
      'type'          => new sfValidatorChoice(array('choices' => array('movie' => 'movie', 'poi' => 'poi', 'event' => 'event'))),
      'total_time'    => new sfValidatorTime(),
      'created_at'    => new sfValidatorDateTime(),
      'updated_at'    => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('import_logger[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ImportLogger';
  }

}
