<?php

/**
 * PoiChangesLog form base class.
 *
 * @method PoiChangesLog getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasePoiChangesLogForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'log'        => new sfWidgetFormTextarea(),
      'poi_id'     => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Poi'), 'add_empty' => false)),
      'created_at' => new sfWidgetFormDateTime(),
      'updated_at' => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'log'        => new sfValidatorString(array('max_length' => 500)),
      'poi_id'     => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Poi'))),
      'created_at' => new sfValidatorDateTime(),
      'updated_at' => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('poi_changes_log[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'PoiChangesLog';
  }

}