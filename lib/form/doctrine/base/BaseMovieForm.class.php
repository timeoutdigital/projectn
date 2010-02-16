<?php

/**
 * Movie form base class.
 *
 * @method Movie getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseMovieForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                => new sfWidgetFormInputHidden(),
      'vendor_id'         => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'), 'add_empty' => false)),
      'vendor_movie_id'   => new sfWidgetFormInputText(),
      'name'              => new sfWidgetFormInputText(),
      'plot'              => new sfWidgetFormTextarea(),
      'review'            => new sfWidgetFormTextarea(),
      'url'               => new sfWidgetFormTextarea(),
      'rating'            => new sfWidgetFormInputText(),
      'age_rating'        => new sfWidgetFormInputText(),
      'utf_offset'        => new sfWidgetFormInputText(),
      'poi_id'            => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Poi'), 'add_empty' => true)),
      'created_at'        => new sfWidgetFormDateTime(),
      'updated_at'        => new sfWidgetFormDateTime(),
      'movie_genres_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'MovieGenre')),
    ));

    $this->setValidators(array(
      'id'                => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'vendor_id'         => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'))),
      'vendor_movie_id'   => new sfValidatorString(array('max_length' => 25)),
      'name'              => new sfValidatorString(array('max_length' => 255)),
      'plot'              => new sfValidatorString(array('max_length' => 65535, 'required' => false)),
      'review'            => new sfValidatorString(array('max_length' => 65535, 'required' => false)),
      'url'               => new sfValidatorString(array('max_length' => 1024, 'required' => false)),
      'rating'            => new sfValidatorNumber(array('required' => false)),
      'age_rating'        => new sfValidatorString(array('max_length' => 32, 'required' => false)),
      'utf_offset'        => new sfValidatorString(array('max_length' => 9)),
      'poi_id'            => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Poi'), 'required' => false)),
      'created_at'        => new sfValidatorDateTime(),
      'updated_at'        => new sfValidatorDateTime(),
      'movie_genres_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'MovieGenre', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('movie[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Movie';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['movie_genres_list']))
    {
      $this->setDefault('movie_genres_list', $this->object->MovieGenres->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
    $this->saveMovieGenresList($con);

    parent::doSave($con);
  }

  public function saveMovieGenresList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['movie_genres_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->MovieGenres->getPrimaryKeys();
    $values = $this->getValue('movie_genres_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('MovieGenres', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('MovieGenres', array_values($link));
    }
  }

}
