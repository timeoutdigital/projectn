<?php

/**
 * OverrideMovie form base class.
 *
 * @method OverrideMovie getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseOverrideMovieForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'override_id' => new sfWidgetFormInputHidden(),
      'movie_id'    => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'override_id' => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'override_id', 'required' => false)),
      'movie_id'    => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'movie_id', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('override_movie[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'OverrideMovie';
  }

}
