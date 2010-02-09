<?php

/**
 * User form base class.
 *
 * @method User getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseUserForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                 => new sfWidgetFormInputHidden(),
      'vendor_id'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'), 'add_empty' => false)),
      'user_name'          => new sfWidgetFormInputText(),
      'user_reputation'    => new sfWidgetFormInputText(),
      'user_infomation'    => new sfWidgetFormTextarea(),
      'comments_relevance' => new sfWidgetFormInputText(),
      'specialty'          => new sfWidgetFormInputText(),
      'created_at'         => new sfWidgetFormDateTime(),
      'updated_at'         => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                 => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'vendor_id'          => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'))),
      'user_name'          => new sfValidatorString(array('max_length' => 32)),
      'user_reputation'    => new sfValidatorInteger(array('required' => false)),
      'user_infomation'    => new sfValidatorString(),
      'comments_relevance' => new sfValidatorNumber(array('required' => false)),
      'specialty'          => new sfValidatorString(array('max_length' => 128, 'required' => false)),
      'created_at'         => new sfValidatorDateTime(),
      'updated_at'         => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('user[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'User';
  }

}
