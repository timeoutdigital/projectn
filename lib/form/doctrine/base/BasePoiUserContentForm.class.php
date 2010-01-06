<?php

/**
 * PoiUserContent form base class.
 *
 * @method PoiUserContent getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasePoiUserContentForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'              => new sfWidgetFormInputHidden(),
      'vender_ucid'     => new sfWidgetFormInputText(),
      'comment_subject' => new sfWidgetFormTextarea(),
      'comment_body'    => new sfWidgetFormTextarea(),
      'user_rating'     => new sfWidgetFormInputText(),
      'poi_user_id'     => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('PoiUser'), 'add_empty' => false)),
      'poi_id'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Poi'), 'add_empty' => false)),
      'created_at'      => new sfWidgetFormDateTime(),
      'updated_at'      => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'              => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'vender_ucid'     => new sfValidatorString(array('max_length' => 32)),
      'comment_subject' => new sfValidatorString(array('max_length' => 512)),
      'comment_body'    => new sfValidatorString(array('max_length' => 65535)),
      'user_rating'     => new sfValidatorNumber(array('required' => false)),
      'poi_user_id'     => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('PoiUser'))),
      'poi_id'          => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Poi'))),
      'created_at'      => new sfValidatorDateTime(),
      'updated_at'      => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('poi_user_content[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'PoiUserContent';
  }

}
