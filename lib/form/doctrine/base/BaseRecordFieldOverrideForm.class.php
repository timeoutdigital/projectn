<?php

/**
 * RecordFieldOverride form base class.
 *
 * @method RecordFieldOverride getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseRecordFieldOverrideForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'             => new sfWidgetFormInputHidden(),
      'field'          => new sfWidgetFormInputText(),
      'received_value' => new sfWidgetFormTextarea(),
      'edited_value'   => new sfWidgetFormTextarea(),
      'created_at'     => new sfWidgetFormDateTime(),
      'updated_at'     => new sfWidgetFormDateTime(),
      'poi_list'       => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Poi')),
      'event_list'     => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Event')),
      'movie_list'     => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Movie')),
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'field'          => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'received_value' => new sfValidatorString(array('required' => false)),
      'edited_value'   => new sfValidatorString(array('required' => false)),
      'created_at'     => new sfValidatorDateTime(),
      'updated_at'     => new sfValidatorDateTime(),
      'poi_list'       => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Poi', 'required' => false)),
      'event_list'     => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Event', 'required' => false)),
      'movie_list'     => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Movie', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('record_field_override[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'RecordFieldOverride';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['poi_list']))
    {
      $this->setDefault('poi_list', $this->object->Poi->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['event_list']))
    {
      $this->setDefault('event_list', $this->object->Event->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['movie_list']))
    {
      $this->setDefault('movie_list', $this->object->Movie->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
    $this->savePoiList($con);
    $this->saveEventList($con);
    $this->saveMovieList($con);

    parent::doSave($con);
  }

  public function savePoiList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['poi_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->Poi->getPrimaryKeys();
    $values = $this->getValue('poi_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Poi', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Poi', array_values($link));
    }
  }

  public function saveEventList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['event_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->Event->getPrimaryKeys();
    $values = $this->getValue('event_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Event', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Event', array_values($link));
    }
  }

  public function saveMovieList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['movie_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->Movie->getPrimaryKeys();
    $values = $this->getValue('movie_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Movie', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Movie', array_values($link));
    }
  }

}
