<?php

/**
 * EventTranslation form base class.
 *
 * @method EventTranslation getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseEventTranslationForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                => new sfWidgetFormInputHidden(),
      'name'              => new sfWidgetFormTextarea(),
      'vendor_category'   => new sfWidgetFormTextarea(),
      'short_description' => new sfWidgetFormTextarea(),
      'description'       => new sfWidgetFormTextarea(),
      'booking_url'       => new sfWidgetFormTextarea(),
      'lang'              => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'id'                => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'name'              => new sfValidatorString(array('max_length' => 256)),
      'vendor_category'   => new sfValidatorString(array('max_length' => 256)),
      'short_description' => new sfValidatorString(array('max_length' => 1024, 'required' => false)),
      'description'       => new sfValidatorString(array('max_length' => 65535, 'required' => false)),
      'booking_url'       => new sfValidatorString(array('max_length' => 1024, 'required' => false)),
      'lang'              => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'lang', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('event_translation[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'EventTranslation';
  }

}
