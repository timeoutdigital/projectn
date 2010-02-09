<?php

/**
 * MovieGenre form base class.
 *
 * @method MovieGenre getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseMovieGenreForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'genre'       => new sfWidgetFormTextarea(),
      'created_at'  => new sfWidgetFormDateTime(),
      'updated_at'  => new sfWidgetFormDateTime(),
      'movies_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Movie')),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'genre'       => new sfValidatorString(array('max_length' => 256)),
      'created_at'  => new sfValidatorDateTime(),
      'updated_at'  => new sfValidatorDateTime(),
      'movies_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Movie', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('movie_genre[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'MovieGenre';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['movies_list']))
    {
      $this->setDefault('movies_list', $this->object->Movies->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
    $this->saveMoviesList($con);

    parent::doSave($con);
  }

  public function saveMoviesList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['movies_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->Movies->getPrimaryKeys();
    $values = $this->getValue('movies_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Movies', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Movies', array_values($link));
    }
  }

}
