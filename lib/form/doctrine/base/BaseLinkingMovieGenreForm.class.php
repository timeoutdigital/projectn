<?php

/**
 * LinkingMovieGenre form base class.
 *
 * @method LinkingMovieGenre getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseLinkingMovieGenreForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'             => new sfWidgetFormInputHidden(),
      'movie_genre_id' => new sfWidgetFormInputText(),
      'movie_id'       => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'movie_genre_id' => new sfValidatorInteger(),
      'movie_id'       => new sfValidatorInteger(),
    ));

    $this->widgetSchema->setNameFormat('linking_movie_genre[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'LinkingMovieGenre';
  }

}
