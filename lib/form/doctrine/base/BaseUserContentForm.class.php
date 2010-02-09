<?php

/**
 * UserContent form base class.
 *
 * @method UserContent getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseUserContentForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'              => new sfWidgetFormInputHidden(),
      'vendor_ucid'     => new sfWidgetFormInputText(),
      'comment_subject' => new sfWidgetFormTextarea(),
      'comment_body'    => new sfWidgetFormTextarea(),
      'user_rating'     => new sfWidgetFormInputText(),
      'user_id'         => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('User'), 'add_empty' => false)),
      'poi_id'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Poi'), 'add_empty' => false)),
      'created_at'      => new sfWidgetFormDateTime(),
      'updated_at'      => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'              => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'vendor_ucid'     => new sfValidatorString(array('max_length' => 32)),
      'comment_subject' => new sfValidatorString(array('max_length' => 512)),
      'comment_body'    => new sfValidatorString(array('max_length' => 65535)),
      'user_rating'     => new sfValidatorNumber(array('required' => false)),
      'user_id'         => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('User'))),
      'poi_id'          => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Poi'))),
      'created_at'      => new sfValidatorDateTime(),
      'updated_at'      => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('user_content[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'UserContent';
  }

}
