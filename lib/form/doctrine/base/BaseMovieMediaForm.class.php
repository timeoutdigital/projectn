<?php

/**
 * MovieMedia form base class.
 *
 * @method MovieMedia getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseMovieMediaForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'media_url'  => new sfWidgetFormTextarea(),
      'mime_type'  => new sfWidgetFormInputText(),
      'movie_id'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Movie'), 'add_empty' => false)),
      'created_at' => new sfWidgetFormDateTime(),
      'updated_at' => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'media_url'  => new sfValidatorString(array('max_length' => 1024)),
      'mime_type'  => new sfValidatorString(array('max_length' => 255)),
      'movie_id'   => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Movie'))),
      'created_at' => new sfValidatorDateTime(),
      'updated_at' => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('movie_media[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'MovieMedia';
  }

}
