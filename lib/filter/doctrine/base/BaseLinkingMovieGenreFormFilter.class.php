<?php

/**
 * LinkingMovieGenre filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseLinkingMovieGenreFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'movie_genre_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('MovieGenre'), 'add_empty' => true)),
      'movie_id'       => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Movie'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'movie_genre_id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('MovieGenre'), 'column' => 'id')),
      'movie_id'       => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Movie'), 'column' => 'id')),
    ));

    $this->widgetSchema->setNameFormat('linking_movie_genre_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'LinkingMovieGenre';
  }

  public function getFields()
  {
    return array(
      'id'             => 'Number',
      'movie_genre_id' => 'ForeignKey',
      'movie_id'       => 'ForeignKey',
    );
  }
}
