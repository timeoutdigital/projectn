<?php

/**
 * VendorEventCategory form base class.
 *
 * @method VendorEventCategory getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseVendorEventCategoryForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                    => new sfWidgetFormInputHidden(),
      'name'                  => new sfWidgetFormInputText(),
      'vendor_id'             => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'), 'add_empty' => false)),
      'events_list'           => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Event')),
      'event_categories_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'EventCategory')),
    ));

    $this->setValidators(array(
      'id'                    => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'name'                  => new sfValidatorString(array('max_length' => 255)),
      'vendor_id'             => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'))),
      'events_list'           => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Event', 'required' => false)),
      'event_categories_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'EventCategory', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('vendor_event_category[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'VendorEventCategory';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['events_list']))
    {
      $this->setDefault('events_list', $this->object->Events->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['event_categories_list']))
    {
      $this->setDefault('event_categories_list', $this->object->EventCategories->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
    $this->saveEventsList($con);
    $this->saveEventCategoriesList($con);

    parent::doSave($con);
  }

  public function saveEventsList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['events_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->Events->getPrimaryKeys();
    $values = $this->getValue('events_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Events', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Events', array_values($link));
    }
  }

  public function saveEventCategoriesList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['event_categories_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->EventCategories->getPrimaryKeys();
    $values = $this->getValue('event_categories_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('EventCategories', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('EventCategories', array_values($link));
    }
  }

}
