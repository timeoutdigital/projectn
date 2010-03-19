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
      'id'                         => new sfWidgetFormInputHidden(),
      'review_date'                => new sfWidgetFormInputText(),
      'vendor_event_id'            => new sfWidgetFormInputText(),
      'name'                       => new sfWidgetFormInputText(),
      'short_description'          => new sfWidgetFormTextarea(),
      'description'                => new sfWidgetFormTextarea(),
      'booking_url'                => new sfWidgetFormTextarea(),
      'url'                        => new sfWidgetFormTextarea(),
      'price'                      => new sfWidgetFormTextarea(),
      'rating'                     => new sfWidgetFormInputText(),
      'vendor_id'                  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'), 'add_empty' => false)),
      'created_at'                 => new sfWidgetFormDateTime(),
      'updated_at'                 => new sfWidgetFormDateTime(),
      'vendor_event_category_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'VendorEventCategory')),
    ));

    $this->setValidators(array(
      'id'                         => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'review_date'                => new sfValidatorPass(array('required' => false)),
      'vendor_event_id'            => new sfValidatorString(array('max_length' => 10)),
      'name'                       => new sfValidatorString(array('max_length' => 255)),
      'short_description'          => new sfValidatorString(array('max_length' => 1024, 'required' => false)),
      'description'                => new sfValidatorString(array('max_length' => 65535, 'required' => false)),
      'booking_url'                => new sfValidatorString(array('max_length' => 1024, 'required' => false)),
      'url'                        => new sfValidatorString(array('max_length' => 1024, 'required' => false)),
      'price'                      => new sfValidatorString(array('max_length' => 1024, 'required' => false)),
      'rating'                     => new sfValidatorNumber(array('required' => false)),
      'vendor_id'                  => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'))),
      'created_at'                 => new sfValidatorDateTime(),
      'updated_at'                 => new sfValidatorDateTime(),
      'vendor_event_category_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'VendorEventCategory', 'required' => false)),
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

    if (isset($this->widgetSchema['vendor_event_category_list']))
    {
      $this->setDefault('vendor_event_category_list', $this->object->VendorEventCategory->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
    $this->saveVendorEventCategoryList($con);

    parent::doSave($con);
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

}
