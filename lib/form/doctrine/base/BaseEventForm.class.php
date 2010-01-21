<?php

/**
 * Event form base class.
 *
 * @method Event getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseEventForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
<<<<<<< HEAD:lib/form/doctrine/base/BaseEventForm.class.php
      'id'                           => new sfWidgetFormInputHidden(),
      'name'                         => new sfWidgetFormTextarea(),
      'short_description'            => new sfWidgetFormTextarea(),
      'description'                  => new sfWidgetFormTextarea(),
      'booking_url'                  => new sfWidgetFormTextarea(),
      'url'                          => new sfWidgetFormTextarea(),
      'price'                        => new sfWidgetFormTextarea(),
      'rating'                       => new sfWidgetFormInputText(),
      'vendor_id'                    => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'), 'add_empty' => false)),
      'created_at'                   => new sfWidgetFormDateTime(),
      'updated_at'                   => new sfWidgetFormDateTime(),
      'event_categories_list'        => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'EventCategory')),
      'vendor_event_categories_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'VendorEventCategory')),
      'vendor_event_category_list'   => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'VendorEventCategory')),
      'event_category_list'          => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'EventCategory')),
    ));

    $this->setValidators(array(
      'id'                           => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'name'                         => new sfValidatorString(array('max_length' => 256)),
      'short_description'            => new sfValidatorString(array('max_length' => 1024, 'required' => false)),
      'description'                  => new sfValidatorString(array('max_length' => 65535, 'required' => false)),
      'booking_url'                  => new sfValidatorString(array('max_length' => 1024, 'required' => false)),
      'url'                          => new sfValidatorString(array('max_length' => 1024, 'required' => false)),
      'price'                        => new sfValidatorString(array('max_length' => 1024, 'required' => false)),
      'rating'                       => new sfValidatorNumber(array('required' => false)),
      'vendor_id'                    => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'))),
      'created_at'                   => new sfValidatorDateTime(),
      'updated_at'                   => new sfValidatorDateTime(),
      'event_categories_list'        => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'EventCategory', 'required' => false)),
      'vendor_event_categories_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'VendorEventCategory', 'required' => false)),
      'vendor_event_category_list'   => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'VendorEventCategory', 'required' => false)),
      'event_category_list'          => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'EventCategory', 'required' => false)),
=======
      'id'                => new sfWidgetFormInputHidden(),
      'url'               => new sfWidgetFormTextarea(),
      'price'             => new sfWidgetFormTextarea(),
      'rating'            => new sfWidgetFormInputText(),
      'event_category_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('EventCategory'), 'add_empty' => true)),
      'vendor_id'         => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'), 'add_empty' => false)),
      'created_at'        => new sfWidgetFormDateTime(),
      'updated_at'        => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'url'               => new sfValidatorString(array('max_length' => 1024, 'required' => false)),
      'price'             => new sfValidatorString(array('max_length' => 1024, 'required' => false)),
      'rating'            => new sfValidatorNumber(array('required' => false)),
      'event_category_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('EventCategory'), 'required' => false)),
      'vendor_id'         => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'))),
      'created_at'        => new sfValidatorDateTime(),
      'updated_at'        => new sfValidatorDateTime(),
>>>>>>> 727941d371a3d6774a32c72dbba8fa6963178c1b:lib/form/doctrine/base/BaseEventForm.class.php
    ));

    $this->widgetSchema->setNameFormat('event[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Event';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['event_categories_list']))
    {
      $this->setDefault('event_categories_list', $this->object->EventCategories->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['vendor_event_categories_list']))
    {
      $this->setDefault('vendor_event_categories_list', $this->object->VendorEventCategories->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['vendor_event_category_list']))
    {
      $this->setDefault('vendor_event_category_list', $this->object->VendorEventCategory->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['event_category_list']))
    {
      $this->setDefault('event_category_list', $this->object->EventCategory->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
    $this->saveEventCategoriesList($con);
    $this->saveVendorEventCategoriesList($con);
    $this->saveVendorEventCategoryList($con);
    $this->saveEventCategoryList($con);

    parent::doSave($con);
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

  public function saveVendorEventCategoriesList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['vendor_event_categories_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->VendorEventCategories->getPrimaryKeys();
    $values = $this->getValue('vendor_event_categories_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('VendorEventCategories', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('VendorEventCategories', array_values($link));
    }
  }

  public function saveVendorEventCategoryList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['vendor_event_category_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->VendorEventCategory->getPrimaryKeys();
    $values = $this->getValue('vendor_event_category_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('VendorEventCategory', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('VendorEventCategory', array_values($link));
    }
  }

  public function saveEventCategoryList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['event_category_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->EventCategory->getPrimaryKeys();
    $values = $this->getValue('event_category_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('EventCategory', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('EventCategory', array_values($link));
    }
  }

}
