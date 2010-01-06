<?php

/**
 * PoiTranslation form base class.
 *
 * @method PoiTranslation getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasePoiTranslationForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'        => new sfWidgetFormInputHidden(),
      'house_no'  => new sfWidgetFormInputText(),
      'street'    => new sfWidgetFormInputText(),
      'city'      => new sfWidgetFormInputText(),
      'district'  => new sfWidgetFormInputText(),
      'country'   => new sfWidgetFormInputText(),
      'zips'      => new sfWidgetFormInputText(),
      'extension' => new sfWidgetFormInputText(),
      'lang'      => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'id'        => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'house_no'  => new sfValidatorString(array('max_length' => 16, 'required' => false)),
      'street'    => new sfValidatorString(array('max_length' => 128)),
      'city'      => new sfValidatorString(array('max_length' => 32)),
      'district'  => new sfValidatorString(array('max_length' => 128, 'required' => false)),
      'country'   => new sfValidatorString(array('max_length' => 3)),
      'zips'      => new sfValidatorString(array('max_length' => 16, 'required' => false)),
      'extension' => new sfValidatorString(array('max_length' => 128, 'required' => false)),
      'lang'      => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'lang', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('poi_translation[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'PoiTranslation';
  }

}
