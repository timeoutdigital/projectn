<?php

/**
 * Media form base class.
 *
 * @method Media getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseMediaForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                 => new sfWidgetFormInputHidden(),
      'ident'              => new sfWidgetFormInputText(),
      'url'                => new sfWidgetFormTextarea(),
      'mime_type'          => new sfWidgetFormInputText(),
      'file_last_modified' => new sfWidgetFormInputText(),
      'etag'               => new sfWidgetFormInputText(),
      'content_length'     => new sfWidgetFormInputText(),
      'created_at'         => new sfWidgetFormDateTime(),
      'updated_at'         => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                 => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'ident'              => new sfValidatorString(array('max_length' => 32)),
      'url'                => new sfValidatorString(array('max_length' => 1024)),
      'mime_type'          => new sfValidatorString(array('max_length' => 255)),
      'file_last_modified' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'etag'               => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'content_length'     => new sfValidatorInteger(array('required' => false)),
      'created_at'         => new sfValidatorDateTime(),
      'updated_at'         => new sfValidatorDateTime(),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'Media', 'column' => array('ident')))
    );

    $this->widgetSchema->setNameFormat('media[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Media';
  }

}
