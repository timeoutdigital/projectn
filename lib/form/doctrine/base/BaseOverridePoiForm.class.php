<?php

/**
 * OverridePoi form base class.
 *
 * @method OverridePoi getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseOverridePoiForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'override_id' => new sfWidgetFormInputHidden(),
      'poi_id'      => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'override_id' => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'override_id', 'required' => false)),
      'poi_id'      => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'poi_id', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('override_poi[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'OverridePoi';
  }

}
