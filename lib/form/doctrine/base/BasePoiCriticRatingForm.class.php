<?php

/**
 * PoiCriticRating form base class.
 *
 * @method PoiCriticRating getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasePoiCriticRatingForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'              => new sfWidgetFormInputHidden(),
      'comment_subject' => new sfWidgetFormTextarea(),
      'comment_body'    => new sfWidgetFormTextarea(),
      'user_rating'     => new sfWidgetFormInputText(),
      'poi_user_id'     => new sfWidgetFormInputText(),
      'created_at'      => new sfWidgetFormDateTime(),
      'updated_at'      => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'              => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'comment_subject' => new sfValidatorString(array('max_length' => 512)),
      'comment_body'    => new sfValidatorString(array('max_length' => 65535)),
      'user_rating'     => new sfValidatorNumber(array('required' => false)),
      'poi_user_id'     => new sfValidatorInteger(),
      'created_at'      => new sfValidatorDateTime(),
      'updated_at'      => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('poi_critic_rating[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'PoiCriticRating';
  }

}
