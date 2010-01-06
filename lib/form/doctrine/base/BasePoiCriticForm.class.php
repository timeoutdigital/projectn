<?php

/**
 * PoiCritic form base class.
 *
 * @method PoiCritic getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasePoiCriticForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                 => new sfWidgetFormInputHidden(),
      'user_name'          => new sfWidgetFormInputText(),
      'user_reputation'    => new sfWidgetFormInputText(),
      'user_infomation'    => new sfWidgetFormTextarea(),
      'total_comments'     => new sfWidgetFormInputText(),
      'comments_relevance' => new sfWidgetFormInputText(),
      'specialty'          => new sfWidgetFormInputText(),
      'created_at'         => new sfWidgetFormDateTime(),
      'updated_at'         => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                 => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'user_name'          => new sfValidatorString(array('max_length' => 32)),
      'user_reputation'    => new sfValidatorInteger(array('required' => false)),
      'user_infomation'    => new sfValidatorString(),
      'total_comments'     => new sfValidatorInteger(),
      'comments_relevance' => new sfValidatorNumber(array('required' => false)),
      'specialty'          => new sfValidatorString(array('max_length' => 128, 'required' => false)),
      'created_at'         => new sfValidatorDateTime(),
      'updated_at'         => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('poi_critic[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'PoiCritic';
  }

}
